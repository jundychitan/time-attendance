<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Employees', href: '/employees' },
    { title: 'Add Employee', href: '/employees/create' },
];

const form = useForm({
    id_number: '',
    first_name: '',
    last_name: '',
    department: '',
    position: '',
    is_active: true,
});

function submit() {
    form.post('/employees');
}
</script>

<template>
    <Head title="Add Employee" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <UserPlus class="h-5 w-5" />
                        <CardTitle>Add Employee</CardTitle>
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
                                {{ form.processing ? 'Saving...' : 'Save Employee' }}
                            </Button>
                            <Button variant="outline" as-child>
                                <Link href="/employees">Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
