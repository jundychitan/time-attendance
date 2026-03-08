<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Plus } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Employee = {
    id: number;
    id_number: string;
    first_name: string;
    last_name: string;
    department: string | null;
    position: string | null;
    is_active: boolean;
    checkins_count: number;
};

type Props = {
    employees: {
        data: Employee[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Employees', href: '/employees' },
];

const search = ref(props.filters.search ?? '');

let debounceTimer: ReturnType<typeof setTimeout>;
watch(search, (value) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            '/employees',
            { search: value || undefined },
            { preserveState: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="Employees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between"
                >
                    <div class="flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        <CardTitle>Employees</CardTitle>
                    </div>
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="search"
                            placeholder="Search by name or ID..."
                            class="max-w-sm"
                        />
                        <Button as-child>
                            <Link href="/employees/create">
                                <Plus class="mr-1 h-4 w-4" />
                                Add
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID Number</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">
                                    Check-ins
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="emp in props.employees.data"
                                :key="emp.id"
                            >
                                <TableCell>
                                    <Link
                                        :href="`/employees/${emp.id}`"
                                        class="text-primary hover:underline"
                                    >
                                        {{ emp.id_number }}
                                    </Link>
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ emp.first_name }} {{ emp.last_name }}
                                </TableCell>
                                <TableCell>
                                    {{ emp.department ?? '—' }}
                                </TableCell>
                                <TableCell>
                                    {{ emp.position ?? '—' }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            emp.is_active
                                                ? 'default'
                                                : 'secondary'
                                        "
                                    >
                                        {{
                                            emp.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ emp.checkins_count }}
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-if="props.employees.data.length === 0"
                            >
                                <TableCell :colspan="6" class="text-center">
                                    No employees found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="props.employees.last_page > 1"
                        class="mt-4 flex justify-center gap-1"
                    >
                        <template
                            v-for="link in props.employees.links"
                            :key="link.label"
                        >
                            <Button
                                v-if="link.url"
                                :variant="
                                    link.active ? 'default' : 'outline'
                                "
                                size="sm"
                                as-child
                            >
                                <Link
                                    :href="link.url"
                                    v-html="link.label"
                                    preserve-state
                                />
                            </Button>
                        </template>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
