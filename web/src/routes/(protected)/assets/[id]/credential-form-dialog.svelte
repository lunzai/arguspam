<script lang="ts">
	import { Input } from '$ui/input';
	import * as Select from '$ui/select';
	import { Textarea } from '$ui/textarea';
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zodClient } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { capitalizeWords } from '$utils/string';
	import { Button } from '$ui/button';
	import type { Asset } from '$models/asset';
	import Loader from '$components/loader.svelte';
	import { AssetCredentialsSchema } from '$lib/validations/asset';

	interface Props {
		isOpen: boolean;
		model: Asset;
		data: any;
		onSuccess: (data: Asset) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		model = $bindable(),
		data = $bindable(),
		onSuccess = async (data: Asset) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zodClient(AssetCredentialsSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as Asset);
				isOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting } = form;

	function handleCancel() {
		isOpen = false;
	}
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content class="sm:max-w-2xl max-h-[90vh] overflow-y-auto" interactOutsideBehavior="ignore">
		{#if $submitting}
			<Loader show={$submitting} />
		{/if}
		<form class="space-y-6" method="POST" action="?/updateCredentials" use:enhance>
			<Dialog.Header>
				<Dialog.Title>Edit Credentials</Dialog.Title>
				<Dialog.Description>Edit asset credentials.</Dialog.Description>
			</Dialog.Header>
			<div class="space-y-6">
				<div class="grid grid-cols-2 gap-6">
					<Form.Field {form} name="host">
						<Form.Control>
							<Form.Label>Host</Form.Label>
							<Input type="text" name="host" bind:value={$formData.host} disabled={$submitting} />
						</Form.Control>
						<Form.FieldErrors />
					</Form.Field>
					<Form.Field {form} name="port">
						<Form.Control>
							<Form.Label>Port</Form.Label>
							<Input
								type="number"
								min={0}
								max={65535}
								step={1}
								name="port"
								bind:value={$formData.port}
								disabled={$submitting}
							/>
						</Form.Control>
						<Form.FieldErrors />
					</Form.Field>
				</div>
				<div class="grid grid-cols-2 gap-6">
					<Form.Field {form} name="username">
						<Form.Control>
							<Form.Label>Admin User</Form.Label>
							<Input
								type="text"
								name="username"
								placeholder="Leave blank to keep current"
								bind:value={$formData.username}
								disabled={$submitting}
								data-1p-ignore
							/>
						</Form.Control>
						<Form.FieldErrors />
					</Form.Field>
					<Form.Field {form} name="password">
						<Form.Control>
							<Form.Label>Admin Password</Form.Label>
							<Input
								type="password"
								name="password"
								placeholder="Leave blank to keep current"
								bind:value={$formData.password}
								disabled={$submitting}
								data-1p-ignore
							/>
						</Form.Control>
						<Form.FieldErrors />
					</Form.Field>
				</div>
				<div class="grid grid-cols-2 gap-6">
					<Form.Field {form} name="dbms">
						<Form.Control>
							<Form.Label>DBMS</Form.Label>
							<Select.Root
								name="dbms"
								type="single"
								bind:value={$formData.dbms}
								disabled={$submitting}
							>
								<Select.Trigger class="w-full">
									{$formData.dbms ? capitalizeWords($formData.dbms) : 'Select DBMS'}
								</Select.Trigger>
								<Select.Content>
									<Select.Item value="mysql" label="MySQL" />
									<Select.Item value="postgresql" label="PostgreSQL" />
									<Select.Item value="sqlserver" label="SQL Server" />
									<Select.Item value="oracle" label="Oracle" />
									<Select.Item value="mongodb" label="MongoDB" />
									<Select.Item value="redis" label="Redis" />
									<Select.Item value="mariadb" label="MariaDB" />
								</Select.Content>
							</Select.Root>
						</Form.Control>
						<Form.FieldErrors />
					</Form.Field>
				</div>
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Update</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
