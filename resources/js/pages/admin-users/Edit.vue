<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

type Props = {
    adminUser: {
        id: number;
        name: string;
        email: string;
        company: string | null;
        is_super_admin: boolean;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Admin Users', href: '/admin-users' },
    { title: props.adminUser.name, href: `/admin-users/${props.adminUser.id}/edit` },
];

const form = useForm({
    name: props.adminUser.name,
    email: props.adminUser.email,
    password: '',
    password_confirmation: '',
    company: props.adminUser.company ?? '',
    is_super_admin: props.adminUser.is_super_admin,
});

function submit() {
    form.put(`/admin-users/${props.adminUser.id}`);
}
</script>

<template>
    <Head title="Edit Admin User" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="h-5 w-5" />
                        <CardTitle>Edit Admin User</CardTitle>
                    </div>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Name *</Label>
                            <Input id="name" v-model="form.name" />
                            <p v-if="form.errors.name" class="text-destructive text-sm">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="email">Email *</Label>
                                <Input id="email" v-model="form.email" type="email" />
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
                                <Label for="password">New Password (leave blank to keep current)</Label>
                                <Input id="password" v-model="form.password" type="password" />
                                <p v-if="form.errors.password" class="text-destructive text-sm">{{ form.errors.password }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm New Password</Label>
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_super_admin"
                                :model-value="form.is_super_admin"
                                @update:model-value="form.is_super_admin = !!$event"
                            />
                            <Label for="is_super_admin" class="cursor-pointer">Super Admin</Label>
                        </div>

                        <div class="flex items-center gap-3">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Update Admin' }}
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
