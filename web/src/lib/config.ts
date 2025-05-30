import { dev } from '$app/environment';

interface Config {
  auth: {
    tokenKey: string;
    tokenExpiry: number;
    cookieOptions: {
      path: string;
      secure: boolean;
      sameSite: 'strict' | 'lax' | 'none';
    };
  };
  api: {
    baseUrl: string;
  };
}

const config: Config = {
  auth: {
    tokenKey: import.meta.env.VITE_AUTH_TOKEN_KEY || 'auth_token',
    tokenExpiry: Number(import.meta.env.VITE_AUTH_TOKEN_EXPIRY) || 24 * 60 * 60, // 24 hours in seconds
    cookieOptions: {
      path: '/',
      secure: !dev,
      sameSite: (import.meta.env.VITE_AUTH_COOKIE_SAME_SITE as 'strict' | 'lax' | 'none') || 'lax',
    },
  },
  api: {
    baseUrl: import.meta.env.VITE_API_URL
  },
};

export default config; 