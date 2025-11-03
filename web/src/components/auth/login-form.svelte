<script lang="ts">
	import * as Form from '$ui/form';
	import { Input } from '$ui/input';
	import { Button } from '$ui/button';
	import { LoginSchema, type Login } from '$validations/auth';
	import { type SuperValidated, superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';

	let {
		data
	}: {
		data: { form: SuperValidated<Login> };
	} = $props();

	const form = superForm(data.form, {
		validators: zod4Client(LoginSchema)
	});

	const { form: formData, enhance, message } = form;
	if ($message) {
		toast.success($message);
	}

	if ($formData.email === '') {
		$formData.email = 'heanluen@gmail.com';
	}
	if ($formData.password === '') {
		$formData.password = 'password';
	}
</script>

<div class="space-y-6">
	<div class="flex flex-col items-center gap-6">
		<div class="flex size-24 items-center justify-center rounded-md">
			<img src="/logo.png" alt="ArgusPAM" />
		</div>
		<span class="sr-only">ArgusPAM</span>
		<h1 class="text-xl font-bold">Welcome to ArgusPAM</h1>
	</div>
	<form method="POST" use:enhance class="max-w-xl space-y-6">
		<Form.Field {form} name="email">
			<Form.Control>
				<Form.Label>Email</Form.Label>
				<Input bind:value={$formData.email} />
			</Form.Control>
			<Form.FieldErrors />
		</Form.Field>
		<Form.Field {form} name="password">
			<Form.Control>
				<Form.Label>Password</Form.Label>
				<Input type="password" bind:value={$formData.password} />
			</Form.Control>
			<Form.FieldErrors />
		</Form.Field>
		<Button type="submit">Login</Button>
	</form>
</div>
