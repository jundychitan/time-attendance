<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ShieldPlus } from 'lucide-vue-next';
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
    { title: 'Admin Users', href: '/admin-users' },
    { title: 'Add Admin', href: '/admin-users/create' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company: '',
    is_super_admin: false,
});

function submit() {
    form.post('/admin-users');
}
</script>

<template>
    <Head title="Add Admin User" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <ShieldPlus class="h-5 w-5" />
                        <CardTitle>Add Admin User</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="form.name" placeholder="Admin Name" />
                            <p v-if="form.errors.name" class="text-destructive text-sm">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email">Email *</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="admin@example.com" />
                                <p v-if="form.errors.email" class="text-destructive text-sm">{{ form.errors.email }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="company">Company</Label>
                                <Input id="company" v-model="form.company" placeholder="Acme Corp" />
                                <p v-if="form.errors.company" class="text-destructive text-sm">{{ form.errors.company }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="password">Password *</Label>
                                <Input id="password" v-model="form.password" type="password" />
                                <p v-if="form.errors.password" class="text-destructive text-sm">{{ form.errors.password }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password *</Label>
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_super_admin"
                                :model-value="form.is_super_admin"
                                @update:model-value="form.is_super_admin = !!$event"
                            />
                            <Label for="is_super_admin" class="cursor-pointer">Super Admin (can manage other admins)</Label>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Admin' }}
                            </Button>
                            <Button variant="outline" as-child>
                                <Link href="/admin-users">Cancel</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
