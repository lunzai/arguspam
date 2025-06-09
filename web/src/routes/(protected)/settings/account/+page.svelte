<script lang="ts">
	import { userProfileSchema } from '$lib/validations/user';
    import { Input } from '$ui/input';
    import * as Form from '$ui/form';
    import { Button } from '$ui/button';
    import { toast } from 'svelte-sonner';
    import { userService } from '$lib/services/client/users';
    import { authStore } from '$lib/stores/auth';
	
    import {
        type SuperValidated,
        type Infer,
        superForm
    } from 'sveltekit-superforms';
    import { zodClient } from 'sveltekit-superforms/adapters';

    let { data } = $props();

    const form = superForm(data.form, {
        validators: zodClient(userProfileSchema),
        onSubmit: async ({ formData, cancel }) => {
            cancel(); // Prevent default form submission
            
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
            }
        }
    });

    const { form: formData, enhance, errors, constraints } = form;
</script>

<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Profile Settings</h1>
        <p class="text-muted-foreground">Update your personal information.</p>
    </div>

    <form method="POST" use:enhance class="space-y-4 max-w-md">
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
        </div>

        <Button type="submit" class="w-full">Update Profile</Button>
    </form>
</div>