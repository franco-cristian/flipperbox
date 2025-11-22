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

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-white" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="ejemplo@correo.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-white" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox
                        v-model:checked="form.remember"
                        name="remember"
                        class="bg-white/10 border-gray-500 text-cyan-500 focus:ring-cyan-500"
                    />
                    <span class="ms-2 text-sm text-gray-300">Recordarme</span>
                </label>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 mt-6">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-gray-300 hover:text-white underline decoration-1 underline-offset-2 transition"
                >
                    ¿Olvidaste tu contraseña?
                </Link>

                <PrimaryButton
                    class="w-full sm:w-auto justify-center bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 border-0 font-bold py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Ingresar
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center border-t border-white/20 pt-4">
                <Link
                    :href="route('register')"
                    class="text-sm text-cyan-400 hover:text-cyan-300 font-medium transition"
                >
                    ¿No tienes cuenta? <span class="underline">Regístrate aquí</span>
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
