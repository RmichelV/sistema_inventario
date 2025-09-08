<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';
import type { Usd_exchange_rate } from '@/types';
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
        href: '/rusdexchangerates',
    },
];

const props = defineProps<{
    usd: Usd_exchange_rate
}>();

// 1. Inicializa el formulario con los datos del usuario
const form = useForm({
    exchange_rate: props.usd.exchange_rate
});

console.log(props)
// 2. Define el método para enviar la actualización
const submit = () => {
    // Usa form.put() para enviar una solicitud PUT y el id del usuario en la URL
    form.put(route('rusdexchangerates.update', 1), {
        onSuccess: () => {
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
                <AuthBase title="Registra un nuevo empleado" >
                    <Head title="Registro" />

                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        <div class="grid gap-6">

                            <div class="grid gap-2">
                                <Label for="exchange_rate">Ingrese el tipo de cambio $us a Bs </Label>
                                <Input id="exchange_rate" type="number" min="1.00" step="0.01" required autofocus :tabindex="1" autocomplete="exchange_rate" v-model="form.exchange_rate" placeholder="Ej. 6.96" />
                                <InputError :message="form.errors.exchange_rate" />
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