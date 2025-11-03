<script setup lang="ts">
import InputError from '@/components/InputError.vue';
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Lista de clientes',
        href: '/rcustomers',
    },
];

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
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <AuthBase title="Registrar un nuevo cliente">
                    <Head title="Registrar Cliente" />

                    <Form
                        method="post"
                        :action="route('rcustomers.store')"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-6"
                    >
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
                                    name="name"
                                    placeholder="Ingrese el nombre completo"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="email">Correo electrónico</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    name="email"
                                    placeholder="Ej. cliente@example.com"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="phone">Número de contacto</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    :tabindex="3"
                                    autocomplete="tel"
                                    name="phone"
                                    placeholder="Ej. 71234568"
                                />
                                <InputError :message="errors.phone" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="address">Dirección</Label>
                                <Input
                                    id="address"
                                    type="text"
                                    :tabindex="4"
                                    autocomplete="address"
                                    name="address"
                                    placeholder="Ej. Av. Buenos Aires #10000"
                                />
                                <InputError :message="errors.address" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="notes">Notas</Label>
                                <textarea
                                    id="notes"
                                    :tabindex="5"
                                    name="notes"
                                    placeholder="Ej. Cliente frecuente"
                                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                                    rows="4"
                                />
                                <InputError :message="errors.notes" />
                            </div>

                            <Button type="submit" class="w-full mt-2" tabindex="6" :disabled="processing">
                                <LoaderCircle v-if="processing" class="w-4 h-4 animate-spin" />
                                Registrar cliente
                            </Button>
                        </div>
                    </Form>
                </AuthBase>
            </div>
        </div>
    </AppLayout>
</template>
