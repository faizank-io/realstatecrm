<template>
  <div class="login-container">
    <h2>Vue Test Login</h2>

    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label>Email:</label>
        <input type="email" v-model="form.email" required placeholder="admin@test.com">
      </div>

      <div class="form-group">
        <label>Password:</label>
        <input type="password" v-model="form.password" required placeholder="password123">
      </div>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Logging in...' : 'Submit' }}
      </button>
    </form>

    <!-- Status Messages -->
    <p v-if="message" :class="{ 'text-success': success, 'text-danger': !success }">
      {{ message }}
    </p>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const form = reactive({
    email: '',
    password: ''
});

const loading = ref(false);
const message = ref('');
const success = ref(false);

const handleLogin = async () => {
    loading.value = true;
    message.value = '';

    try {
        // Calling our static Laravel API route
        const response = await fetch('/test-login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form)
        });

        const data = await response.json();
        success.value = data.success;
        message.value = data.message;

        if (data.success) {
            console.log('Logged in user:', data.user);
            // You can save token to localStorage here if needed
        }
    } catch (error) {
        success.value = false;
        message.value = 'Something went wrong connection to the server.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-container { max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; font-family: sans-serif; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; }
.form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
button { width: 100%; padding: 10px; background-color: #4f46e5; color: white; border: none; border-radius: 4px; cursor: pointer; }
button:disabled { background-color: #a5b4fc; }
.text-success { color: green; margin-top: 10px; font-weight: bold; }
.text-danger { color: red; margin-top: 10px; font-weight: bold; }
</style>
