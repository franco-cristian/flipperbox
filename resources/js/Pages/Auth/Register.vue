<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    apellido: '',
    email: '',
    telefono: '',
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
                <div>
                    <InputLabel for="name" value="Nombre" class="text-white" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                        required
                        autofocus
                        autocomplete="given-name"
                        placeholder="Juan"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="apellido" value="Apellido" class="text-white" />
                    <TextInput
                        id="apellido"
                        v-model="form.apellido"
                        type="text"
                        class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                        required
                        autocomplete="family-name"
                        placeholder="Pérez"
                    />
                    <InputError class="mt-2" :message="form.errors.apellido" />
                </div>
            </div>

            <div>
                <InputLabel for="email" value="Correo Electrónico" class="text-white" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="username"
                    placeholder="juan.perez@ejemplo.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="telefono" value="Teléfono / Celular" class="text-white" />
                <TextInput
                    id="telefono"
                    v-model="form.telefono"
                    type="text"
                    class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    placeholder="3704..."
                />
                <InputError class="mt-2" :message="form.errors.telefono" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" class="text-white" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="new-password"
                    placeholder="Mínimo 8 caracteres"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="text-white" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full bg-white/10 border-gray-500 text-white placeholder-gray-400 focus:border-cyan-500 focus:ring-cyan-500"
                    required
                    autocomplete="new-password"
                    placeholder="Repite la contraseña"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-4 mt-8">
                <Link
                    :href="route('login')"
                    class="text-sm text-gray-300 hover:text-white underline decoration-1 underline-offset-2 transition"
                >
                    ¿Ya tienes cuenta?
                </Link>

                <PrimaryButton
                    class="w-full sm:w-auto justify-center bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 border-0 font-bold py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Registrarse
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
