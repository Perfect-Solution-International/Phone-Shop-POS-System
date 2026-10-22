<template>
    <main class="login-shell">
        <b-card class="login-card" title="Welcome back" sub-title="Sign in to your workspace">
            <validation-observer ref="form" v-slot="{ handleSubmit }">
                <b-form class="mt-4" @submit.prevent="handleSubmit(submit)">
                    <b-alert :show="Boolean(error)" variant="danger" role="alert">{{ error }}</b-alert>
                    <validation-provider v-slot="{ errors }" name="Email" vid="email" rules="required|email">
                        <b-form-group label="Email address" label-for="email">
                            <b-form-input id="email" v-model="credentials.email" type="email" autocomplete="username" :state="errors.length ? false : null" required />
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                    <validation-provider v-slot="{ errors }" name="Password" vid="password" rules="required">
                        <b-form-group label="Password" label-for="password">
                            <b-form-input id="password" v-model="credentials.password" type="password" autocomplete="current-password" :state="errors.length ? false : null" required />
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                    <b-button type="submit" variant="primary" block :disabled="busy"><b-spinner v-if="busy" small /> {{ busy ? 'Signing in?' : 'Sign in' }}</b-button>
                </b-form>
            </validation-observer>
        </b-card>
    </main>
</template>

<script>
export default {
    metaInfo: { title: 'Sign in' },
    data: () => ({ credentials: { email: '', password: '' }, busy: false, error: '' }),
    methods: {
        async submit() {
            if (this.busy) { return; }
            this.busy = true;
            this.error = '';
            try {
                await this.$store.dispatch('auth/login', this.credentials);
                window.location.assign('/app/');
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.$refs.form.setErrors(error.response.data.errors);
                } else {
                    this.error = error.response && error.response.status === 429
                        ? 'Too many sign-in attempts. Please wait a minute.'
                        : error.message === 'Network Error' ? 'Unable to connect. Please try again.' : 'Unable to sign in. Please try again.';
                }
            } finally { this.busy = false; }
        },
    },
};
</script>
