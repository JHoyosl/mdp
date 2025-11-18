export class UserModel {
  id: number;
  email: string;
  password: string;
  current_company: string;
  names: string;
  type: string;
  last_names: string;
  token?: string;

  setValues(Object: any) {
    this.id = Object.id;
    this.email = Object.email;
    this.password = Object.password;
    this.current_company = Object.current_company;
    this.names = Object.names;
    this.type = Object.type;
    this.last_names = Object.last_names;
  }
}
