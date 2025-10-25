<script setup lang="ts">
import InputError from '@/components/InputError.vue';
// TextLink no se usa aquí
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { useSwal } from '@/composables/useSwal';
import { LoaderCircle } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type { Role, Branch } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de empleados',
        href: '/rusers',
    },
];

const { roles, branches } = defineProps<{
    roles: Role[];
    branches: Branch[];
}>();

// Mostrar mensajes flash (error/success) usando SweetAlert para feedback inmediato
const page = usePage();
const swal = useSwal();

const handleFlash = () => {
    if (page.props.flash?.error) {
        swal.fire({ icon: 'error', title: 'Error', text: page.props.flash.error });
    }
    if (page.props.flash?.success) {
        swal.fire({ icon: 'success', title: 'Éxito', text: page.props.flash.success });
    }
};

onMounted(() => handleFlash());
watch(() => page.props, handleFlash, { deep: true });

</script>

<template>
    <Head title="Empleados" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div> -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <AuthBase title="Registra un nuevo empleado" >
                    <Head title="Registro" />

                    <Form
                        method="post"
                        :action="route('rusers.store')"
                        :reset-on-success="['password', 'password_confirmation']"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="name">Nombre(s) y Apellido(s)</Label>
                                <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name" placeholder="Ingrese el nombre completo" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="address">Dirección</Label>
                                <Input id="address" type="text" required autofocus :tabindex="1" autocomplete="address" name="address" placeholder="Ej. Av. Buenos Aires #10000" />
                                <InputError :message="errors.address" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Número de contacto</Label>
                                <Input id="phone" type="text" required autofocus :tabindex="1" autocomplete="phone" name="phone" placeholder="Ej. 71234568" />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="role_id">Elija el cargo</Label>
                                <select id="role_id" name="role_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" required>
                                <option value="" disabled selected>Seleccione un cargo</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                </select>
                                <InputError :message="errors.role_id" />
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="branch_id">Sucursal</Label>
                                <select id="branch_id" name="branch_id" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm" required>
                                    <option value="" disabled selected>Seleccione una sucursal</option>
                                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                                </select>
                                <InputError :message="errors.branch_id" />
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="base_salary">Salario base (Bs.)</Label>
                                <Input id="base_salary" type="text" required autofocus :tabindex="1" autocomplete="base_salary" name="base_salary" placeholder="Ej. 500.00" step="0.01" min="550"/>
                                <InputError :message="errors.base_salary" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="hire_date">Fecha de ingreso</Label>
                                <Input id="hire_date" type="date" required autofocus :tabindex="1" autocomplete="hire_date" name="hire_date" />
                                <InputError :message="errors.hire_date" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Correo electrónico</Label>
                                <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email" placeholder="Ej. email@ewtto.com" />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password">Contraseña</Label>
                                <Input id="password" type="password" required :tabindex="3" autocomplete="new-password" name="password" placeholder="Ej. Aab123" />
                                <InputError :message="errors.password" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="password_confirmation">Confirmar contraseña</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    :tabindex="4"
                                    autocomplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirmar contraseña"
                                />
                                <InputError :message="errors.password_confirmation" />
                            </div>

                            <Button type="submit" class="w-full mt-2" tabindex="5" :disabled="processing">
                                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                                Registrar empleado
                            </Button>
                        </div>

                        <!-- <div class="text-sm text-center text-muted-foreground">
                            Already have an account?
                            <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
                        </div> -->
                    </Form>
                </AuthBase>
            </div>
        </div>
    </AppLayout>
</template>