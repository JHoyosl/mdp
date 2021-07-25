import { LoginModel } from './../../models/login.model';
import { createAction, props } from '@ngrx/store';

export const loginAction = createAction(
    '[LOGIN] Login',
    props<{loginModel: LoginModel}>()
);

export const logoutAction = createAction(
    '[LOGOUT] Logout',
    props<{loginModel: LoginModel}>()
);
