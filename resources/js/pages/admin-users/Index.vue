<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShieldCheck, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    company: string | null;
    is_super_admin: boolean;
    created_at: string;
};

const props = defineProps<{ users: AdminUser[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Admin Users', href: '/admin-users' },
];

const deletingId = ref<number | null>(null);

function confirmDelete(id: number) {
    deletingId.value = id;
}

function deleteUser(id: number) {
    router.delete(`/admin-users/${id}`);
    deletingId.value = null;
}
</script>

<template>
    <Head title="Admin Users" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="h-5 w-5" />
                        <CardTitle>Admin Users</CardTitle>
                    </div>
                    <Button as-child>
                        <Link href="/admin-users/create">
                            <Plus class="mr-1 h-4 w-4" /> Add
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Company</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in props.users" :key="user.id">
                                <TableCell class="font-medium">{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>{{ user.company ?? '—' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="user.is_super_admin ? 'default' : 'secondary'">
                                        {{ user.is_super_admin ? 'Super Admin' : 'Admin' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button size="sm" variant="outline" as-child>
                                            <Link :href="`/admin-users/${user.id}/edit`">Edit</Link>
                                        </Button>
                                        <template v-if="deletingId === user.id">
                                            <span class="text-destructive text-sm">Sure?</span>
                                            <Button size="sm" variant="destructive" @click="deleteUser(user.id)">Yes</Button>
                                            <Button size="sm" variant="ghost" @click="deletingId = null">No</Button>
                                        </template>
                                        <Button v-else size="sm" variant="destructive" @click="confirmDelete(user.id)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
