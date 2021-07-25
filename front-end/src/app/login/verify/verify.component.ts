import { Component, OnInit } from '@angular/core';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';
import { ActivatedRoute } from '@angular/router';
import { AuthServiceService } from '../../services/auth-service.service';
import { UserModel } from '../../models/user.model';

@Component({
  selector: 'app-verify',
  templateUrl: './verify.component.html',
  styleUrls: ['./verify.component.css']
})
export class VerifyComponent implements OnInit {

  constructor( private route: ActivatedRoute, private auth:AuthServiceService ) { }
  
  user = new UserModel();
  repassword:string;
  

  ngOnInit() {
    
    this.user.token = this.route.snapshot.paramMap.get("token");
    // console.log(this.user.token);

  }
  
  onSubmit( form:NgForm ){
    
    if( form.invalid ){

      Swal.fire({
        type: 'warning',
        text: 'Todos los campos son obligatorios',
      });
      return;
    }
    if( this.user.password != this.repassword){

      Swal.fire({
        type: 'warning',
        text: 'Las contraseñas no coinciden',
      });
      return;
    }
    
    this.auth.validateUser( this.user )
      .subscribe( (response) => {
          
        console.log(response);

        this.auth.getAccessToken(this.user);
          
        
      }, (err)=>{
        
        console.log(err);
        Swal.fire({
          type: 'warning',
          text: 'Infromación Incorrecta, si el problema persiste, solicite nuevo correo de verificación',
        });
        
      })
  }
}
