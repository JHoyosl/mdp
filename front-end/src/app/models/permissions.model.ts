export class PermissionsModel {
    id: number;
    name: string;
    guard: string;
    selected = false;

    setValues( Object: any) {

        this.id = Object.id;
        this.name = Object.name;
        this.guard = Object.guard;

    }

}

