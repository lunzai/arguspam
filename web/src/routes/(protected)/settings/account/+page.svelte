<script lang="ts">
	import { UserProfileSchema } from '$lib/validations/user';
	import { Input } from '$ui/input';
	import * as Form from '$ui/form';
	import { toast } from 'svelte-sonner';
	import { Loader2 } from '@lucide/svelte';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import type { SuperValidated } from 'sveltekit-superforms';
	import * as Card from '$ui/card';
	import { TIMEZONES } from '$lib/constants/timezones';
	import * as Select from '$ui/select';
	import { layoutStore } from '$lib/stores/layout';
	import type { User, Me } from '$models/user';
	import type { UserProfile } from '$lib/validations/user';
	let { data } = $props<{
		data: { form: SuperValidated<UserProfile>; me: Me; title: string };
	}>();
	console.log(data);
	const form = superForm<UserProfile>(data.form, {
		validators: zod4Client(UserProfileSchema),
		delayMs: 100,
		resetForm: false,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				await layoutStore.setUser(result.data.user as User);
				toast.success(result.data.message);
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting, delayed } = form;
</script>

<form method="POST" use:enhance>
	<Card.Root class="w-full">
		<Card.Header class="">
			<Card.Title class="text-lg">Profile Settings</Card.Title>
			<Card.Description>Update your personal information.</Card.Description>
			<Card.Action></Card.Action>
		</Card.Header>
		<Card.Content>
			<div class="max-w-xl space-y-6">
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
					<Input type="email" value={data.me.email} readonly disabled />
					<p class="text-muted-foreground text-sm">
						Email address cannot be changed. Contact your administrator to update your email.
					</p>
				</div>

				<Form.Field {form} name="default_timezone">
					<Form.Control>
						<Form.Label>Default Timezone</Form.Label>
						<Select.Root
							name="default_timezone"
							type="single"
							bind:value={$formData.default_timezone}
							disabled={$submitting}
						>
							<Select.Trigger class="w-full">
								{$formData.default_timezone ? $formData.default_timezone : 'Select timezone'}
							</Select.Trigger>
							<Select.Content>
								{#each TIMEZONES as timezone}
									<Select.Item value={timezone}>{timezone}</Select.Item>
								{/each}
							</Select.Content>
						</Select.Root>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>

				<Form.Button type="submit" disabled={$submitting}>
					{#if $delayed}
						<Loader2 className="size-4 animate-spin" />
					{/if}
					Update Profile
				</Form.Button>
			</div>
		</Card.Content>
	</Card.Root>
</form>
