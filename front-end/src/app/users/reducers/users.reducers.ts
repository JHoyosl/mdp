import { UserModel } from 'src/app/models/user.model';
import { getUserInfoAction, getCompaniesAction } from './../actions/users.actions';
import { CompanyModel } from './../../models/company.model';
import { createReducer, on } from '@ngrx/store';

export const initialState:  UserModel = new UserModel();

const _userReducer = createReducer(initialState,
  on(getUserInfoAction, (state, { user: newUser } ) => newUser ),

  on(getCompaniesAction, (state, { user: userCompany } ) => userCompany),
);

export function userReducer(state, action) {
  return _userReducer(state, action);
}
