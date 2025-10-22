<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sucursales',
        href: '/rbranches',
    },
];

const form = useForm({
    name: '',
    address: '',
});

const submit = () => {
    form.post(route('rbranches.store'));
};
</script>

<template>
    <Head title="Crear Sucursal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <h2 class="text-lg font-medium mb-4">Crear nueva sucursal</h2>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Nombre</label>
                    <input v-model="form.name" type="text" name="name" class="mt-1 block w-full border rounded-md p-2" />
                </div>
                <div>
                    <label class="block text-sm font-medium">Dirección</label>
                    <input v-model="form.address" type="text" name="address" class="mt-1 block w-full border rounded-md p-2" />
                </div>
                <div>
                    <button type="submit" class="btn btn-green">Crear</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
