<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarCheck, Clock } from 'lucide-vue-next';
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
    time_out_next_day: boolean;
    total_hours: number | null;
    checkin_id: number | null;
    manual_time_out: string | null;
    selfie_in_url: string | null;
    selfie_out_url: string | null;
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
                                    <template v-if="record.time_in">
                                        <a
                                            v-if="record.selfie_in_url"
                                            :href="record.selfie_in_url"
                                            target="_blank"
                                            class="text-primary hover:underline"
                                        >
                                            {{ record.time_in }}
                                        </a>
                                        <span v-else>{{ record.time_in }}</span>
                                    </template>
                                    <span v-else>—</span>
                                </TableCell>
                                <TableCell>
                                    <template v-if="record.time_out">
                                        <a
                                            v-if="record.selfie_out_url"
                                            :href="record.selfie_out_url"
                                            target="_blank"
                                            class="text-primary hover:underline"
                                        >
                                            {{ record.time_out }}
                                        </a>
                                        <span v-else>{{ record.time_out }}</span>
                                        <Badge v-if="record.time_out_next_day" variant="outline" class="ml-1 text-xs">
                                            +1d
                                        </Badge>
                                        <Badge v-if="record.manual_time_out" variant="outline" class="ml-1 text-xs">
                                            manual
                                        </Badge>
                                    </template>
                                    <template v-else-if="record.time_in && !record.time_out">
                                        <!-- Missing time-out: show manual entry -->
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
                                            @click="startEditManualTimeOut(record.checkin_id!)"
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
