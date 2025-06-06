/**
 * Generate a user avatar using DiceBear API
 * @param seed - The seed for generating the avatar (usually user's name or email)
 * @returns URL for the generated avatar
 */
export function generateAvatar(seed: string): string {
	const DICEBEAR_API = 'https://api.dicebear.com/9.x/bottts/svg';
	const encodedSeed = encodeURIComponent(seed);
	return `${DICEBEAR_API}?seed=${encodedSeed}`;
}

/**
 * Generate an organization logo using UI Avatars API
 * @param name - The organization name
 * @param size - Optional size parameter (default: 128)
 * @returns URL for the generated logo
 */
export function generateInitials(name: string, size: number = 128): string {
	const UI_AVATARS_API = 'https://ui-avatars.com/api';
	const encodedName = encodeURIComponent(name);
	return `${UI_AVATARS_API}/?size=${size}&name=${encodedName}`;
}

/**
 * Extract 2-letter initials from a string
 * @param name - The input string to generate initials from
 * @returns Two uppercase letters representing the initials
 */
export function getInitials(name: string): string {
	const words = name.split(/\s+/).filter((word) => word.length > 0);
	if (words.length === 0) {
		return 'NA';
	}
	if (words.length === 1) {
		return words[0].substring(0, 2).toUpperCase();
	}
	return (words[0][0] + words[words.length - 1][0]).toUpperCase();
}
