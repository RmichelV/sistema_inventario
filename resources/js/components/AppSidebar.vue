<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, BookUser, Folder, LayoutGrid, PersonStanding, TimerIcon, TimerOff, TimerOffIcon } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { route } from 'ziggy-js'; 

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Datos de empleados',
        href: '/rusers',
        icon: BookUser,
    },
    {
        title: 'Registros de asistencias',
        href: '/rattendance_records',
        icon: TimerOffIcon,
    },
    {
        title: 'Registrar asistencia',
        href: route('rattendance_records.create'),
        icon: TimerIcon,
    },
    {
        title: 'Registros de Movimientos de salarios',
        href: '/rsalary_adjustments',
        icon: TimerIcon,
    },
    {
        title: 'Gestion de salarios',
        href: route('rsalary_adjustments.create'),
        icon: TimerIcon,
    },
    {
        title: 'Lista de productos',
        href: '/rproducts',
        icon: TimerIcon,
    },
    {
        title: 'Registrar de Productos',
        href: route('rproducts.create'),
        icon: TimerIcon,
    },
    
    {
        title: 'Historial de compras',
        href: '/rpurchases',
        icon: TimerIcon,
    },
    {
        title: 'Registrar de Compra',
        href: route('rpurchases.create'),
        icon: TimerIcon,
    },
    {
        title: 'Productos en la tienda',
        href: '/rproductstores',
        icon: TimerIcon,
    },
    {
        title: 'Ingresar Productos',
        href: route('rproductstores.create'),
        icon: TimerIcon,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
