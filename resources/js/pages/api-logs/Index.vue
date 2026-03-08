<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Bug, RefreshCw, CheckCircle, XCircle } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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

type ApiLog = {
    id: number;
    method: string;
    url: string;
    ip_address: string | null;
    bearer_token: string | null;
    authenticated: boolean;
    payload: Record<string, unknown> | null;
    headers: Record<string, string | null> | null;
    response_status: number | null;
    response_body: string | null;
    created_at: string;
};

type Props = {
    logs: {
        data: ApiLog[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'API Debug Logs', href: '/api-logs' },
];

const autoRefresh = ref(true);
let refreshInterval: ReturnType<typeof setInterval>;

function refresh() {
    router.reload({ only: ['logs'], preserveState: true });
}

onMounted(() => {
    refreshInterval = setInterval(() => {
        if (autoRefresh.value) {
            refresh();
        }
    }, 5000);
});

onUnmounted(() => {
    clearInterval(refreshInterval);
});

const expandedId = ref<number | null>(null);

function toggle(id: number) {
    expandedId.value = expandedId.value === id ? null : id;
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleString();
}

function statusColor(status: number | null): string {
    if (!status) return 'secondary';
    if (status >= 200 && status < 300) return 'default';
    if (status === 401) return 'destructive';
    if (status >= 400) return 'destructive';
    return 'secondary';
}
</script>

<template>
    <Head title="API Debug Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Bug class="h-5 w-5" />
                        <CardTitle>API Debug Logs</CardTitle>
                        <Badge variant="secondary">
                            Auto-refresh: {{ autoRefresh ? 'ON' : 'OFF' }}
                        </Badge>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            size="sm"
                            variant="outline"
                            @click="autoRefresh = !autoRefresh"
                        >
                            {{ autoRefresh ? 'Pause' : 'Resume' }}
                        </Button>
                        <Button size="sm" variant="outline" @click="refresh">
                            <RefreshCw class="mr-1 h-4 w-4" />
                            Refresh
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[50px]">Auth</TableHead>
                                <TableHead class="w-[60px]">Status</TableHead>
                                <TableHead class="w-[80px]">Method</TableHead>
                                <TableHead>URL</TableHead>
                                <TableHead>Bearer Token</TableHead>
                                <TableHead>IP</TableHead>
                                <TableHead>Time</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="props.logs.data.length === 0">
                                <TableRow>
                                    <TableCell :colspan="7" class="text-muted-foreground text-center">
                                        No API requests logged yet. Waiting for incoming requests...
                                    </TableCell>
                                </TableRow>
                            </template>
                            <template
                                v-for="log in props.logs.data"
                                :key="log.id"
                            >
                                <TableRow
                                    class="cursor-pointer hover:bg-muted/50"
                                    @click="toggle(log.id)"
                                >
                                    <TableCell>
                                        <CheckCircle
                                            v-if="log.authenticated"
                                            class="h-5 w-5 text-green-500"
                                        />
                                        <XCircle
                                            v-else
                                            class="h-5 w-5 text-red-500"
                                        />
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="statusColor(log.response_status) as any">
                                            {{ log.response_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">
                                            {{ log.method }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="max-w-[300px] truncate text-sm">
                                        {{ log.url }}
                                    </TableCell>
                                    <TableCell class="font-mono text-xs">
                                        {{ log.bearer_token ?? '(none)' }}
                                    </TableCell>
                                    <TableCell class="text-sm">
                                        {{ log.ip_address }}
                                    </TableCell>
                                    <TableCell class="text-muted-foreground text-sm">
                                        {{ formatDate(log.created_at) }}
                                    </TableCell>
                                </TableRow>
                                <!-- Expanded detail row -->
                                <TableRow v-if="expandedId === log.id">
                                    <TableCell :colspan="7" class="bg-muted/30 p-4">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <div>
                                                <h4 class="mb-2 font-semibold">Headers</h4>
                                                <pre class="bg-background overflow-auto rounded p-3 text-xs">{{ JSON.stringify(log.headers, null, 2) }}</pre>
                                            </div>
                                            <div>
                                                <h4 class="mb-2 font-semibold">Payload</h4>
                                                <pre class="bg-background overflow-auto rounded p-3 text-xs">{{ JSON.stringify(log.payload, null, 2) }}</pre>
                                            </div>
                                            <div class="md:col-span-2">
                                                <h4 class="mb-2 font-semibold">
                                                    Response
                                                    <Badge :variant="statusColor(log.response_status) as any" class="ml-2">
                                                        {{ log.response_status }}
                                                    </Badge>
                                                </h4>
                                                <pre class="bg-background max-h-48 overflow-auto rounded p-3 text-xs">{{ log.response_body ? JSON.stringify(JSON.parse(log.response_body), null, 2) : '(empty)' }}</pre>
                                            </div>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div
                        v-if="props.logs.last_page > 1"
                        class="mt-4 flex justify-center gap-1"
                    >
                        <template
                            v-for="link in props.logs.links"
                            :key="link.label"
                        >
                            <Button
                                v-if="link.url"
                                :variant="link.active ? 'default' : 'outline'"
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
