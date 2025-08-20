<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

// Definición de las props con tipado de TypeScript
const props = defineProps<{
    href?: string; // Ahora es opcional
    color: string;
    nombre: string;
    iconName?: string; // Es opcional, con un valor por defecto
}>();

// Objeto para mapear los colores a clases de Tailwind
// Se pueden agregar más colores según lo necesites
const colorClasses: { [key: string]: string } = {
    'rgb(65, 27, 215)': 'bg-blue-600 hover:bg-blue-700', // Ejemplo para tu color original
    'red': 'bg-red-600 hover:bg-red-700',
    'green': 'bg-green-600 hover:bg-green-700',
};

// Propiedad computada para obtener las clases de color
const buttonColor = computed(() => {
    return colorClasses[props.color] || 'bg-gray-600 hover:bg-gray-700';
});

// Estilos del botón de forma modular y reutilizable
const baseClasses = 'px-4 py-2 font-medium text-white rounded-md transition-colors duration-200 ease-in-out shadow-md';
const iconAndTextClasses = 'flex items-center justify-center gap-2';

// Clases combinadas para el botón final
const buttonClasses = computed(() => {
    return `${baseClasses} ${buttonColor.value} ${iconAndTextClasses}`;
});
</script>

<template>
    <template v-if="href">
        <Link :href="href" :class="buttonClasses">
            <i :class="['bx', iconName]"></i>
            <span>{{ nombre }}</span>
        </Link>
    </template>
    <template v-else>
        <button type="button" :class="buttonClasses">
            <i :class="['bx', iconName]"></i>
            <span>{{ nombre }}</span>
        </button>
    </template>
</template>
