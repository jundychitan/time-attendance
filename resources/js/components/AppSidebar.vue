<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    BookOpen,
    Bug,
    CalendarCheck,
    FolderGit2,
    LayoutGrid,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const isSuperAdmin = computed(() => user.value?.is_super_admin);
const isEmployee = computed(() => user.value?.role === 'employee');

const mainNavItems = computed<NavItem[]>(() => {
    // Employee role: only My Attendance
    if (isEmployee.value) {
        return [
            {
                title: 'My Attendance',
                href: '/my-attendance',
                icon: CalendarCheck,
            },
        ];
    }

    // Admin role
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Employees',
            href: '/employees',
            icon: Users,
        },
        {
            title: 'Attendance',
            href: '/attendance',
            icon: CalendarCheck,
        },
    ];

    if (isSuperAdmin.value) {
        items.push(
            {
                title: 'Admin Users',
                href: '/admin-users',
                icon: ShieldCheck,
            },
            {
                title: 'API Debug Logs',
                href: '/api-logs',
                icon: Bug,
            },
        );
    }

    return items;
});

const footerNavItems = computed<NavItem[]>(() => {
    if (! isSuperAdmin.value) {
        return [];
    }

    return [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/vue-starter-kit',
            icon: FolderGit2,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#vue',
            icon: BookOpen,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
