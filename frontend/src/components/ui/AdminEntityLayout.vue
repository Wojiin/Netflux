<script setup>
import NeonFrame from "./NeonFrame.vue";
import NeonScrollPanel from "./NeonScrollPanel.vue";
import PageHeader from "./PageHeader.vue";
import StatusMessage from "./StatusMessage.vue";

defineProps({
  eyebrow: {
    type: String,
    required: true,
  },
  eyebrowClass: {
    type: String,
    default: "",
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  viewName: {
    type: String,
    required: true,
  },
  modelValue: {
    type: String,
    default: "",
  },
  searchPlaceholder: {
    type: String,
    required: true,
  },
  searchTone: {
    type: String,
    default: "cyan",
  },
  searchClass: {
    type: String,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  loadingMessage: {
    type: String,
    required: true,
  },
  error: {
    type: String,
    default: "",
  },
  maxHeight: {
    type: String,
    default: "min(58vh, 42rem)",
  },
});

defineEmits(["update:modelValue"]);
</script>

<template>
  <section class="space-y-8">
    <PageHeader
      :eyebrow="eyebrow"
      :eyebrow-class="eyebrowClass"
      :title="title"
      :description="description"
      :view-name="viewName"
    >
      <template #actions>
        <slot name="actions" />
      </template>
    </PageHeader>

    <NeonFrame class="mb-6 p-5" :tone="searchTone" :padded="false">
      <input
        :value="modelValue"
        type="text"
        :placeholder="searchPlaceholder"
        :class="searchClass"
        @input="$emit('update:modelValue', $event.target.value)"
      />
    </NeonFrame>

    <StatusMessage v-if="loading">{{ loadingMessage }}</StatusMessage>
    <StatusMessage v-else-if="error" variant="error">Erreur : {{ error }}</StatusMessage>

    <NeonScrollPanel
      v-else
      class="grid gap-5 pr-2"
      :max-height="maxHeight"
    >
      <slot />
    </NeonScrollPanel>
  </section>
</template>
