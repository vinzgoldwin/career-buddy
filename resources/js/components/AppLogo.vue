<script setup lang="ts">
import logo from '../../images/career-buddy.png';
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <img :src="logo" alt="Career Buddy Logo" class="size-5 object-contain" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">Career Buddy</span>
    </div>
</template>
