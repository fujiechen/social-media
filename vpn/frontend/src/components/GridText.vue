<script setup>
defineProps({
  text: {
    type: String,
    required: true,
  },
  size: {
    type: [Number, String], // Allow both number and string input
    required: false,
    default: '15px', // Default size as a string
  },
});
</script>

<template>
  <div :style="{ fontSize: size }">
    <b>{{ text }}</b>
  </div>
</template>
