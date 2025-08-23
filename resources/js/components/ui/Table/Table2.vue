<script setup lang="ts">
import { computed } from 'vue';
import { ActionButton } from '@/components/ui/ActionButton';

// Define las propiedades del componente con tipado de TypeScript
const props = defineProps<{
  Cadena: any[]; // Cambiado a 'any[]' para mayor flexibilidad con datos complejos
  Cabeceras: string[];
  campos: string[];
  acciones: {
    href?: string | ((item: any) => string);
    color: string;
    name: string;
    iconName: string;
    onClick?: (item: any) => void;
  }[];
  agregar: {
    href: string;
    color: string;
    name: string;
    iconName: string;
  } | boolean;
}>();

// Función para obtener valores de objetos anidados (la misma lógica que tenías)
function getValor(obj: any, campo: string): any {
    const partes = campo.split('.');
    let valor = obj;
    for (const parte of partes) {
        if (!valor || typeof valor !== 'object') {
            return 'N/A'; // Usar 'N/A' en lugar de 'No disponible' para ser más conciso
        }
        valor = valor[parte];
    }
    return valor || 'N/A';
}

// Lógica computada para el botón de agregar
const agregarAccion = computed(() => {
  if (typeof props.agregar === 'object' && props.agregar.href && props.agregar.name) {
    return props.agregar;
  }
  return null;
});
</script>

<template>
  <!-- Contenedor del botón de agregar -->
  <div v-if="agregarAccion" class="flex justify-end p-4">
    <ActionButton
        :href="agregarAccion.href"
        :color="agregarAccion.color"
        :name="agregarAccion.name"
        :iconName="agregarAccion.iconName"
    />
  </div>

  <!-- Contenedor principal que maneja la responsividad -->
  <div class="overflow-x-auto shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="(cabecera, index) in Cabeceras"
            :key="`header-${index}`"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            {{ cabecera }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="cadena in Cadena" :key="cadena.id || cadena.nombre">
          <td
            v-for="(campo, index) in campos"
            :key="`data-${cadena.id}-${index}`"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
          >
            {{ getValor(cadena, campo) }}
          </td>
          <td v-if="acciones.length > 0" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center gap-2">
              <template v-for="(accion, idx) in acciones" :key="`action-${cadena.id}-${idx}`">
                <ActionButton
                    :href="typeof accion.href === 'function' ? accion.href(cadena) : accion.href"
                    :color="accion.color"
                    :nombre="accion.name"
                    :iconName="accion.iconName"
                    @click="() => accion.onClick && accion.onClick(cadena)"
                />
              </template>
            </div>
          </td>
        </tr>
        <tr v-if="!Cadena || Cadena.length === 0">
          <td :colspan="Cabeceras.length + (acciones.length > 0 ? 1 : 0)" class="px-6 py-4 text-center text-gray-500">
            No se encontraron datos.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
