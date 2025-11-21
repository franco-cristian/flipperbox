<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <h2 class="text-2xl font-bold text-white text-center mb-6">Bienvenido de nuevo</h2>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-400 text-center">
            {{ status }}
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-gray-300" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-gray-300" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox
                        v-model:checked="form.remember"
                        name="remember"
                        class="bg-white/10 border-gray-600 text-cyan-500 focus:ring-cyan-500"
                    />
                    <span class="ms-2 text-sm text-gray-400">Recordarme</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-400 hover:text-white transition"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton
                    class="ml-4 bg-gradient-to-r from-cyan-600 to-blue-600 border-0 hover:from-cyan-500 hover:to-blue-500"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Ingresar
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center border-t border-white/10 pt-4">
                <Link
                    :href="route('register')"
                    class="text-sm text-cyan-400 hover:text-cyan-300 transition font-medium"
                >
                    ¿No tienes cuenta? Regístrate aquí
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
