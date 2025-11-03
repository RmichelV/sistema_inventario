<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useForm, Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';
import AuthBase from '@/layouts/AuthLayout.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de clientes',
        href: '/rcustomers',
    },
];

const { customer } = defineProps<{
    customer: any;
}>();

// Inicializa el formulario con los datos del cliente
const form = useForm({
    name: customer.name,
    email: customer.email,
    phone: customer.phone ?? '',
    address: customer.address ?? '',
    notes: customer.notes ?? '',
});

// Define el método para enviar la actualización
const submit = () => {
    form.put(route('rcustomers.update', customer.id));
};
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <AuthBase title="Editar cliente">
                    <Head title="Editar Cliente" />

                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="name">Nombre</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    v-model="form.name"
                                    placeholder="Ingrese el nombre completo"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Correo electrónico</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    v-model="form.email"
                                    placeholder="Ej. cliente@example.com"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Número de contacto</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    :tabindex="3"
                                    autocomplete="tel"
                                    v-model="form.phone"
                                    placeholder="Ej. 71234568"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="address">Dirección</Label>
                                <Input
                                    id="address"
                                    type="text"
                                    :tabindex="4"
                                    autocomplete="address"
                                    v-model="form.address"
                                    placeholder="Ej. Av. Buenos Aires #10000"
                                />
                                <InputError :message="form.errors.address" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="notes">Notas</Label>
                                <textarea
                                    id="notes"
                                    :tabindex="5"
                                    v-model="form.notes"
                                    placeholder="Ej. Cliente frecuente"
                                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                    rows="4"
                                />
                                <InputError :message="form.errors.notes" />
                            </div>

                            <Button type="submit" class="w-full mt-2" tabindex="6" :disabled="form.processing">
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
