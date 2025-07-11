<script lang="ts">
	import TwoFactorAuthenticationForm from '$components/user/two-factor-authentication-form.svelte';
	import ChangePasswordForm from '$components/user/change-password-form.svelte';
	import * as Card from '$ui/card';

	let { data } = $props();
</script>

<div class="space-y-6">
	<Card.Root class="w-full">
		<Card.Header class="">
			<Card.Title class="text-lg">Password</Card.Title>
			<Card.Description>Change your password.</Card.Description>
			<Card.Action>
			</Card.Action>
		</Card.Header>
		<Card.Content>
			<ChangePasswordForm data={data.changePasswordForm} />
		</Card.Content>
	</Card.Root>

	<Card.Root class="w-full">
		<Card.Header class="">
			<Card.Title class="text-lg">Two-Factor Authentication</Card.Title>
			<Card.Description>Enable or disable two-factor authentication.</Card.Description>
			<Card.Action>
			</Card.Action>
		</Card.Header>
		<Card.Content>
			<TwoFactorAuthenticationForm />
		</Card.Content>
	</Card.Root>
</div>
