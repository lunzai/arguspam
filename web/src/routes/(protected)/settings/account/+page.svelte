<script lang="ts">
	import { userProfileSchema } from '$lib/validations/user';
    import { Input } from '$ui/input';
    import * as Form from '$ui/form';
    import { toast } from 'svelte-sonner';
    import { UserService } from '$services/user';
    import { authStore } from '$lib/stores/auth';
    import { Loader2 } from '@lucide/svelte';
	
    import {
        superForm
    } from 'sveltekit-superforms';
    import { zodClient } from 'sveltekit-superforms/adapters';

    let { data } = $props();
    let isLoading = $state(false);

    const form = superForm(data.form, {
        validators: zodClient(userProfileSchema),
        onSubmit: async ({ formData, cancel }) => {
            cancel(); // Prevent default form submission
            isLoading = true;
            const formValues = Object.fromEntries(formData);
            
            try {
                // Call backend API directly using client service (only name can be updated)
                await userService.update(data.user.id, {
                    name: formValues.name as string
                });
                const user = await userService.me();
                authStore.setUser(user.data.attributes);
                toast.success('Profile updated successfully!');
            } catch (error) {
                console.error('Failed to update profile:', error);
                toast.error('Failed to update profile. Please try again.');
            } finally {
                isLoading = false;
            }
        }
    });

    const { form: formData, enhance } = form;
</script>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Profile Settings</h1>
        <p class="text-muted-foreground">Update your personal information.</p>
    </div>

    <form method="POST" use:enhance class="space-y-6 max-w-xl">
        <Form.Field {form} name="name">
            <Form.Control>
                <Form.Label>Full Name</Form.Label>
                <Input type="text" name="name" bind:value={$formData.name} disabled={isLoading} />
            </Form.Control>
            <Form.FieldErrors />
        </Form.Field>
        <Form.Field {form} name="email">
            <Form.Control>
                <Form.Label>Email</Form.Label>
                <Input type="email" name="email" value={data.user.email} readonly disabled />
                <Form.Description>Email address cannot be changed. Contact your administrator to update your email.</Form.Description>
            </Form.Control>
            <Form.FieldErrors />
        </Form.Field>
        <Form.Button type="submit" disabled={isLoading}>
            {#if isLoading}
                <Loader2 class="animate-spin" /> Updating...
            {:else}
                Update Profile
            {/if}
        </Form.Button>
    </form>

    <!-- <form method="POST" use:enhance class="space-y-4 max-w-xl">
        <div class="space-y-2">
            <label for="name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Full Name
            </label>
            <Input 
                id="name" 
                name="name" 
                type="text" 
                bind:value={$formData.name} 
                aria-invalid={$errors.name ? 'true' : undefined}
                {...$constraints.name}
            />
            {#if $errors.name}
                <p class="text-sm text-destructive">{$errors.name}</p>
            {/if}
        </div>

        <div class="space-y-2">
            <label for="email" class="text-sm font-medium leading-none">
                Email Address
            </label>
            <Input 
                id="email" 
                name="email" 
                type="email" 
                value={data.user.email}
                readonly
                disabled
                class="bg-muted cursor-not-allowed"
            />
            <p class="text-xs text-muted-foreground">
                Email address cannot be changed. Contact your administrator to update your email.
            </p>
        </div> -->

        <!-- <Button type="submit" class="mt-4" disabled={isLoading}>
            {#if isLoading}
                <Loader2 class="animate-spin" /> Updating...
            {:else}
                Update Profile
            {/if}
        </Button> -->
    <!-- </form> -->
</div>