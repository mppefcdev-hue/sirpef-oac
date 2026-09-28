<script setup lang="ts">
// @ts-nocheck
import { useRoute } from 'vue-router'

defineProps<{
  links: any[]
}>()

const route = useRoute()

const isNext = (label: string) => {
  if (!label) return false
  const l = label.toLowerCase()
  return l.includes('next') || l.includes('siguiente') || l.includes('raquo')
}

const getTarget = (url: string | null) => {
  if (!url) return {}
  try {
    const parsed = new URL(url, window.location.origin)
    const newQuery: Record<string, any> = { ...route.query }
    parsed.searchParams.forEach((val, key) => {
      newQuery[key] = val
    })
    return {
      path: route.path,
      query: newQuery
    }
  } catch (e) {
    const match = url ? url.match(/[?&]page=(\d+)/) : null
    return {
      path: route.path,
      query: {
        ...route.query,
        ...(match ? { page: match[1] } : {})
      }
    }
  }
}
</script>

<template>
  <div class="mt-6 -mb-1 flex flex-wrap">
    <template v-for="(link, key) in links" :key="key">      
      <div
        v-if="link.url === null"        
        class="mr-1 mb-1 px-4 py-3 text-sm border rounded text-gray-400 select-none"
        :class="{ 'ml-auto': isNext(link.label) }"
        v-html="link.label"
      ></div>
      <RouterLink
        v-if="link.url !== null"        
        class="mr-1 mb-1 px-4 py-3 text-sm border rounded hover:bg-gray-100 focus:border-indigo-500 focus:text-indigo-500 transition-colors"
        :class="{ 'bg-[#010c41] text-white font-bold border-[#010c41]': link.active, 'bg-white text-gray-700': !link.active, 'ml-auto': isNext(link.label) }"
        :to="getTarget(link.url)"
        v-html="link.label"
      ></RouterLink>
    </template>
  </div>
</template>





