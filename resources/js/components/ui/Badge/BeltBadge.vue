<script setup lang="ts">
defineProps < {
  belt: {
    name: string;
    slug: string;
  } | null;
} > ()

</script>

<template>
  <span :class="`belt belt--${belt?.slug}`">{{ belt?.name }}</span>
</template>

<style scoped lang="scss">
.belt {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: bold;

  &--white {
    background: #e5e5e5;
    color: black;
  }

  &--gray {
    background: #6b7280;
    color: white;
  }

  &--gray-white {
    background: #6b7280;
    color: white;
    border-left: 6px solid white;
  }

  &--gray-black {
    background: #6b7280;
    color: white;
    border-left: 6px solid black;
  }

  &--yellow {
    background: #facc15;
    color: black;
  }

  &--yellow-white {
    background: #facc15;
    color: black;
    border-left: 6px solid white;
  }

  &--yellow-black {
    background: #facc15;
    color: black;
    border-left: 6px solid black;
  }

  &--orange {
    background: #f97316;
    color: white;
  }

  &--orange-black {
    background: #f97316;
    color: white;
    border-left: 6px solid black;
  }

  &--green {
    background: #16a34a;
    color: white;
  }

  &--green-black {
    background: #16a34a;
    color: white;
    border-left: 6px solid black;
  }

  &--blue {
    background: #1e40af;
    color: white;
  }

  &--purple {
    background: #6b21a8;
    color: white;
  }

  &--brown {
    background: #78350f;
    color: white;
  }

  &--black {
    background: black;
    color: white;
  }
}
</style>