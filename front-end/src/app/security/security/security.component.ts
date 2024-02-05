import { RolModel } from './../../models/rol.model';
import { UserModel } from './../../models/user.model';
import { PermissionsModel } from './../../models/permissions.model';
import { ApiRequestService } from './../../services/api-request.service';
import { Component, OnInit, ViewChild } from '@angular/core';
import { NgbTabset, NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import Swal from 'sweetalert2';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-security',
  templateUrl: './security.component.html',
  styleUrls: ['./security.component.css']
})
export class SecurityComponent implements OnInit {

  @ViewChild('tabSet')
  private tabSet: NgbTabset;

  closeResult = '';
  newRolName = '';
  newPermisionName = '';
  roles: any;
  permissions: any;
  selectedRol: any;
  searchUser = '';
  userSearchArray = [];
  selectedUser = new UserModel();
  rolesList = [];

  constructor(private modalService: NgbModal, private apiRequestService: ApiRequestService, private toastr: ToastrService) { }

  ngOnInit() {
    this.getRoles();
    this.tabSet.activeId = 'usaurioRol';
    this.listarUsuarios(null);
  }

  chooseUserRol(rol: RolModel){

    const formData = new FormData();
    formData.set('userId', this.selectedUser.id.toString());
    formData.set('rolId', rol.id.toString());

    if (rol.selected) {

      this.apiRequestService.setRolPermission(formData, 'user/revokeUserRol')
      .subscribe( (resp) => {

      }, (err) => {
        console.error(err);
      });

    } else {

      this.apiRequestService.setRolPermission(formData, 'user/setUserRol')
      .subscribe( (resp) => {

      }, (err) => {
        console.error(err);
      });
    }
  }

  choosePermission(permission) {

    const formData = new FormData();
    formData.set('rolId', this.selectedRol.id);
    formData.set('permissionId', permission.id);

    if (permission.selected) {

      this.apiRequestService.revokeRolPermission(formData, 'user/revokeRolPermission')
      .subscribe( (resp) => {

      }, (err) => {
        console.error(err);
      });

    } else {

      this.apiRequestService.setRolPermission(formData, 'user/setRolPermission')
      .subscribe( (resp) => {

      }, (err) => {
        console.error(err);
      });
    }
  }

  listarUsuarios(event) {

    if (this.searchUser.length > 2) {

      this.apiRequestService.getCollection(`user/getUserList/${this.searchUser}`)
      .subscribe( (response) => {

        this.userSearchArray = response;

      }, (err) => {

        console.error(err);
      });
    } else {

      if (this.searchUser === '') {
        this.userSearchArray = [];
      }
    }

  }


  getPermissions(rolName: string) {

    this.apiRequestService.getPermissionList(rolName)
      .subscribe( (resp) => {

        const permissionArray = [];
        let permissionList: [];
        permissionList = resp['data'].permissions;
        let permissionRole: [];
        permissionRole = resp['data']['role']['permissions'];

        for (let i = 0; i < permissionList.length; i++) {
          const tmpPermission = new PermissionsModel();
          tmpPermission.setValues(permissionList[i]);
          permissionArray.push(tmpPermission);
        }

        permissionArray.forEach(permission => {
          permissionRole.forEach(rolePermission => {
            if (permission.id === rolePermission['id']) {
              permission.selected = true;
            }
          });
        });

        this.permissions = permissionArray;

      }, (err) => {
        console.error(err);
      });
  }

  getUserRoles(user: UserModel) {

    this.apiRequestService.getUserRoleList(user)
      .subscribe( (resp) => {

        const rolArray = resp['data']['listado'];
        const rolAsign = resp['data']['asignados'];

        rolArray.forEach(rol => {
          const tmpRol = new RolModel();
          tmpRol.setValues(rol);

          rolAsign.forEach(asignado => {

            if (asignado === tmpRol.name) {

              tmpRol.selected = true;
            }
          });

          this.rolesList.push(tmpRol);

        });

      }, (err) => {
        console.error(err);
      });
  }

  getRoles() {

    this.apiRequestService.getRoleList()
      .subscribe( (resp) => {

        this.roles = resp;

      }, (err) => {
        console.error(err);
      });
  }

  saveNewPermission() {

    if (this.newPermisionName === '' || this.newPermisionName.length < 3) {

      Swal.fire(
        'Error',
        'El nombre debe tener por lo menos 3 caractéres',
        'warning'
      );

      return;
    }

    this.apiRequestService.addPemission(this.newPermisionName)
      .subscribe( (resp) => {
        this.toastr.success('Permiso Creado', 'Success!');
        this.modalService.dismissAll();
        if (this.selectedRol !== null) {
          this.getPermissions(this.selectedRol);
        }
      }, (err) => {

        Swal.fire(
          'Error',
          'Se presentó un error en la creación',
          'warning'
        );
        this.modalService.dismissAll();
      });
  }
  saveNewRol() {

    if (this.newRolName === '' || this.newRolName.length < 3) {

      Swal.fire(
        'Error',
        'El nombre debe tener por lo menos 3 caractéres',
        'warning'
      );

      return;
    }

    this.apiRequestService.addRol(this.newRolName)
      .subscribe( (resp) => {
        this.getRoles();
        this.toastr.success('Rol Creado', 'Success!');
        this.modalService.dismissAll();
      }, (err) => {

        Swal.fire(
          'Error',
          'Se presentó un error en la creación',
          'warning'
        );
        this.modalService.dismissAll();
      });

  }


  chooseRol(rol: any) {
    this.selectedRol = rol;
    this.getPermissions(rol.name);
  }

  chooseUser(user: UserModel) {
    this.selectedUser = user;
    this.getUserRoles(user);
  }

  open(content) {
    this.modalService.open(content, {ariaLabelledBy: 'modal-basic-title'}).result.then((result) => {
      this.closeResult = `Closed with: ${result}`;
    }, (reason) => {
      this.closeResult = `Dismissed ${this.getDismissReason(reason)}`;
    });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  nuevoRolShow(){
    
  }
}
