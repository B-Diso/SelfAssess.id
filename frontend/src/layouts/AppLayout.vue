<template>
  <SidebarProvider class="h-screen overflow-hidden">
    <AppSidebar />
    <SidebarInset class="overflow-hidden">
      <AppHeader />
      <div class="flex-1 overflow-auto p-3 box-border">
        <router-view />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import { SidebarProvider, SidebarInset } from "@/components/ui/sidebar";
import AppSidebar from "./components/AppSidebar.vue";
import AppHeader from "./components/AppHeader.vue";
</script>
