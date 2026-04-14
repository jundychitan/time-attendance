<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarCheck, ChevronLeft, ChevronRight, Clock } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
    checkin_id: number | null;
    manual_time_out: string | null;
    manual_time_out_status: string | null;
    regular_hours: number | null;
    overtime_hours: number | null;
    overtime_status: string | null;
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
    totalRegularHours: number;
    totalOvertimeHours: number;
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

const editingCheckinId = ref<number | null>(null);
const manualTimeOutInput = ref('');

function startEdit(checkinId: number) {
    editingCheckinId.value = checkinId;
    manualTimeOutInput.value = '';
}

function cancelEdit() {
    editingCheckinId.value = null;
    manualTimeOutInput.value = '';
}

function submitManualTimeOut(checkinId: number, date: string) {
    const form = useForm({
        manual_time_out: `${date} ${manualTimeOutInput.value}`,
    });
    form.patch(`/attendance/checkins/${checkinId}/manual-time-out`, {
        onSuccess: () => {
            editingCheckinId.value = null;
            manualTimeOutInput.value = '';
        },
    });
}
</script>

<template>
    <Head title="My Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Employee Info -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                                <div class="text-2xl font-bold">{{ props.totalRegularHours > 0 ? `${props.totalRegularHours}h` : '—' }}</div>
                                <div class="text-muted-foreground text-xs">Regular</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ props.totalOvertimeHours > 0 ? `${props.totalOvertimeHours}h` : '—' }}</div>
                                <div class="text-muted-foreground text-xs">Overtime</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Attendance Records -->
            <Card>
                <CardHeader class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <CalendarCheck class="h-5 w-5" />
                        <CardTitle>Attendance</CardTitle>
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <Button size="sm" variant="outline" @click="goToPeriod(props.previousPeriod.start)">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="text-center text-sm font-medium">
                            {{ props.period.label }}
                        </span>
                        <Button size="sm" variant="outline" @click="goToPeriod(props.nextPeriod.start)">
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Time In</TableHead>
                                <TableHead>Time Out</TableHead>
                                <TableHead class="text-right">Regular</TableHead>
                                <TableHead class="text-right">OT</TableHead>
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
                                    <template v-else-if="record.time_in && !record.time_out">
                                        <!-- Edit form -->
                                        <div v-if="editingCheckinId === record.checkin_id" class="flex items-center gap-1">
                                            <Input
                                                v-model="manualTimeOutInput"
                                                type="time"
                                                step="1"
                                                class="h-7 w-28 text-xs"
                                            />
                                            <Button
                                                size="sm"
                                                class="h-7 px-2 text-xs"
                                                :disabled="!manualTimeOutInput"
                                                @click="submitManualTimeOut(record.checkin_id!, record.date)"
                                            >
                                                Save
                                            </Button>
                                            <Button size="sm" variant="ghost" class="h-7 px-2 text-xs" @click="cancelEdit">
                                                ✕
                                            </Button>
                                        </div>
                                        <!-- Pending/rejected with edit -->
                                        <div v-else-if="record.manual_time_out && record.manual_time_out_status" class="flex items-center gap-1">
                                            <span class="text-muted-foreground">{{ record.manual_time_out }}</span>
                                            <Badge v-if="record.manual_time_out_status === 'pending'" variant="outline" class="text-xs text-yellow-600">pending</Badge>
                                            <Badge v-else-if="record.manual_time_out_status === 'rejected'" variant="destructive" class="text-xs">rejected</Badge>
                                            <Button size="sm" variant="ghost" class="h-6 px-1.5 text-xs" @click="startEdit(record.checkin_id!)">
                                                edit
                                            </Button>
                                        </div>
                                        <!-- Set time-out button -->
                                        <Button
                                            v-else
                                            size="sm"
                                            variant="outline"
                                            class="h-7 px-2 text-xs"
                                            @click="startEdit(record.checkin_id!)"
                                        >
                                            <Clock class="mr-1 h-3 w-3" />
                                            Set time-out
                                        </Button>
                                    </template>
                                    <span v-else>—</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ record.regular_hours !== null ? `${record.regular_hours}h` : '—' }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <template v-if="record.overtime_hours">
                                        {{ record.overtime_hours }}h
                                        <Badge v-if="record.overtime_status === 'approved'" variant="outline" class="ml-1 text-xs text-green-600">approved</Badge>
                                        <Badge v-else-if="record.overtime_status === 'rejected'" variant="destructive" class="ml-1 text-xs">rejected</Badge>
                                        <Badge v-else variant="outline" class="ml-1 text-xs text-yellow-600">pending</Badge>
                                    </template>
                                    <span v-else>—</span>
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
