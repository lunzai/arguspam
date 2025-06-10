<script lang="ts">
    import { Input } from '$ui/input';
    import * as Form from '$ui/form';
    import { toast } from 'svelte-sonner';
    import { Loader2 } from '@lucide/svelte';
    import { changePasswordSchema } from '$lib/validations/user';
    import { superForm } from 'sveltekit-superforms';
    import { zodClient } from 'sveltekit-superforms/adapters';
    import { authService } from '$lib/services/client/auth';
    import type { ChangePasswordRequest } from '$types/auth';

    let { data } = $props();
    let isLoading = $state(false);

    const form = superForm(data.changePasswordForm, {
        validators: zodClient(changePasswordSchema),
        onSubmit: async ({ formData, cancel }) => {
            cancel(); // Prevent default form submission
            
            // Check if form has validation errors before proceeding
            isLoading = true;
            const formValues = Object.fromEntries(formData) as unknown as ChangePasswordRequest;
            
            try {
                await authService.changePassword(formValues);
                toast.success('Password updated successfully!');
                reset();
            } catch (error) {
                console.error('Failed to update password:', error);
                toast.error('Failed to update password. Please try again.');
            } finally {
                isLoading = false;
            }
        }
    });

    const { form: formData, enhance, reset, allErrors } = form;

</script>

<form method="POST" use:enhance class="space-y-6 max-w-xl">
    <Form.Field form={form} name="currentPassword">
        <Form.Control>
            <Form.Label>Current Password</Form.Label>
            <Input type="password" name="currentPassword" bind:value={$formData.currentPassword} disabled={isLoading} />
        </Form.Control>
        <Form.FieldErrors />
    </Form.Field>
    <Form.Field form={form} name="newPassword">
        <Form.Control>
            <Form.Label>New Password</Form.Label>
            <Input type="password" name="newPassword" bind:value={$formData.newPassword} disabled={isLoading} />
        </Form.Control>
        <Form.FieldErrors />
    </Form.Field>
    <Form.Field form={form} name="confirmNewPassword">
        <Form.Control>
            <Form.Label>Confirm New Password</Form.Label>
            <Input type="password" name="confirmNewPassword" bind:value={$formData.confirmNewPassword} disabled={isLoading} />
        </Form.Control>
        <Form.FieldErrors />
    </Form.Field>
    <Form.Button type="submit" disabled={isLoading}>
        {#if isLoading}
            <Loader2 class="animate-spin" /> Updating...
        {:else}
            Change Password
        {/if}
    </Form.Button>
</form>