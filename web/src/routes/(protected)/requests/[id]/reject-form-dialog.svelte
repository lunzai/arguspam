<script lang="ts">
	import * as Form from '$ui/form';
	import * as Dialog from '$ui/dialog';
	import { superForm } from 'sveltekit-superforms';
	import { zod4Client } from 'sveltekit-superforms/adapters';
	import { toast } from 'svelte-sonner';
	import { Button } from '$ui/button';
	import type { Request } from '$models/request';
	import Loader from '$components/loader.svelte';
	import { RejectSchema } from '$lib/validations/request';
	import { Textarea } from '$ui/textarea';

	interface Props {
		isOpen: boolean;
		data: any;
		onSuccess: (data: Request) => Promise<void>;
	}

	let {
		isOpen = $bindable(false),
		data = $bindable(),
		onSuccess = async (data: Request) => {}
	}: Props = $props();

	const form = superForm(data, {
		validators: zod4Client(RejectSchema),
		delayMs: 100,
		async onUpdate({ form, result }) {
			if (!form.valid && Object.keys(form.errors).length > 0) {
				return;
			}
			if (result.type === 'success') {
				toast.success(result.data.message);
				await onSuccess(result.data.model as Request);
				isOpen = false;
			} else if (result.type === 'failure') {
				toast.error(result.data.error);
			}
		}
	});

	const { form: formData, enhance, submitting, reset } = form;

	function handleCancel() {
		isOpen = false;
		reset();
	}
</script>

<Dialog.Root bind:open={isOpen}>
	<Dialog.Content
		class="max-h-[90vh] overflow-y-auto sm:max-w-2xl"
		interactOutsideBehavior="ignore"
	>
		{#if $submitting}
			<Loader show={$submitting} />
		{/if}
		<form class="min-w-0 space-y-6" method="POST" action="?/reject" use:enhance>
			<Dialog.Header>
				<Dialog.Title>Reject Request</Dialog.Title>
			</Dialog.Header>
			<div class="space-y-6">
				<Form.Field {form} name="approver_note">
					<Form.Control>
						<Form.Label>Approver Note</Form.Label>
						<Textarea
							name="approver_note"
							bind:value={$formData.approver_note}
							disabled={$submitting}
							class="min-h-18"
						/>
					</Form.Control>
					<Form.FieldErrors />
				</Form.Field>
			</div>
			<Dialog.Footer>
				<Button variant="outline" onclick={handleCancel}>Cancel</Button>
				<Button variant="default" type="submit">Reject</Button>
			</Dialog.Footer>
		</form>
	</Dialog.Content>
</Dialog.Root>
