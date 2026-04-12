<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
</script>

<template>
    <AuthBase
        title="Registration disabled"
        description="Contact your administrator to create an account."
    >
        <Head title="Register" />

        <div class="text-center text-sm text-muted-foreground">
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
            >Back to login</TextLink>
        </div>
    </AuthBase>
</template>
