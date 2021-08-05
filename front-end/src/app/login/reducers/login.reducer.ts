import { LoginModel } from './../../models/login.model';
import { loginAction, logoutAction } from '../actions/login.actions';
import { createReducer, on } from '@ngrx/store';


export const initialState: LoginModel = new LoginModel();

const _loginReducer = createReducer(initialState,
  on(loginAction, (state, {loginModel: loginModel} ) => loginModel),

  on(logoutAction, (state, {loginModel: loginModel}) => loginModel)

);

export function loginReducer(state, action) {
  return _loginReducer(state, action);
}
