<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarCheck, Check, ChevronLeft, ChevronRight, Clock, X } from 'lucide-vue-next';
import { ref } from 'vue';
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

type DailyRecord = {
    date: string;
    time_in: string | null;
    time_out: string | null;
    time_out_next_day: boolean;
    total_hours: number | null;
    checkin_id: number | null;
    manual_time_out: string | null;
    manual_time_out_status: string | null;
};

type EmployeeAttendance = {
    employee: {
        id: number;
        id_number: string;
        full_name: string;
        department: string | null;
    };
    records: DailyRecord[];
    total_hours: number;
    days_present: number;
};

type Period = {
    start: string;
    end: string;
    label: string;
};

type Props = {
    attendance: EmployeeAttendance[];
    period: Period;
    previousPeriod: Period;
    nextPeriod: Period;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Attendance', href: '/attendance' },
];

function goToPeriod(periodStart: string) {
    router.get(
        '/attendance',
        { period_start: periodStart },
        { preserveState: true },
    );
}

const editingCheckinId = ref<number | null>(null);
const manualTimeOutInput = ref('');

function startEditManualTimeOut(checkinId: number) {
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

function approveTimeOut(checkinId: number) {
    router.patch(`/attendance/checkins/${checkinId}/approve`, {}, { preserveState: true });
}

function rejectTimeOut(checkinId: number) {
    router.patch(`/attendance/checkins/${checkinId}/reject`, {}, { preserveState: true });
}

const expandedEmployeeId = ref<number | null>(null);

function toggleEmployee(id: number) {
    expandedEmployeeId.value = expandedEmployeeId.value === id ? null : id;
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
                        <CardTitle>Attendance</CardTitle>
                    </div>
                    <!-- Period Navigator -->
                    <div class="flex items-center gap-2">
                        <Button
                            size="sm"
                            variant="outline"
                            @click="goToPeriod(props.previousPeriod.start)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <span class="min-w-[220px] text-center text-sm font-medium">
                            {{ props.period.label }}
                        </span>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="goToPeriod(props.nextPeriod.start)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID Number</TableHead>
                                <TableHead>Employee</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead class="text-right">Days Present</TableHead>
                                <TableHead class="text-right">Total Hours</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template
                                v-for="item in props.attendance"
                                :key="item.employee.id"
                            >
                                <TableRow
                                    class="cursor-pointer hover:bg-muted/50"
                                    @click="toggleEmployee(item.employee.id)"
                                >
                                    <TableCell>
                                        {{ item.employee.id_number }}
                                    </TableCell>
                                    <TableCell class="font-medium">
                                        {{ item.employee.full_name }}
                                    </TableCell>
                                    <TableCell>
                                        {{ item.employee.department ?? '—' }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ item.days_present }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ item.total_hours > 0 ? `${item.total_hours}h` : '—' }}
                                    </TableCell>
                                </TableRow>
                                <!-- Expanded daily records -->
                                <TableRow v-if="expandedEmployeeId === item.employee.id">
                                    <TableCell :colspan="5" class="bg-muted/30 p-0">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead class="pl-8">Date</TableHead>
                                                    <TableHead>Time In</TableHead>
                                                    <TableHead>Time Out</TableHead>
                                                    <TableHead class="text-right">Hours</TableHead>
                                                    <TableHead>Status</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                <TableRow
                                                    v-for="record in item.records"
                                                    :key="record.date"
                                                >
                                                    <TableCell class="pl-8 font-medium">
                                                        {{ record.date }}
                                                    </TableCell>
                                                    <TableCell>
                                                        {{ record.time_in ?? '—' }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <template v-if="record.time_out">
                                                            {{ record.time_out }}
                                                            <Badge v-if="record.time_out_next_day" variant="outline" class="ml-1 text-xs">
                                                                +1d
                                                            </Badge>
                                                            <Badge v-if="record.manual_time_out_status === 'approved'" variant="outline" class="ml-1 text-xs">
                                                                manual
                                                            </Badge>
                                                        </template>
                                                        <template v-else-if="record.time_in && !record.time_out">
                                                            <!-- Show pending/rejected manual time-out value -->
                                                            <div v-if="record.manual_time_out && record.manual_time_out_status" class="flex items-center gap-1">
                                                                <span class="text-muted-foreground">{{ record.manual_time_out }}</span>
                                                                <Badge v-if="record.manual_time_out_status === 'pending'" variant="outline" class="text-xs text-yellow-600">
                                                                    pending
                                                                </Badge>
                                                                <Badge v-else-if="record.manual_time_out_status === 'rejected'" variant="destructive" class="text-xs">
                                                                    rejected
                                                                </Badge>
                                                                <Button
                                                                    size="sm"
                                                                    variant="ghost"
                                                                    class="h-6 px-1.5 text-xs"
                                                                    @click.stop="startEditManualTimeOut(record.checkin_id!)"
                                                                >
                                                                    edit
                                                                </Button>
                                                            </div>
                                                            <!-- Edit or set new manual time-out -->
                                                            <div v-else-if="editingCheckinId === record.checkin_id" class="flex items-center gap-1">
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
                                                                <Button
                                                                    size="sm"
                                                                    variant="ghost"
                                                                    class="h-7 px-2 text-xs"
                                                                    @click="cancelEdit"
                                                                >
                                                                    ✕
                                                                </Button>
                                                            </div>
                                                            <Button
                                                                v-else
                                                                size="sm"
                                                                variant="outline"
                                                                class="h-7 px-2 text-xs"
                                                                @click.stop="startEditManualTimeOut(record.checkin_id!)"
                                                            >
                                                                <Clock class="mr-1 h-3 w-3" />
                                                                Set time-out
                                                            </Button>
                                                        </template>
                                                        <span v-else>—</span>
                                                    </TableCell>
                                                    <TableCell class="text-right">
                                                        {{
                                                            record.total_hours !== null
                                                                ? `${record.total_hours}h`
                                                                : '—'
                                                        }}
                                                    </TableCell>
                                                    <TableCell>
                                                        <div class="flex items-center gap-1">
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
                                                            <!-- Pending approval -->
                                                            <template v-if="record.manual_time_out_status === 'pending'">
                                                                <Badge variant="outline" class="text-xs text-yellow-600">
                                                                    pending
                                                                </Badge>
                                                                <Button
                                                                    size="sm"
                                                                    variant="outline"
                                                                    class="h-6 px-1.5"
                                                                    @click.stop="approveTimeOut(record.checkin_id!)"
                                                                >
                                                                    <Check class="h-3 w-3 text-green-600" />
                                                                </Button>
                                                                <Button
                                                                    size="sm"
                                                                    variant="outline"
                                                                    class="h-6 px-1.5"
                                                                    @click.stop="rejectTimeOut(record.checkin_id!)"
                                                                >
                                                                    <X class="h-3 w-3 text-red-600" />
                                                                </Button>
                                                            </template>
                                                            <Badge v-else-if="record.manual_time_out_status === 'rejected'" variant="destructive" class="text-xs">
                                                                rejected
                                                            </Badge>
                                                        </div>
                                                    </TableCell>
                                                </TableRow>
                                            </TableBody>
                                        </Table>
                                    </TableCell>
                                </TableRow>
                            </template>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
