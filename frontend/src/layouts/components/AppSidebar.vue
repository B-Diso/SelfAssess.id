<template>
  <Sidebar variant="inset" collapsible="icon">
    <!-- Header with Logo -->
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            size="lg"
            class="hover:bg-transparent hover:text-inherit cursor-default"
          >
            <div class="flex items-center gap-2.5">
              <img
                src="/SelfAssess.id-icon.png"
                alt="SelfAssess.id Logo"
                :class="[
                  'transition-all duration-300 ease-in-out shrink-0',
                  state === 'collapsed' ? 'h-7 w-7' : 'h-10 w-10',
                ]"
              />
              <div
                v-if="state !== 'collapsed'"
                class="flex flex-col leading-tight text-left"
              >
                <span class="font-bold text-base text-teal-800 tracking-tight"
                  >SelfAssess.id</span
                >
              </div>
            </div>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <!-- Navigation Menu -->
    <SidebarContent>
      <SidebarGroup v-for="section in menuSections" :key="section.title">
        <SidebarGroupLabel>{{ section.title }}</SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem v-for="item in section.items" :key="item.path">
            <SidebarMenuButton
              :tooltip="item.label"
              @click="navigateTo(item.path)"
            >
              <component :is="item.icon" />
              <span>{{ item.label }}</span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <!-- User Footer -->
    <SidebarFooter>
      <SidebarMenu>
        <!-- User Profile -->
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <router-link to="/profile" class="flex items-center gap-2">
              <Avatar class="h-8 w-8 rounded-lg">
                <AvatarFallback class="rounded-lg">
                  {{ userInitials }}
                </AvatarFallback>
              </Avatar>
              <div class="grid flex-1 text-left text-sm leading-tight">
                <span class="truncate font-semibold">{{ user?.name }}</span>
                <span class="truncate text-xs">{{ user?.email }}</span>
              </div>
            </router-link>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <!-- Logout Button -->
        <SidebarMenuItem>
          <SidebarMenuButton tooltip="Logout" @click="handleLogout">
            <LogOutIcon class="size-4" />
            <span>Logout</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>

    <!-- Rail for visual feedback -->
    <SidebarRail />
  </Sidebar>
</template>

<script setup lang="ts">
import { computed,onErrorCaptured } from "vue";
import { useRouter } from "vue-router";
import type { Component } from "vue";
import { toast } from "vue-sonner";
import { useAuthStore } from "@/features/auth/stores/authStore";
import { useUserStore } from "@/features/auth/stores/userStore";
import { usePermissions } from "@/composables/userPermissions";
import { storeToRefs } from "pinia";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarRail,
  useSidebar,
} from "@/components/ui/sidebar";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import {
  LayoutDashboard,
  Users,
  Building2,
  Shield,
  Globe2,
  LogOut as LogOutIcon,
  ClipboardCheck,
  FileText,
  FolderOpen,
  List,
} from "lucide-vue-next";
import { PERMISSIONS, ROLES } from "@/lib/constants";
import { useRoles } from "@/composables/userRoles";

const authStore = useAuthStore();
const userStore = useUserStore();
const router = useRouter();
const { state } = useSidebar();
const { user } = storeToRefs(userStore);

const { hasPermission } = usePermissions();
const { hasRole } = useRoles();

// Capture and suppress harmless unmounting errors during navigation
onErrorCaptured((err) => {
  if (
    err instanceof TypeError && 
    (err.message.includes("reading 'type'") || err.message.includes("reading 'parentNode'"))
  ) {
    // This is likely a Vue internal error during unmount/transition race conditions
    // Suppress it to prevent app crash
    return false;
  }
  // Propagate other errors
  return true;
});

// Computed property to check if user data is loaded
const isUserLoaded = computed(() => {
  // If we have user data, we consider it loaded regardless of background refreshing
  if (userStore.user) return true;
  // Only if we don't have user data do we check loading state
  return !userStore.isLoading;
});

type MenuItem = {
  path: string;
  label: string;
  icon: Component;
};

type MenuSection = {
  title: string;
  items: MenuItem[];
};

// Derive initials from user name; fallback to email first letter
const userInitials = computed(() => {
  const name = user.value?.name?.trim();
  if (name && name.length > 0) {
    const parts = name.split(/\s+/).filter((p) => p.length > 0);
    if (parts.length >= 2) {
      const first = parts[0]!.charAt(0).toUpperCase();
      const last = parts[parts.length - 1]!.charAt(0).toUpperCase();
      return first + last;
    }
    if (parts.length === 1) {
      const part = parts[0]!;
      const hyphenParts = part.split("-").filter((p) => p.length > 0);
      if (hyphenParts.length >= 2) {
        const first = hyphenParts[0]!.charAt(0).toUpperCase();
        const last =
          hyphenParts[hyphenParts.length - 1]!.charAt(0).toUpperCase();
        return first + last;
      }
      return part.charAt(0).toUpperCase();
    }
  }
  const email = user.value?.email;
  if (email && email.length > 0) return email.charAt(0).toUpperCase();
  return "U";
});

const menuSections = computed<MenuSection[]>(() => {
  try {
    // Return empty array while user data is loading to prevent crash
    if (!isUserLoaded.value) {
      return [];
    }
  const sections: MenuSection[] = [];

  const generalItems: MenuItem[] = [
    {
      path: "/dashboard",
      label: "Dashboard",
      icon: LayoutDashboard,
    },
  ];

  // Use pure permission check for attachments
  if (
    hasPermission(PERMISSIONS.VIEW_ATTACHMENTS)
  ) {
    generalItems.push({
      path: "/attachments",
      label: "Attachments",
      icon: FolderOpen,
    });
  }

  if (generalItems.length > 0) {
    sections.push({ title: "General", items: generalItems });
  }

  // Assessment Section
  const assessmentItems: MenuItem[] = [];

  // Use pure permission check for assessments
  if (
    hasPermission(PERMISSIONS.VIEW_ASSESSMENTS)
  ) {
    assessmentItems.push({
      path: "/assessments",
      label: "Assessments",
      icon: ClipboardCheck,
    });
  }

  if (assessmentItems.length > 0) {
    sections.push({ title: "Assessment", items: assessmentItems });
  }

  // Standard Section - Hide for Organization Users
  const standardItems: MenuItem[] = [];

  // Use pure permission check for standards
  // Only show if user has permission AND is NOT an Organization User
  if (
    hasPermission(PERMISSIONS.VIEW_STANDARDS)
  ) {
    standardItems.push({
      path: "/standards",
      label: "Standards",
      icon: FileText,
    });
  }

  if (standardItems.length > 0) {
    sections.push({ title: "Standard", items: standardItems });
  }

  // Admin Section - based on user management permission
  // Show Admin section for ALL users with user management permissions EXCEPT Organization Users

  let titleAdmin = ""
  if (hasRole(ROLES.ORGANIZATION_ADMIN) || hasRole(ROLES.SUPER_ADMIN)) {
    titleAdmin = "Admin"
  }

  if (
      hasPermission(PERMISSIONS.VIEW_USERS)
    ) {
  sections.push({
      title: titleAdmin,
      items: [
        {
          path: "/users",
          label: "User Management",
          icon: Users,
        },
      ],
    });
  }

  const superAdminItems: MenuItem[] = [];

  let title = ""
  if (userStore.isSuperAdmin) {
    if (
      hasPermission(PERMISSIONS.VIEW_USERS)
    ) {
      superAdminItems.push({
        path: "/admin/users",
        label: "Global User Management",
        icon: Globe2,
      });
    }

    title = "Super Admin"
  }

    // Organizations - requires create and delete organizations permissions
    if (hasPermission(PERMISSIONS.VIEW_ORGANIZATIONS)) {
      superAdminItems.push({
        path: "/organizations",
        label: "Organizations",
        icon: Building2,
      });
    }

    // Standard Report - view assessment adoption across organizations
    if (userStore.isSuperAdmin) {
      superAdminItems.push({
        path: "/standards/report",
        label: "Standard Report",
        icon: List,
      });
    }

    if (
      hasPermission(PERMISSIONS.VIEW_ROLES)
    ) {
      superAdminItems.push({
        path: "/roles",
        label: "Roles",
        icon: Shield,
      });
    }

  sections.push({
    title: title,
    items: superAdminItems,
  });

  return sections;
} catch (err) {
    console.error("Error calculating menu sections:", err);
    return [];
  }
});

async function handleLogout() {
  try {
    toast.success("Logged out successfully");
    authStore.logout();
    userStore.clearUser();
    // Redirect to login page
    window.location.href = "/login";
  } catch (err) {
    console.error("Logout error:", err);
    // Force redirect even if error
    window.location.href = "/login";
  }
}

function navigateTo(path: string) {
  router.push(path);
}
</script>
