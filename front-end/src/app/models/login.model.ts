import { UserModel } from './user.model';

export class LoginModel {

    isLogin: boolean;
    access_token: string;
    createed_at: number;
    expires_in: number;
    refresh_token: string;
    type: string;

    constructor() {
        this.isLogin = false;
        this.access_token = null;
        this.createed_at = null;
        this.expires_in = null;
        this.refresh_token = null;

    }
}
