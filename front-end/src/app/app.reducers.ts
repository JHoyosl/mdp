import { loginAction } from './login/actions/login.actions';

import { ActionReducerMap } from '@ngrx/store';
import { UserModel } from 'src/app/models/user.model';
import { LoginModel } from './models/login.model';
import { loginReducer } from './login/reducers/login.reducer';
import { userReducer } from './users/reducers/users.reducers';


export interface AppState {

    login: LoginModel;
    user: UserModel;


}

export const appReducers: ActionReducerMap<AppState> = {

    login: loginReducer,
    user: userReducer


};
