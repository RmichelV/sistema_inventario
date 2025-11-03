<script setup lang="ts" generic="T">
import { ref, computed, watch, onMounted, type PropType } from 'vue';

// 📦 Define las props de forma genérica
const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    required: true
  },
  options: {
    type: Array as PropType<T[]>,
    required: true
  },
  valueKey: {
    type: String,
    default: 'id'
  },
  labelKey: {
    type: String,
    default: 'name'
  },
  searchKeys: {
    type: Array as PropType<Array<keyof T>>,
    default: () => ['name', 'code']
  },
  placeholder: {
    type: String,
    default: 'Buscar...'
  },
  name: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);
const searchTerm = ref('');
const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

// Sincronizar el valor del select con el input
watch(() => props.modelValue, (newValue) => {
  const selectedOption = props.options.find(option => (option as any)[props.valueKey] === newValue);
  searchTerm.value = selectedOption ? (selectedOption as any)[props.labelKey] as string : '';
}, { immediate: true });

// Filtrar y ordenar opciones
const filteredOptions = computed(() => {
  if (!searchTerm.value) {
    return [...props.options].sort((a, b) =>
      String((a as any)[props.labelKey] || '').localeCompare(String((b as any)[props.labelKey] || ''))
    );
  }

  const searchLower = searchTerm.value.toLowerCase();

  return [...props.options]
    .filter(option =>
      props.searchKeys.some(key =>
        String((option as any)[key] || '').toLowerCase().includes(searchLower)
      )
    )
    .sort((a, b) =>
      String((a as any)[props.labelKey] || '').localeCompare(String((b as any)[props.labelKey] || ''))
    );
});

// Seleccionar un valor
const selectOption = (option: T) => {
  emit('update:modelValue', (option as any)[props.valueKey]);
  searchTerm.value = (option as any)[props.labelKey] as string;
  isOpen.value = false;
};

// Cerrar dropdown si se hace clic fuera
const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
</script>

<template>
  <div ref="dropdownRef" class="relative w-full">
    <input
      type="text"
      v-model="searchTerm"
      :placeholder="placeholder"
      class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full p-2 cursor-text bg-white text-black"
      @focus="isOpen = true"
      @input="isOpen = true"
    />
     <input 
      type="hidden" 
      :name="name" 
      :id="id" 
      :value="modelValue" 
    />
    <div
      v-if="isOpen"
      class="absolute bg-white border border-gray-300 w-full shadow-md mt-1 rounded-md max-h-40 overflow-y-auto z-10 force-light-dropdown"
      >
      <div
        v-for="(option, index) in filteredOptions"
        :key="index"
        @click="selectOption(option)"
        class="p-2 text-black hover:bg-gray-200 cursor-pointer bg-white"
        >
        {{ (option as any)[labelKey] }}
      </div>
      <div v-if="filteredOptions.length === 0" class="p-2 text-gray-600 bg-white">
        No se encontraron resultados
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Forzar fondo blanco y texto negro en el input y dropdown independientemente del tema */
.force-light-dropdown {
  background-color: #ffffff !important;
  color: #000000 !important;
}

.force-light-dropdown > div {
  background-color: #ffffff !important;
  color: #000000 !important;
}

.force-light-dropdown .p-2 {
  color: #000000 !important;
  background-color: #ffffff !important;
}

.force-light-dropdown .p-2:hover {
  background-color: #e5e7eb !important;
  color: #000000 !important;
}

.force-light-dropdown .text-gray-600 {
  color: #4b5563 !important;
  background-color: #ffffff !important;
}

/* Asegurar que el input siempre sea blanco con texto negro */
input[type="text"] {
  background-color: #ffffff !important;
  color: #000000 !important;
}

input[type="text"]::placeholder {
  color: #999999 !important;
}
</style>