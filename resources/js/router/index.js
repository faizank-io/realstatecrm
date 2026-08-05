import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from '../views/LandingPage.vue';
import LoginView from '../views/LoginView.vue';
import RegisterView from '../views/RegisterView.vue';
import SuperAdminLoginView from '../views/SuperAdminLoginView.vue';
import AgencyDashboard from '../views/AgencyDashboard.vue';
import PropertiesView from '../views/PropertiesView.vue';
import LeadsView from '../views/LeadsView.vue';
import TeamView from '../views/TeamView.vue';
import RolesView from '../views/RolesView.vue';
import SuperAdminDashboard from '../views/SuperAdminDashboard.vue';

const routes = [
  { path: '/', name: 'Landing', component: LandingPage },
  { path: '/login', name: 'Login', component: LoginView },
  { path: '/register', name: 'Register', component: RegisterView },
  { path: '/super-admin/login', name: 'SuperAdminLogin', component: SuperAdminLoginView },
  { path: '/dashboard', name: 'AgencyDashboard', component: AgencyDashboard },
  { path: '/properties', name: 'Properties', component: PropertiesView },
  { path: '/leads', name: 'Leads', component: LeadsView },
  { path: '/team', name: 'Team', component: TeamView },
  { path: '/roles', name: 'Roles', component: RolesView },
  { path: '/super-admin/dashboard', name: 'SuperAdminDashboard', component: SuperAdminDashboard },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
