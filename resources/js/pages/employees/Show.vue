<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

type Props = {
    employee: {
        id: number;
        id_number: string;
        first_name: string;
        last_name: string;
        department: string | null;
        position: string | null;
        is_active: boolean;
        checkins_count: number;
    };
    attendance: {
        date: string;
        time_in: string | null;
        time_out: string | null;
        total_hours: number | null;
    }[];
    recentCheckins: {
        id: number;
        latitude: string;
        longitude: string;
        location_name: string | null;
        selfie_path: string;
        captured_at: string;
    }[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Employees', href: '/employees' },
    {
        title: `${props.employee.first_name} ${props.employee.last_name}`,
        href: `/employees/${props.employee.id}`,
    },
];
</script>

<template>
    <Head
        :title="`${props.employee.first_name} ${props.employee.last_name}`"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Employee Info -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>
                            {{ props.employee.first_name }}
                            {{ props.employee.last_name }}
                        </CardTitle>
                        <Badge
                            :variant="
                                props.employee.is_active
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{
                                props.employee.is_active
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </Badge>
                    </div>
                </CardHeader>
                <CardContent>
                    <dl class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div>
                            <dt class="text-muted-foreground text-sm">
                                ID Number
                            </dt>
                            <dd class="font-medium">
                                {{ props.employee.id_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-sm">
                                Department
                            </dt>
                            <dd class="font-medium">
                                {{ props.employee.department ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-sm">
                                Position
                            </dt>
                            <dd class="font-medium">
                                {{ props.employee.position ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground text-sm">
                                Total Check-ins
                            </dt>
                            <dd class="font-medium">
                                {{ props.employee.checkins_count }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <!-- Attendance History -->
            <Card>
                <CardHeader>
                    <CardTitle>Attendance This Month</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Time In</TableHead>
                                <TableHead>Time Out</TableHead>
                                <TableHead class="text-right">
                                    Hours
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="record in props.attendance"
                                :key="record.date"
                            >
                                <TableCell class="font-medium">
                                    {{ record.date }}
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
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Recent Checkins -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Check-ins</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Time</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead>Coordinates</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="checkin in props.recentCheckins"
                                :key="checkin.id"
                            >
                                <TableCell>
                                    {{
                                        new Date(
                                            checkin.captured_at,
                                        ).toLocaleString()
                                    }}
                                </TableCell>
                                <TableCell>
                                    {{ checkin.location_name ?? '—' }}
                                </TableCell>
                                <TableCell class="text-muted-foreground text-sm">
                                    {{ checkin.latitude }},
                                    {{ checkin.longitude }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
