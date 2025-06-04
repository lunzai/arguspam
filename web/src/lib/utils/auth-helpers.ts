import type { User } from '$lib/types/user.js';


// // In your auth store - computed automatically
// $authStore.shouldSetupTwoFactor      // BLOCKING: Must setup 2FA first
// $authStore.shouldChallengeTwoFactor  // BLOCKING: Must enter OTP
// $authStore.shouldVerifyEmail         // WARNING: Should verify email

// // Helper utilities for complex logic
// import { shouldBlockAccess, getAuthActions } from '$lib/utils/auth-helpers';

// if (shouldBlockAccess(user)) {
//   // Redirect to appropriate auth step
// }

/**
 * Determine what actions are required after login
 */
export function getAuthActions(user: User) {
	const isEmailVerified = user.email_verified_at !== null;
	const isTwoFactorEnabled = user.two_factor_enabled;
	const isTwoFactorVerified = user.two_factor_confirmed_at !== null;

	return {
		// Computed states
		isEmailVerified,
		isTwoFactorEnabled,
		isTwoFactorVerified,
		
		// Action priorities (in order of importance)
		shouldChallengeTwoFactor: isTwoFactorEnabled && isTwoFactorVerified,  // BLOCKING: Must enter OTP
		shouldSetupTwoFactor: isTwoFactorEnabled && !isTwoFactorVerified,    // BLOCKING: Must setup 2FA
		shouldVerifyEmail: !isEmailVerified,                                 // WARNING: Should verify email
		
		// Helper for redirect logic
		getRedirectPath: () => {
			if (isTwoFactorEnabled && !isTwoFactorVerified) {
				return '/auth/setup-2fa';  // BLOCKS access until 2FA setup
			}
			if (isTwoFactorEnabled && isTwoFactorVerified) {
				return '/auth/verify-2fa'; // BLOCKS access until OTP entered
			}
			return '/dashboard'; // Normal flow (email verification is optional warning)
		}
	};
}

/**
 * Check if user access should be blocked
 */
export function shouldBlockAccess(user: User): boolean {
	const { shouldChallengeTwoFactor, shouldSetupTwoFactor } = getAuthActions(user);
	return shouldChallengeTwoFactor || shouldSetupTwoFactor;
}

/**
 * Get user-friendly status message
 */
export function getAuthStatusMessage(user: User): string {
	const actions = getAuthActions(user);
	
	if (actions.shouldSetupTwoFactor) {
		return "Please complete your two-factor authentication setup";
	}
	if (actions.shouldChallengeTwoFactor) {
		return "Please enter your two-factor authentication code";
	}
	if (actions.shouldVerifyEmail) {
		return "Consider verifying your email address for better security";
	}
	return "All security settings are configured";
} 