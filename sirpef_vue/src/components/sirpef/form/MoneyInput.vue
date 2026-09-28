<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';

const props = withDefaults(defineProps<{
  modelValue: string | number | null | undefined;
  name?: string;
  id?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  readonly?: boolean;
  inputClass?: string;
  asNumber?: boolean;
}>(), {
  modelValue: '',
  name: '',
  id: '',
  placeholder: '0,00',
  required: false,
  disabled: false,
  readonly: false,
  inputClass: 'w-full bg-gray-100 text-gray-900 mt-1 p-3 rounded-lg',
  asNumber: false
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void;
  (e: 'change', value: string | number): void;
  (e: 'blur', event: FocusEvent): void;
  (e: 'focus', event: FocusEvent): void;
}>();

const inputRef = ref<HTMLInputElement | null>(null);
const displayValue = ref('');
const isFocused = ref(false);

// Formatear valor numérico a representación visual (ej: 1234.56 -> "1.234,56")
const formatDisplay = (val: string | number | null | undefined, forceDecimals = true): string => {
  if (val === null || val === undefined || val === '') return '';
  const num = typeof val === 'number' ? val : parseFloat(val.toString());
  if (isNaN(num)) return '';

  return num.toLocaleString('de-DE', {
    minimumFractionDigits: forceDecimals ? 2 : 0,
    maximumFractionDigits: 2,
  });
};

// Parsear string visual ("1.234,56") a número
const parseToNumber = (val: string | number | null | undefined): number | null => {
  if (val === null || val === undefined || val === '') return null;
  if (typeof val === 'number') return isNaN(val) ? null : val;
  
  // Limpiar separadores de miles y convertir coma en punto
  const cleaned = val.toString().replace(/\./g, '').replace(',', '.');
  const num = parseFloat(cleaned);
  return isNaN(num) ? null : num;
};

// Formatear string durante la escritura
const formatTyping = (text: string): { formatted: string; raw: string | number } => {
  if (!text) {
    return { formatted: '', raw: props.asNumber ? 0 : '' };
  }

  const isNegative = text.trim().startsWith('-');
  const hasComma = text.includes(',');
  const parts = text.split(',');
  const intPart = parts[0] || '';
  const decPart = parts.length > 1 ? parts.slice(1).join('') : null;

  // Limpiar parte entera
  const intDigits = intPart.replace(/\D/g, '');
  let formattedInt = '';
  if (intDigits) {
    const cleanInt = intDigits.replace(/^0+(?=\d)/, '');
    formattedInt = cleanInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  } else if (hasComma) {
    formattedInt = '0';
  }

  // Limpiar parte decimal (máximo 2 dígitos)
  let formattedDec = '';
  if (decPart !== null) {
    formattedDec = decPart.replace(/\D/g, '').slice(0, 2);
  }

  // Armar texto visual
  let formatted = '';
  if (formattedInt || hasComma) {
    formatted = (isNegative ? '-' : '') + (formattedInt || '0');
    if (hasComma) {
      formatted += ',' + formattedDec;
    }
  }

  // Calcular valor crudo para base de datos / modelValue
  let raw: string | number = '';
  if (intDigits || (decPart !== null && formattedDec)) {
    const rawNumberStr = (isNegative ? '-' : '') + (intDigits || '0') + (decPart !== null && formattedDec ? '.' + formattedDec : '');
    raw = props.asNumber ? parseFloat(rawNumberStr) : rawNumberStr;
  } else {
    raw = props.asNumber ? 0 : '';
  }

  return { formatted, raw };
};

// Manejar cambios en el input
const handleInput = (event: Event) => {
  const input = event.target as HTMLInputElement;
  const currentVal = input.value;
  const cursorPos = input.selectionStart || 0;

  // Contar caracteres no-puntos antes del cursor
  const nonDotsBefore = currentVal.slice(0, cursorPos).replace(/\./g, '').length;

  const { formatted, raw } = formatTyping(currentVal);
  displayValue.value = formatted;

  emit('update:modelValue', raw);

  // Calcular nueva posición del cursor
  nextTick(() => {
    if (!inputRef.value) return;
    let newPos = 0;
    let count = 0;
    for (let i = 0; i < formatted.length; i++) {
      if (count >= nonDotsBefore) {
        newPos = i;
        break;
      }
      if (formatted[i] !== '.') {
        count++;
      }
      newPos = i + 1;
    }
    inputRef.value.setSelectionRange(newPos, newPos);
  });
};

// Teclas especiales (. , Backspace, Delete)
const onKeyDown = (e: KeyboardEvent) => {
  if (props.readonly || props.disabled) return;
  const input = inputRef.value;
  if (!input) return;

  // Interceptar punto o coma para separador decimal
  if (e.key === '.' || e.key === ',') {
    e.preventDefault();
    const val = input.value;
    const start = input.selectionStart || 0;
    const end = input.selectionEnd || 0;

    const commaIdx = val.indexOf(',');
    if (commaIdx !== -1 && (commaIdx < start || commaIdx >= end)) {
      // Si ya hay coma fuera de la selección, posicionar cursor tras la coma
      input.setSelectionRange(commaIdx + 1, commaIdx + 1);
      return;
    }

    const before = val.slice(0, start);
    const after = val.slice(end);
    const newVal = (before || '0') + ',' + after;

    const nonDotsBefore = (before || '0').replace(/\./g, '').length + 1; // +1 por la coma
    const { formatted, raw } = formatTyping(newVal);
    displayValue.value = formatted;
    emit('update:modelValue', raw);

    nextTick(() => {
      if (!inputRef.value) return;
      let newPos = 0;
      let count = 0;
      for (let i = 0; i < formatted.length; i++) {
        if (count >= nonDotsBefore) {
          newPos = i;
          break;
        }
        if (formatted[i] !== '.') {
          count++;
        }
        newPos = i + 1;
      }
      inputRef.value.setSelectionRange(newPos, newPos);
    });
    return;
  }

  // Backspace sobre punto de miles: saltar punto y borrar dígito previo
  if (e.key === 'Backspace' && input.selectionStart === input.selectionEnd) {
    const pos = input.selectionStart || 0;
    if (pos > 0 && input.value[pos - 1] === '.') {
      e.preventDefault();
      const val = input.value;
      const newVal = val.slice(0, pos - 2) + val.slice(pos);
      const nonDotsBefore = val.slice(0, pos - 2).replace(/\./g, '').length;

      const { formatted, raw } = formatTyping(newVal);
      displayValue.value = formatted;
      emit('update:modelValue', raw);

      nextTick(() => {
        if (!inputRef.value) return;
        let newPos = 0;
        let count = 0;
        for (let i = 0; i < formatted.length; i++) {
          if (count >= nonDotsBefore) {
            newPos = i;
            break;
          }
          if (formatted[i] !== '.') {
            count++;
          }
          newPos = i + 1;
        }
        inputRef.value.setSelectionRange(newPos, newPos);
      });
      return;
    }
  }

  // Delete sobre punto de miles: saltar punto y borrar dígito posterior
  if (e.key === 'Delete' && input.selectionStart === input.selectionEnd) {
    const pos = input.selectionStart || 0;
    if (pos < input.value.length && input.value[pos] === '.') {
      e.preventDefault();
      const val = input.value;
      const newVal = val.slice(0, pos) + val.slice(pos + 2);
      const nonDotsBefore = val.slice(0, pos).replace(/\./g, '').length;

      const { formatted, raw } = formatTyping(newVal);
      displayValue.value = formatted;
      emit('update:modelValue', raw);

      nextTick(() => {
        if (!inputRef.value) return;
        let newPos = 0;
        let count = 0;
        for (let i = 0; i < formatted.length; i++) {
          if (count >= nonDotsBefore) {
            newPos = i;
            break;
          }
          if (formatted[i] !== '.') {
            count++;
          }
          newPos = i + 1;
        }
        inputRef.value.setSelectionRange(newPos, newPos);
      });
      return;
    }
  }
};

// Pegar montos
const onPaste = (e: ClipboardEvent) => {
  e.preventDefault();
  const text = e.clipboardData?.getData('text') || '';
  if (!text) return;

  let clean = text.trim();
  // Limpieza de formato pegado
  if (clean.includes('.') && clean.includes(',')) {
    const lastDot = clean.lastIndexOf('.');
    const lastComma = clean.lastIndexOf(',');
    if (lastComma > lastDot) {
      clean = clean.replace(/\./g, '');
    } else {
      clean = clean.replace(/,/g, '').replace('.', ',');
    }
  } else if (clean.includes('.')) {
    const dots = (clean.match(/\./g) || []).length;
    if (dots > 1) {
      clean = clean.replace(/\./g, '');
    } else {
      const parts = clean.split('.');
      if (parts[1] && parts[1].length <= 2) {
        clean = parts[0] + ',' + parts[1];
      } else {
        clean = clean.replace('.', '');
      }
    }
  }

  const { formatted, raw } = formatTyping(clean);
  displayValue.value = formatted;
  emit('update:modelValue', raw);
  emit('change', raw);
};

const onFocus = (e: FocusEvent) => {
  isFocused.value = true;
  emit('focus', e);
  // Seleccionar todo para facilitar edición rápida
  if (inputRef.value) {
    inputRef.value.select();
  }
};

const onBlur = (e: FocusEvent) => {
  isFocused.value = false;
  if (displayValue.value) {
    const num = parseToNumber(displayValue.value);
    if (num !== null && !isNaN(num)) {
      displayValue.value = num.toLocaleString('de-DE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
      const raw = props.asNumber ? num : num.toFixed(2);
      emit('update:modelValue', raw);
      emit('change', raw);
    } else {
      displayValue.value = '';
      const emptyRaw = props.asNumber ? 0 : '';
      emit('update:modelValue', emptyRaw);
      emit('change', emptyRaw);
    }
  } else {
    displayValue.value = '';
    const emptyRaw = props.asNumber ? 0 : '';
    emit('update:modelValue', emptyRaw);
    emit('change', emptyRaw);
  }
  emit('blur', e);
};

// Sincronizar cuando cambia modelValue externamente
watch(
  () => props.modelValue,
  (newVal) => {
    if (isFocused.value) {
      // Si está enfocado y el número es equivalente, no interferir con la escritura
      const currentNum = parseToNumber(displayValue.value);
      const incomingNum = parseToNumber(newVal);
      if (currentNum !== null && incomingNum !== null && currentNum === incomingNum) {
        return;
      }
    }

    if (newVal === null || newVal === undefined || newVal === '') {
      displayValue.value = '';
      return;
    }

    const num = typeof newVal === 'number' ? newVal : parseFloat(newVal.toString());
    if (isNaN(num)) {
      displayValue.value = '';
      return;
    }

    displayValue.value = num.toLocaleString('de-DE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  },
  { immediate: true }
);
</script>

<template>
  <input
    ref="inputRef"
    type="text"
    inputmode="decimal"
    :name="name"
    :id="id"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    :readonly="readonly"
    :class="inputClass"
    :value="displayValue"
    @input="handleInput"
    @keydown="onKeyDown"
    @paste="onPaste"
    @focus="onFocus"
    @blur="onBlur"
  />
</template>
