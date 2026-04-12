<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { UserPen } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    employee: {
        id: number;
        id_number: string;
        first_name: string;
        last_name: string;
        email: string | null;
        company: string | null;
        department: string | null;
        position: string | null;
        is_active: boolean;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Employees', href: '/employees' },
    { title: `${props.employee.first_name} ${props.employee.last_name}`, href: `/employees/${props.employee.id}` },
    { title: 'Edit', href: `/employees/${props.employee.id}/edit` },
];

const form = useForm({
    id_number: props.employee.id_number,
    first_name: props.employee.first_name,
    last_name: props.employee.last_name,
    email: props.employee.email ?? '',
    company: props.employee.company ?? '',
    department: props.employee.department ?? '',
    position: props.employee.position ?? '',
    is_active: props.employee.is_active,
});

function submit() {
    form.put(`/employees/${props.employee.id}`);
}
</script>

<template>
    <Head title="Edit Employee" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <UserPen class="h-5 w-5" />
                        <CardTitle>Edit Employee</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="id_number">ID Number *</Label>
                            <Input
                                id="id_number"
                                v-model="form.id_number"
                                placeholder="e.g. EMP-004"
                            />
                            <p v-if="form.errors.id_number" class="text-destructive text-sm">
                                {{ form.errors.id_number }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="first_name">First Name *</Label>
                                <Input
                                    id="first_name"
                                    v-model="form.first_name"
                                    placeholder="Juan"
                                />
                                <p v-if="form.errors.first_name" class="text-destructive text-sm">
                                    {{ form.errors.first_name }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="last_name">Last Name *</Label>
                                <Input
                                    id="last_name"
                                    v-model="form.last_name"
                                    placeholder="Dela Cruz"
                                />
                                <p v-if="form.errors.last_name" class="text-destructive text-sm">
                                    {{ form.errors.last_name }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="juan@example.com"
                                />
                                <p v-if="form.errors.email" class="text-destructive text-sm">
                                    {{ form.errors.email }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="company">Company</Label>
                                <Input
                                    id="company"
                                    v-model="form.company"
                                    placeholder="Acme Corp"
                                />
                                <p v-if="form.errors.company" class="text-destructive text-sm">
                                    {{ form.errors.company }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="department">Department</Label>
                                <Input
                                    id="department"
                                    v-model="form.department"
                                    placeholder="Engineering"
                                />
                                <p v-if="form.errors.department" class="text-destructive text-sm">
                                    {{ form.errors.department }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label for="position">Position</Label>
                                <Input
                                    id="position"
                                    v-model="form.position"
                                    placeholder="Senior Developer"
                                />
                                <p v-if="form.errors.position" class="text-destructive text-sm">
                                    {{ form.errors.position }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_active"
                                :model-value="form.is_active"
                                @update:model-value="form.is_active = !!$event"
                            />
                            <Label for="is_active" class="cursor-pointer">Active</Label>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Update Employee' }}
                            </Button>
                            <Button variant="outline" as-child>
                                <Link :href="`/employees/${props.employee.id}`">Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
