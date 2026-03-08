<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CalendarCheck, Clock, UserCheck, Users } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    stats: {
        total_employees: number;
        present_today: number;
        absent_today: number;
        average_hours: number;
    };
    recentCheckins: {
        id: number;
        employee_name: string;
        employee_id_number: string;
        captured_at: string;
        location_name: string | null;
    }[];
    monthlyTrend: {
        date: string;
        present: number;
        absent: number;
    }[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

function formatDateTime(dt: string): string {
    return new Date(dt).toLocaleString();
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Total Employees
                        </CardTitle>
                        <Users class="text-muted-foreground h-4 w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ props.stats.total_employees }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Present Today
                        </CardTitle>
                        <UserCheck class="text-muted-foreground h-4 w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ props.stats.present_today }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Absent Today
                        </CardTitle>
                        <CalendarCheck class="text-muted-foreground h-4 w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ props.stats.absent_today }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium">
                            Avg. Hours Worked
                        </CardTitle>
                        <Clock class="text-muted-foreground h-4 w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ props.stats.average_hours }}h
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Monthly Trend -->
            <Card>
                <CardHeader>
                    <CardTitle>Monthly Attendance Trend</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-1" style="height: 120px">
                        <div
                            v-for="day in props.monthlyTrend"
                            :key="day.date"
                            class="flex flex-1 flex-col items-center gap-0.5"
                        >
                            <div
                                class="w-full rounded-t bg-green-500"
                                :style="{
                                    height:
                                        props.stats.total_employees > 0
                                            ? `${(day.present / props.stats.total_employees) * 100}px`
                                            : '0px',
                                }"
                                :title="`${day.date}: ${day.present} present`"
                            />
                        </div>
                    </div>
                    <div
                        class="text-muted-foreground mt-2 text-center text-xs"
                    >
                        Daily present count for the current month
                    </div>
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
                                <TableHead>Employee</TableHead>
                                <TableHead>ID Number</TableHead>
                                <TableHead>Time</TableHead>
                                <TableHead>Location</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="checkin in props.recentCheckins"
                                :key="checkin.id"
                            >
                                <TableCell class="font-medium">
                                    {{ checkin.employee_name }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ checkin.employee_id_number }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ formatDateTime(checkin.captured_at) }}
                                </TableCell>
                                <TableCell>
                                    {{ checkin.location_name ?? '\u2014' }}
                                </TableCell>
                            </TableRow>
                            <TableRow
                                v-if="props.recentCheckins.length === 0"
                            >
                                <TableCell :colspan="4" class="text-center">
                                    No check-ins recorded yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
