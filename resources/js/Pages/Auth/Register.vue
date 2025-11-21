<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    apellido: '', // Nuevo campo
    email: '',
    telefono: '', // Nuevo campo
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Crear Cuenta" />

        <h2 class="text-2xl font-bold text-white text-center mb-6">Únete a FlipperBox</h2>

        <form class="space-y-4" @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre -->
                <div>
                    <InputLabel for="name" value="Nombre" class="text-gray-300" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                        required
                        autofocus
                        autocomplete="given-name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Apellido (NUEVO) -->
                <div>
                    <InputLabel for="apellido" value="Apellido" class="text-gray-300" />
                    <TextInput
                        id="apellido"
                        v-model="form.apellido"
                        type="text"
                        class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                        required
                        autocomplete="family-name"
                    />
                    <InputError class="mt-2" :message="form.errors.apellido" />
                </div>
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-gray-300" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Teléfono (NUEVO) -->
            <div>
                <InputLabel for="telefono" value="Teléfono / Celular" class="text-gray-300" />
                <TextInput
                    id="telefono"
                    v-model="form.telefono"
                    type="text"
                    class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    placeholder="Ej: 3704..."
                />
                <InputError class="mt-2" :message="form.errors.telefono" />
            </div>

            <!-- Contraseña -->
            <div>
                <InputLabel for="password" value="Contraseña" class="text-gray-300" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="text-gray-300" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full bg-white/5 border-gray-600 text-white focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <Link :href="route('login')" class="underline text-sm text-gray-400 hover:text-white transition">
                    ¿Ya tienes cuenta?
                </Link>

                <PrimaryButton
                    class="ml-4 bg-gradient-to-r from-cyan-600 to-blue-600 border-0 hover:from-cyan-500 hover:to-blue-500"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
