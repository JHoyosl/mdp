import { Component, OnInit } from '@angular/core';
import { NgForm } from '@angular/forms';
import Swal from 'sweetalert2';
import { AuthServiceService } from 'src/app/services/auth-service.service';
import { UserModel } from 'src/app/models/user.model';
import { Router } from '@angular/router';

@Component({
  selector: 'app-recover-pssw',
  templateUrl: './recover-pssw.component.html',
  styleUrls: ['./recover-pssw.component.css']
})
export class RecoverPsswComponent implements OnInit {
  
  user = new UserModel();

  constructor(private auth:AuthServiceService, private router:Router) { }

  ngOnInit() {

  }
  
  onSubmit( form:NgForm){
    
    if( form.invalid ){

      Swal.fire({
        type: 'warning',
        text: 'Debe Ingresar un correo válido',
      });
      return;
    }
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: "assets/images/2.gif",
       
    });
    this.auth.recoveryPassword(this.user.email)
      .subscribe( (response)=>{
    
        Swal.fire({
          type: 'warning',
          text: 'Se envió un mensaje al correo : \n'+this.user.email
        });
        this.router.navigate(['/login']);

        
      }, (err)=>{
  
        console.error(err);
      })
  }
}
