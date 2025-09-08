<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import type { User, Role } from '@/types';
import { useForm, Head, usePage } from '@inertiajs/vue3'; // Asegúrate de importar useForm
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';
import AuthBase from '@/layouts/AuthLayout.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de empleados',
        href: '/rusers',
    },
];

const props = defineProps<{
    user: User; // <-- Cambia a `User` (singular)
    roles: Role[];
}>();

// 1. Inicializa el formulario con los datos del usuario
const form = useForm({
    name: props.user.name,
    address: props.user.address,
    phone: props.user.phone,
    role_id: props.user.role_id,
    base_salary: props.user.base_salary,
    hire_date: props.user.hire_date,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

// 2. Define el método para enviar la actualización
const submit = () => {
    // Usa form.put() para enviar una solicitud PUT y el id del usuario en la URL
    form.put(route('rusers.update', props.user.id), {
        onSuccess: () => {
            // Opcional: limpiar la contraseña después de un envío exitoso
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Empleados" />

    <AppLayout :breadcrumbs="breadcrumbs">
       <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <AuthBase title="Editar empleado" >
                    <Head title="Registro" />

                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="name">Nombre(s) y Apellido(s)</Label>
                                <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name" placeholder="Ingrese el nombre completo" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="address">Dirección</Label>
                                <Input id="address" type="text" required autofocus :tabindex="1" autocomplete="address" v-model="form.address" placeholder="Ej. Av. Buenos Aires #10000" />
                                <InputError :message="form.errors.address" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Número de contacto</Label>
                                <Input id="phone" type="text" required autofocus :tabindex="1" autocomplete="phone" v-model="form.phone" placeholder="Ej. 71234568" />
                                <InputError :message="form.errors.phone" />
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="role_id">Elija el cargo</Label>
                                <select id="role_id" v-model="form.role_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="" disabled>Seleccione un cargo</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                                <InputError :message="form.errors.role_id" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="base_salary">Salario base (Bs.)</Label>
                                <Input id="base_salary" type="text" required autofocus :tabindex="1" autocomplete="base_salary" v-model="form.base_salary" placeholder="Ej. 500.00" step="0.01" min="0"/>
                                <InputError :message="form.errors.base_salary" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Correo electrónico</Label>
                                <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="Ej. email@ewtto.com" />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password">Contraseña</Label>
                                <Input id="password" type="password" :tabindex="2" autocomplete="password" v-model="form.password" placeholder="Ej. password123" />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation">Confirmar Contraseña</Label>
                                <Input id="password_confirmation" type="password" :tabindex="2" autocomplete="new-password" v-model="form.password_confirmation" placeholder="Ej. password123" />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>

                            <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                                Guardar cambios
                            </Button>
                        </div>
                    </form>
                </AuthBase>
            </div>
        </div>
    </AppLayout>
</template>