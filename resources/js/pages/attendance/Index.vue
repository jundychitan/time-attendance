<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CalendarCheck } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
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

type AttendanceRecord = {
    employee: {
        id: number;
        id_number: string;
        full_name: string;
        department: string | null;
    };
    date: string;
    time_in: string | null;
    time_out: string | null;
    total_hours: number | null;
};

type Props = {
    attendance: AttendanceRecord[];
    date: string;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Attendance', href: '/attendance' },
];

function onDateChange(event: Event) {
    const target = event.target as HTMLInputElement;
    router.get(
        '/attendance',
        { date: target.value },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between"
                >
                    <div class="flex items-center gap-2">
                        <CalendarCheck class="h-5 w-5" />
                        <CardTitle>Daily Attendance</CardTitle>
                    </div>
                    <Input
                        type="date"
                        :value="props.date"
                        class="max-w-[200px]"
                        @change="onDateChange"
                    />
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID Number</TableHead>
                                <TableHead>Employee</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead>Time In</TableHead>
                                <TableHead>Time Out</TableHead>
                                <TableHead class="text-right">
                                    Hours
                                </TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="record in props.attendance"
                                :key="record.employee.id"
                            >
                                <TableCell>
                                    {{ record.employee.id_number }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ record.employee.full_name }}
                                </TableCell>
                                <TableCell>
                                    {{ record.employee.department ?? '—' }}
                                </TableCell>
                                <TableCell>
                                    {{ record.time_in ?? '—' }}
                                </TableCell>
                                <TableCell>
                                    {{ record.time_out ?? '—' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{
                                        record.total_hours !== null
                                            ? `${record.total_hours}h`
                                            : '—'
                                    }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="
                                            record.time_in
                                                ? 'default'
                                                : 'secondary'
                                        "
                                    >
                                        {{
                                            record.time_in
                                                ? 'Present'
                                                : 'Absent'
                                        }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
