<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CalendarCheck, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import type { BreadcrumbItem } from '@/types';

type DailyRecord = {
    date: string;
    time_in: string | null;
    time_out: string | null;
    time_out_next_day: boolean;
    total_hours: number | null;
    manual_time_out: string | null;
    manual_time_out_status: string | null;
};

type Period = {
    start: string;
    end: string;
    label: string;
};

type Props = {
    employee: {
        id_number: string;
        full_name: string;
        department: string | null;
        company: string | null;
    };
    records: DailyRecord[];
    totalHours: number;
    daysPresent: number;
    period: Period;
    previousPeriod: Period;
    nextPeriod: Period;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'My Attendance', href: '/my-attendance' },
];

function goToPeriod(periodStart: string) {
    router.get(
        '/my-attendance',
        { period_start: periodStart },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="My Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Employee Info -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">{{ props.employee.full_name }}</h2>
                            <p class="text-muted-foreground text-sm">
                                {{ props.employee.id_number }}
                                <span v-if="props.employee.department"> · {{ props.employee.department }}</span>
                                <span v-if="props.employee.company"> · {{ props.employee.company }}</span>
                            </p>
                        </div>
                        <div class="flex gap-6 text-center">
                            <div>
                                <div class="text-2xl font-bold">{{ props.daysPresent }}</div>
                                <div class="text-muted-foreground text-xs">Days Present</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ props.totalHours > 0 ? `${props.totalHours}h` : '—' }}</div>
                                <div class="text-muted-foreground text-xs">Total Hours</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Attendance Records -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div class="flex items-center gap-2">
                        <CalendarCheck class="h-5 w-5" />
                        <CardTitle>Attendance</CardTitle>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button size="sm" variant="outline" @click="goToPeriod(props.previousPeriod.start)">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="min-w-[220px] text-center text-sm font-medium">
                            {{ props.period.label }}
                        </span>
                        <Button size="sm" variant="outline" @click="goToPeriod(props.nextPeriod.start)">
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Time In</TableHead>
                                <TableHead>Time Out</TableHead>
                                <TableHead class="text-right">Hours</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="record in props.records" :key="record.date">
                                <TableCell class="font-medium">{{ record.date }}</TableCell>
                                <TableCell>{{ record.time_in ?? '—' }}</TableCell>
                                <TableCell>
                                    <template v-if="record.time_out">
                                        {{ record.time_out }}
                                        <Badge v-if="record.time_out_next_day" variant="outline" class="ml-1 text-xs">+1d</Badge>
                                        <Badge v-if="record.manual_time_out_status === 'approved'" variant="outline" class="ml-1 text-xs">manual</Badge>
                                    </template>
                                    <template v-else-if="record.manual_time_out && record.manual_time_out_status">
                                        <span class="text-muted-foreground">{{ record.manual_time_out }}</span>
                                        <Badge v-if="record.manual_time_out_status === 'pending'" variant="outline" class="ml-1 text-xs text-yellow-600">pending</Badge>
                                        <Badge v-else-if="record.manual_time_out_status === 'rejected'" variant="destructive" class="ml-1 text-xs">rejected</Badge>
                                    </template>
                                    <span v-else>—</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ record.total_hours !== null ? `${record.total_hours}h` : '—' }}
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="record.time_in ? 'default' : 'secondary'">
                                        {{ record.time_in ? 'Present' : 'Absent' }}
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
