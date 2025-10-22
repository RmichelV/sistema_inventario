<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import type { Branch } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sucursales',
        href: '/rbranches',
    },
];

const props = defineProps<{ branch: Branch }>();

const form = useForm({
    name: props.branch.name,
    address: props.branch.address,
});

const submit = () => {
    form.put(route('rbranches.update', props.branch.id));
};
</script>

<template>
    <Head title="Editar Sucursal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <h2 class="text-lg font-medium mb-4">Editar sucursal</h2>
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
                    <button type="submit" class="btn btn-blue">Guardar</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
