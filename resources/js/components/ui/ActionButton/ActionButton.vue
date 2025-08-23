<script setup lang="ts">

//mis importaciones 
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue'

//constantes 
//propiedades del boton
const props = defineProps<{
    color:string
    iconName?:string
    href?:string
    name?:string
}>()

//definicion de claves  y valores  (aqui definimos los colores de los botones)
const colorClasses: {[key:string]:string}={
    'blue': 'bg-blue-600 hover:bg-blue-700', 
    'red': 'bg-red-600 hover:bg-red-700',
    'green': 'bg-green-600 hover:bg-green-700',
    'yellow': 'bg-yellow-600 hover:bg-yellow-700',
}

//computed devolvera el valor segun el nombre de la clave que le pasemos
const buttonColor = computed(()=>{
    return colorClasses[props.color] || 'bg-gray-600 hover:bg-gray-700'
})

//clases base 
const baseClasses = 'px-4 py-2 font-medium text-white rounded-md transition-colors duration-200 ease-in-out shadow-md';

const iconAndTextClasses = 'flex items-center justify-center gap-2'

const buttonClasses = computed(()=>{
    return `${buttonColor.value} ${baseClasses} ${iconAndTextClasses}`
})

</script>

<template>
    <template v-if="href">
        <Link :href="href" :class="buttonClasses">
            <i :class="['bx', iconName]">
            </i>
            <span> {{ name }}</span>
        </Link>
    </template>
    <template v-else>
        <button type="button" :class="buttonClasses">
            <i :class="['bx', iconName]"></i>
            <span>{{ name }}</span>
        </button>
    </template>
</template>