<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PlatformModule;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed System Permissions
        $permissions = [
            // Properties
            ['name' => 'properties.view', 'display_name' => 'View Properties', 'module' => 'properties'],
            ['name' => 'properties.create', 'display_name' => 'Create Properties', 'module' => 'properties'],
            ['name' => 'properties.edit', 'display_name' => 'Edit Properties', 'module' => 'properties'],
            ['name' => 'properties.delete', 'display_name' => 'Delete Properties', 'module' => 'properties'],
            
            // Leads CRM
            ['name' => 'leads.view', 'display_name' => 'View Leads', 'module' => 'leads'],
            ['name' => 'leads.create', 'display_name' => 'Create Leads', 'module' => 'leads'],
            ['name' => 'leads.edit', 'display_name' => 'Edit Leads', 'module' => 'leads'],
            ['name' => 'leads.delete', 'display_name' => 'Delete Leads', 'module' => 'leads'],
            
            // Viewings
            ['name' => 'viewings.view', 'display_name' => 'View Viewings Schedule', 'module' => 'viewings'],
            ['name' => 'viewings.manage', 'display_name' => 'Schedule & Manage Viewings', 'module' => 'viewings'],

            // Deals & Transactions
            ['name' => 'deals.view', 'display_name' => 'View Deals', 'module' => 'deals'],
            ['name' => 'deals.manage', 'display_name' => 'Manage Deals & Offers', 'module' => 'deals'],

            // Documents
            ['name' => 'documents.manage', 'display_name' => 'Manage Documents', 'module' => 'documents'],

            // Agency Team & Roles
            ['name' => 'team.manage', 'display_name' => 'Manage Agency Team', 'module' => 'team'],
            ['name' => 'roles.manage', 'display_name' => 'Manage Custom Roles & Permissions', 'module' => 'team'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Agency Settings', 'module' => 'settings'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p['name']], $p);
        }

        $allPermissionIds = Permission::pluck('id')->toArray();

        // 2. Seed System Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], [
            'display_name' => 'Super Administrator',
            'is_system' => true,
        ]);

        $agencyOwnerRole = Role::firstOrCreate(['name' => 'agency_owner'], [
            'display_name' => 'Agency Owner',
            'is_system' => true,
        ]);
        $agencyOwnerRole->permissions()->sync($allPermissionIds);

        $officeManagerRole = Role::firstOrCreate(['name' => 'office_manager'], [
            'display_name' => 'Office Manager',
            'is_system' => true,
        ]);

        $agentRole = Role::firstOrCreate(['name' => 'agent'], [
            'display_name' => 'Real Estate Agent',
            'is_system' => true,
        ]);

        $propertyManagerRole = Role::firstOrCreate(['name' => 'property_manager'], [
            'display_name' => 'Property Manager',
            'is_system' => true,
        ]);

        // Assign subset permissions to Agent role
        $agentPermissionIds = Permission::whereIn('name', [
            'properties.view', 'properties.create', 'properties.edit',
            'leads.view', 'leads.create', 'leads.edit',
            'viewings.view', 'viewings.manage',
            'deals.view'
        ])->pluck('id')->toArray();
        $agentRole->permissions()->sync($agentPermissionIds);

        // 3. Seed Default Super Admin User
        User::firstOrCreate(['email' => 'admin@realstate.com'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password123'),
            'role_id' => $superAdminRole->id,
            'agency_id' => null,
            'status' => 'active',
        ]);

        // 4. Seed Platform Modules
        $modules = [
            ['key' => 'properties', 'label' => 'Property Management', 'description' => 'Listings, photos, floor plans & categories', 'sort_order' => 1],
            ['key' => 'leads_crm', 'label' => 'Client & Lead CRM', 'description' => 'Buyers, sellers, landlords, tenants & investors tracking', 'sort_order' => 2],
            ['key' => 'viewings', 'label' => 'Viewing Scheduler', 'description' => 'Schedule property viewings & calendar view', 'sort_order' => 3],
            ['key' => 'deals', 'label' => 'Deals & Transactions', 'description' => 'Offers, contracts, commission calculations', 'sort_order' => 4],
            ['key' => 'documents', 'label' => 'Document Vault', 'description' => 'Contracts, agreements & property files storage', 'sort_order' => 5],
            ['key' => 'analytics', 'label' => 'Reports & Analytics', 'description' => 'Sales, rentals & agent performance metrics', 'sort_order' => 6],
        ];

        foreach ($modules as $m) {
            PlatformModule::firstOrCreate(['key' => $m['key']], $m);
        }

        // 5. Seed Subscription Plans
        SubscriptionPlan::firstOrCreate(['slug' => 'starter'], [
            'name' => 'Starter Plan',
            'description' => 'Perfect for small real estate agencies & independent brokers',
            'monthly_price' => 49.00,
            'yearly_price' => 490.00,
            'max_users' => 5,
            'max_properties' => 50,
            'max_agents' => 5,
            'max_roles' => 2,
            'modules' => ['properties', 'leads_crm'],
            'sort_order' => 1,
            'is_active' => true,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'professional'], [
            'name' => 'Professional Plan',
            'description' => 'Ideal for growing real estate agencies with active teams',
            'monthly_price' => 99.00,
            'yearly_price' => 990.00,
            'max_users' => 15,
            'max_properties' => 200,
            'max_agents' => 15,
            'max_roles' => 5,
            'modules' => ['properties', 'leads_crm', 'viewings', 'deals'],
            'sort_order' => 2,
            'is_active' => true,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'enterprise'], [
            'name' => 'Enterprise Plan',
            'description' => 'Complete solution for large real estate firms with unlimited scale',
            'monthly_price' => 249.00,
            'yearly_price' => 2490.00,
            'max_users' => -1, // Unlimited
            'max_properties' => -1, // Unlimited
            'max_agents' => -1, // Unlimited
            'max_roles' => -1, // Unlimited
            'modules' => ['properties', 'leads_crm', 'viewings', 'deals', 'documents', 'analytics'],
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // 6. Seed Default System Settings
        SystemSetting::setValue('general', 'default_trial_days', '14');
        SystemSetting::setValue('general', 'site_name', 'Real Estate SaaS');
    }
}
