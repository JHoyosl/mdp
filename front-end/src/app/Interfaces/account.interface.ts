import { Bank } from "./bank.interface";

export interface Account {
  id: number;
  bankId: number;
  companyId: number;
  accType: string;
  bankAccount: string;
  localAccount: string;
  mapId: number;
  deletedAt: string;
  createdAt: string;
  updatedAt: string;
  banks?: Bank;
}