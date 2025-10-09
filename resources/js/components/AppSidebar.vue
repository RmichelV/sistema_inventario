<script setup lang="ts">
import { computed } from 'vue'; // 1. Importa 'computed'
import { usePage } from '@inertiajs/vue3'; // 2. Importa 'usePage'

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Banknote, BookOpen, BookUser, FileClock, Folder, LayoutGrid, List, ListCheck, ListChecks, PersonStanding, ShoppingCart, Store, TimerIcon, TimerOff, TimerOffIcon, Warehouse } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { route } from 'ziggy-js'; 

// 3. Obtiene los props de Inertia y la información del usuario
const page = usePage();
const user = computed(() => page.props.auth.user);

// 4. Añade una propiedad 'roles' a cada ítem del menú
// Esta propiedad define qué roles (por role_id) pueden ver el ítem.
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        roles: [1, 2, 3, 4], // Todos los roles pueden ver el Dashboard
    },
    {
        title: 'Datos de empleados',
        href: '/rusers',
        icon: BookUser,
        roles: [1], // Solo el rol 1 (Administrador)
    },
    {
        title: 'Gestion de salarios',
        href: '/rsalaries',
        icon: Banknote,
        roles: [1], // Solo el rol 1 (Administrador)
    },
    {
        title: 'Salarios cancelados',
        href: '/rsalariesF',
        icon: Banknote,
        roles: [1], // Solo el rol 1 (Administrador)
    },
    {
        title: 'Registros de asistencias',
        href: '/rattendance_records',
        icon: TimerOffIcon,
        roles: [1], // Rol 1 y 2
    },
    
    {
        title: 'Registrar asistencia',
        href: route('rattendance_records.create'),
        icon: TimerIcon,
        roles: [1, 2],
    },
    {
        title: 'Descuentos/Bonificaciones',
        href: '/rsalary_adjustments',
        icon: Banknote,
        roles: [1,2],
    },
    // bodega
    {
        title: 'Productos en Bodega',
        href: '/rproducts',
        icon: List,
        roles: [1, 4],
    },

    {
        title: 'Historial de compras',
        href: '/rpurchases',
        icon: FileClock,
        roles: [1, 4],
    },
    // tienda
    {
        title: 'Productos en la tienda',
        href: '/rproductstores',
        icon: Store,
        roles: [1, 2, 3],
    },

    {
        title: 'Ventas',
        href: '/rsales',
        icon: ShoppingCart,
        roles: [1, 2, 3],
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

// 5. Crea la propiedad computada para filtrar los ítems del menú
const filteredNavItems = computed(() => {
    // Si no hay un usuario o no tiene un rol, devuelve un array vacío
    if (!user.value || !user.value.role_id) {
        return [];
    }
    
    // Filtra el array, manteniendo solo los ítems que contienen el role_id del usuario
    return mainNavItems.filter(item => 
        item.roles && item.roles.includes(user.value.role_id)
    );
});
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
            <NavMain :items="filteredNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>