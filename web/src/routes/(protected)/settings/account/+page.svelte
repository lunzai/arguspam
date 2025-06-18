<script lang="ts">
	import { userProfileSchema } from '$lib/validations/user';
	import { Input } from '$ui/input';
	import * as Form from '$ui/form';
	import { toast } from 'svelte-sonner';
	import { authStore } from '$lib/stores/auth';
	import { Loader2 } from '@lucide/svelte';
	import type { User } from '$models/user';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';

	let { data } = $props();

	const form = superForm(data.form, {
		validators: zodClient(userProfileSchema),
		delayMs: 100,
		resetForm: false,
		onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				authStore.setUser(result.data.user as User);
				toast.success(result.data.message);
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting, delayed } = form;
</script>

<div class="space-y-6">
	<div>
		<h1 class="text-2xl font-medium">Profile Settings</h1>
		<p class="text-muted-foreground">Update your personal information.</p>
	</div>

	<form method="POST" use:enhance class="max-w-xl space-y-6">
		<Form.Field {form} name="name">
			<Form.Control>
				<Form.Label>Full Name</Form.Label>
				<Input type="text" name="name" bind:value={$formData.name} disabled={$submitting} />
			</Form.Control>
			<Form.FieldErrors />
		</Form.Field>

		<div class="space-y-2">
			<label for="email" class="flex gap-2 text-sm leading-none font-medium select-none"
				>Email</label
			>
			<Input type="email" value={data.user.email} readonly disabled />
			<p class="text-muted-foreground text-sm">
				Email address cannot be changed. Contact your administrator to update your email.
			</p>
		</div>

		<Form.Button type="submit" disabled={$submitting}>
			{#if $delayed}
				<Loader2 className="size-4 animate-spin" />
			{/if}
			Update Profile
		</Form.Button>
	</form>
</div>
