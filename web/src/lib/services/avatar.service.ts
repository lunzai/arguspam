/**
 * Service for handling avatar and organization logo generation
 */
export class AvatarService {
	private static instance: AvatarService;
	//private readonly DICEBEAR_API = 'https://api.dicebear.com/9.x/pixel-art-neutral/svg';
	private readonly DICEBEAR_API = 'https://api.dicebear.com/9.x/bottts/svg';
	private readonly UI_AVATARS_API = 'https://ui-avatars.com/api';

	private constructor() {}

	public static getInstance(): AvatarService {
		if (!AvatarService.instance) {
			AvatarService.instance = new AvatarService();
		}
		return AvatarService.instance;
	}

	/**
	 * Generate a user avatar using DiceBear API
	 * @param seed - The seed for generating the avatar (usually user's name or email)
	 * @returns URL for the generated avatar
	 */
	public avatar(seed: string): string {
		const encodedSeed = encodeURIComponent(seed);
		return `${this.DICEBEAR_API}?seed=${encodedSeed}`;
	}

	/**
	 * Generate an organization logo using UI Avatars API
	 * @param name - The organization name
	 * @param size - Optional size parameter (default: 128)
	 * @returns URL for the generated logo
	 */
	public initial(name: string, size: number = 128): string {
		const encodedName = encodeURIComponent(name);
		return `${this.UI_AVATARS_API}/?size=${size}&name=${encodedName}`;
	}
}

export const avatarService = AvatarService.getInstance();
