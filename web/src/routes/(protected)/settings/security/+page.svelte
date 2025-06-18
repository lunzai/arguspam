<script lang="ts">
	import TwoFactorAuthenticationForm from '$components/user/two-factor-authentication-form.svelte';
	import ChangePasswordForm from '$components/user/change-password-form.svelte';

	let { data } = $props();
</script>

<div class="space-y-6">
	<div>
		<h1 class="text-2xl font-medium">Password</h1>
		<p class="text-muted-foreground">Change your password.</p>
	</div>

	<ChangePasswordForm data={data.changePasswordForm} />

	<div class="mt-12">
		<h1 class="text-2xl font-medium">Two-Factor Authentication</h1>
		<p class="text-muted-foreground">Enable or disable two-factor authentication.</p>
	</div>

	<TwoFactorAuthenticationForm />
</div>
