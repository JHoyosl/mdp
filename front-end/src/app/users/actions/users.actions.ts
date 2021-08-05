import { UserModel } from 'src/app/models/user.model';
import { CompanyModel } from './../../models/company.model';
import { createAction, props } from '@ngrx/store';

export const getUserInfoAction = createAction(
    '[GET USER INFO] Get User Info',
    props<{ user: UserModel }>(),
);

export const getCompaniesAction = createAction(
    '[GET COMPANIES] Get Companies',
    props<{ user: UserModel }>(),
);
