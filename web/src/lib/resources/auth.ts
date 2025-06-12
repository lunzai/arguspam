import type { User } from "$models/user.js";

export interface LoginResponse {
    data: {
        token: string;
        user: User;
    }
}

// export interface RegisterResponse {
//     token: string;
//     user: User;
// }