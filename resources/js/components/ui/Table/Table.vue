<script setup lang="ts">
import { ActionButton } from '@/components/ui/ActionButton';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { SelectSearch as SearchSelect} from '@/components/ui/SelectSearch'; 
import { Input } from '@/components/ui/input'; 

// ------------------- TIPOS -------------------
interface TableField {
  key: string;
  type?: 'text' | 'image' | 'select';
  options?: any[];
  valueKey?: string;
  labelKey?: string;
}

interface SearchSelectConfig {
  options: any[];
  valueKey?: string;
  labelKey?: string;
  placeholder?: string;
}

// ------------------- PROPS -------------------
const props = defineProps<{
  cadena: any[];
  cabeceras: string[];
  campos: (string | TableField)[];
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
  searchSelectConfig?: SearchSelectConfig; 

  // --- PROPS OPCIONALES DE ESTILO ---
  headerBgColor?: string; // e.g., 'bg-indigo-600'
  headerTextColor?: string; // e.g., 'text-white'
  // El filtro de fechas ha sido eliminado.
}>();

// ------------------- LÓGICA DE FILTROS -------------------
const selectedValue = ref<string | number | null>(null);

const normalizedCampos = computed(() => {
  return props.campos.map(campo => {
    if (typeof campo === 'string') {
      return { key: campo, type: 'text' };
    }
    return { key: campo.key, type: campo.type || 'text' };
  });
});


const filteredCadena = computed(() => {
  let filtered = props.cadena;

  // 1. FILTRO DE SEARCH SELECT
  if (props.searchSelectConfig && selectedValue.value) {
    const valueKey = props.searchSelectConfig.valueKey || 'id';
    filtered = filtered.filter(item => item[valueKey] === selectedValue.value);
  }

  return filtered;
});


// ------------------- LÓGICA DE VISTA ADAPTATIVA -------------------
const windowWidth = ref(window.innerWidth);

const handleResize = () => {
  windowWidth.value = window.innerWidth;
};

onMounted(() => {
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  window.removeEventListener('resize', handleResize);
});

const isSmallScreen = computed(() => windowWidth.value <= 768);

// ------------------- LÓGICA EXISTENTE -------------------
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

// ------------------- MODAL PREVIEW -------------------
const showModal = ref(false);
const modalContent = ref<any>(null);
const modalType = ref<'image' | 'text' | null>(null);

function openModal(content: any, type: 'image' | 'text' | null = null) {
  modalContent.value = content;
  modalType.value = type;
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  modalContent.value = null;
  modalType.value = null;
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && showModal.value) {
    closeModal();
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <div>
    <!-- Controles de Filtro y Acción -->
    <div :class="{'flex flex-col md:flex-row justify-between items-center p-4 gap-4': searchSelectConfig, 'flex justify-end p-4': !searchSelectConfig}">
      
      <!-- Filtro Search Select -->
      <div v-if="searchSelectConfig" class="w-full md:w-1/3 border border-gray-300 dark:border-white-600 rounded-md p-2">
        <SearchSelect
          v-model="selectedValue"
          :options="searchSelectConfig.options"
          :valueKey="searchSelectConfig.valueKey"
          :labelKey="searchSelectConfig.labelKey"
          :placeholder="searchSelectConfig.placeholder"
        />
      </div>
      
      <!-- Botón Agregar -->
      <div v-if="agregarAccion" class="w-full flex justify-end">
        <ActionButton
          :href="agregarAccion.href"
          :color="agregarAccion.color"
          :name="agregarAccion.name"
          :iconName="agregarAccion.iconName"
        />
      </div>
    </div>

    <!-- Vista Adaptativa (Mobile) -->
    <div v-if="isSmallScreen" class="grid grid-cols-1 gap-6 p-4">
        <div
            v-for="item in filteredCadena"
            :key="item.id || item.nombre"
            class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border border-gray-200 dark:border-gray-700"
        >
            <div v-for="(campo, index) in normalizedCampos" :key="`card-data-${item.id}-${index}`" class="mb-4 last:mb-0">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider dark:text-gray-400">
                {{ cabeceras[index] }}
                </p>
                <div class="mt-1 text-base text-gray-900 dark:text-white">
                  <div v-if="campo.type === 'image'">
                    <button type="button" @click="openModal(getValor(item, campo.key), 'image')" class="p-0 rounded-full overflow-hidden">
                      <img :src="getValor(item, campo.key)" alt="Imagen" class="w-16 h-16 rounded-full object-cover cursor-pointer block">
                    </button>
                  </div>
                  <div v-else class="">{{ getValor(item, campo.key) }}</div>
                </div>
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

    <!-- Vista Desktop (Tabla) -->
    <div v-else class="overflow-x-auto shadow-md rounded-lg">
      <!-- 'table-fixed' asegura que las columnas se alineen al ancho de la cabecera. -->
      <table class="min-w-full divide-y divide-gray-200 table-fixed">
        <!-- Encabezado de la tabla (THEAD) -->
        <!-- Se aplica el fondo (headerBgColor) al THEAD, quitando el color de texto para moverlo a los <th> -->
        <thead :class="[headerBgColor || 'bg-[#383E82]']"> 
          <tr>
            <th
              v-for="(cabecera, index) in cabeceras"
              :key="`header-${index}`"
              :class="[
                headerTextColor || 'text-gray-100', // APLICACIÓN DIRECTA DEL COLOR DE TEXTO AQUÍ
                'px-6 py-3 text-left text-xs font-medium uppercase tracking-wider'
                // Se removió 'dark:text-gray-400' para que headerTextColor controle el color en ambos modos.
              ]"
            >
              {{ cabecera }}
            </th>
            <!-- La columna de acciones (<th>) ha sido eliminada. -->
          </tr>
        </thead>
        <!-- Cuerpo de la tabla (TBODY) -->
        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
          <tr v-for="item in filteredCadena" :key="item.id || item.nombre">
            <td
              v-for="(campo, index) in normalizedCampos"
              :key="`data-${item.id}-${index}`"
              class="px-6 py-4 text-sm text-gray-900 dark:text-white text-left"
              >
              <!-- Se añade 'text-left' y 'break-words' para forzar la alineación y el uso completo del espacio de la celda. -->
              <div v-if="campo.type === 'image'">
                <button type="button" @click="openModal(getValor(item, campo.key), 'image')" class="p-0 rounded overflow-hidden">
                  <img :src="getValor(item, campo.key)" alt="Imagen" class="w-10 h-10 rounded-md object-cover cursor-pointer block">
                </button>
              </div>
              <div v-else class="break-words">{{ getValor(item, campo.key) }}</div>
            </td>
            <!-- Celda de acciones: Solo se renderiza si hay acciones, con el mismo ancho fijo. -->
            <td v-if="acciones.length > 0" class="px-6 py-4 text-right text-sm font-medium w-40">
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
            </td>
          </tr>
          <tr v-if="filteredCadena.length === 0">
              <td :colspan="cabeceras.length + (acciones.length > 0 ? 1 : 0)" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                  No se encontraron resultados que coincidan con los filtros aplicados.
              </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Global modal (aplica a mobile y desktop) -->
  <div v-if="showModal" @click.self="closeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 max-w-3xl w-full relative shadow-lg">
      <button @click="closeModal" aria-label="Cerrar" class="absolute top-3 right-3 bg-white/90 dark:bg-gray-800/90 hover:bg-white dark:hover:bg-gray-700 rounded-full p-3 text-gray-800 dark:text-gray-100 shadow-md">
        <span class="sr-only">Cerrar</span>
        ✕
      </button>
      <div v-if="modalType === 'image'">
        <img :src="modalContent" alt="Preview" class="w-full h-auto rounded-md object-contain max-h-[80vh] mx-auto">
      </div>
      <div v-else>
        <pre class="whitespace-pre-wrap text-sm text-gray-800 dark:text-gray-200">{{ modalContent }}</pre>
      </div>
    </div>
  </div>
</template>
