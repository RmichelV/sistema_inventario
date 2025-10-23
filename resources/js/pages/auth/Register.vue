<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <Form
            method="post"
            :action="route('register')"
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
                    <InputError :message="errors.address" />
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
</template>
