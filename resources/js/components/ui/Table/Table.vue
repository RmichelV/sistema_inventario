<script setup lang="ts">
import { ActionButton } from '../ActionButton';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
  cadena: any[]
  cabeceras: string[]
  campos: string[]
  agregar: {
    href: string;
    color: string;
    name: string;
    iconName: string;
  } | boolean;
  acciones: {
    href?: string | ((item: any) => string);
    color: string;
    name: string;
    iconName: string;
    onClick?: (item: any) => void;
  }[];
}>();

// --- Lógica para la vista adaptativa ---
const windowWidth = ref(window.innerWidth);

const handleResize = () => {
  windowWidth.value = window.innerWidth;
};

// Se ejecuta cuando el componente se monta
onMounted(() => {
  window.addEventListener('resize', handleResize);
});

// Se ejecuta cuando el componente se desmonta para limpiar el listener
onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});

// Propiedad computada que define si la pantalla es "pequeña" (móvil)
// Aquí, el breakpoint es 768px, el mismo que usa Tailwind para 'md'
const isSmallScreen = computed(() => windowWidth.value <= 768);

// --- Lógica existente ---
function getValor(obj: any, campo: string): any {
  const partes = campo.split('.');
  let valor = obj;
  for (const parte of partes) {
      if (!valor || typeof valor !== 'object') {
          return 'N/A';
      }
      valor = valor[parte];
  }
  return valor || 'N/A';
}

const agregarAccion = computed(() => {
  if (typeof props.agregar === 'object' && props.agregar.href && props.agregar.name) {
    return props.agregar;
  }
  return null;
});

</script>

<template>
  <div v-if="agregarAccion" class="flex justify-end p-4">
    <ActionButton
        :href="agregarAccion.href"
        :color="agregarAccion.color"
        :name="agregarAccion.name"
        :iconName="agregarAccion.iconName"
    />
  </div>

  <div v-if="isSmallScreen" class="grid grid-cols-1 gap-6 p-4">
    <div
      v-for="item in cadena"
      :key="item.id || item.nombre"
      class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700"
    >
      <div v-for="(campo, index) in campos" :key="`card-data-${item.id}-${index}`" class="mb-4 last:mb-0">
        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
          {{ cabeceras[index] }}
        </p>
        <p class="mt-1 text-base text-gray-900 dark:text-white">
          {{ getValor(item, campo) }}
        </p>
      </div>

      <div v-if="acciones.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2 justify-end">
          <template v-for="(accion, idx) in acciones" :key="`action-${item.id}-${idx}`">
            <ActionButton
                :href="typeof accion.href === 'function' ? accion.href(item) : accion.href"
                :color="accion.color"
                :name="accion.name"
                :iconName="accion.iconName"
                @click="() => accion.onClick && accion.onClick(item)"
            />
          </template>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="overflow-x-auto shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="(cabecera, index) in cabeceras"
            :key="`header-${index}`"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:"
          >
            {{ cabecera }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="item in cadena" :key="item.id || item.nombre">
          <td
            v-for="(campo, index) in campos"
            :key="`data-${item.id}-${index}`"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
          >
            {{ getValor(item, campo) }}
          </td>
          <td v-if="acciones.length > 0" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex items-center gap-2">
              <template v-for="(accion, idx) in acciones" :key="`action-${item.id}-${idx}`">
                <ActionButton
                    :href="typeof accion.href === 'function' ? accion.href(item) : accion.href"
                    :color="accion.color"
                    :name="accion.name"
                    :iconName="accion.iconName"
                    @click="() => accion.onClick && accion.onClick(item)"
                />
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>