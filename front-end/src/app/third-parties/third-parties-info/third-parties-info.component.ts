import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from '@angular/material';
import { ThirdPartyAccount, ThirdPartyHeaderInfo } from 'src/app/Interfaces/thirdParties.interface';
import { ThirdPartiesService } from 'src/app/services/third-parties.service';
import Swal from 'sweetalert2';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'app-third-parties-info',
  templateUrl: './third-parties-info.component.html',
  styleUrls: ['./third-parties-info.component.css']
})
export class ThirdPartiesInfoComponent implements OnInit {

  @Output() showDetail = new EventEmitter<ThirdPartyHeaderInfo>();
  
  @Input()
  set account(account: ThirdPartyAccount){
    this._account = account;
    this.getAccountHeader();
  }
  get account(): ThirdPartyAccount {
    return this._account;
  }
  
  @ViewChild(MatPaginator) paginator: MatPaginator;
  
  private _account: ThirdPartyAccount;
  
  dataSource = new MatTableDataSource<ThirdPartyHeaderInfo>();
  
  displayedColumns: string[] = [
    'startDate',
    'endDate',
    'fileName',
    'rows',
    'createdAt',
    'status',
    'Actions'
  ];

  constructor(private thirdPartiesService: ThirdPartiesService, 
    private toastrService:ToastrService ) { }

  ngOnInit() {
    this.dataSource.paginator = this.paginator;
  }

  getAccountHeader(){
    if(!this._account){
      return;
    }
    
    if(!Swal.isVisible()){

      Swal.fire({
        title: 'Procesando',
        allowOutsideClick: false,
        showConfirmButton: false,
        imageUrl: 'assets/images/2.gif',
  
      });
    }
    this.thirdPartiesService.getAccountHeaderInfo(this._account.id).subscribe(
      (response) => {
    
        this.dataSource.data = response;
      },
      (err) => {
        console.error(err);
    
      },
    );
  }

  confirmDelete( header: ThirdPartyHeaderInfo ): void{
    Swal.fire({
      title: 'Confirmación',
      text: `¿Desea eliminar el cargue para las fechas ${header.startDate} - ${header.endDate}?`,
      showConfirmButton: true,
      confirmButtonAriaLabel: 'Eliminar',
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#d33',
      showCancelButton: true,
      cancelButtonAriaLabel: 'Cancelar',
      cancelButtonText: 'Cancelar',
      cancelButtonColor: '#3085d6'
    }).then((result) => {
      if(result.value){
        this.onDelete(header);
      }
    });
  }

  onDelete( header: ThirdPartyHeaderInfo ): void {



    this.thirdPartiesService.deleteLastUpload(header).subscribe(
      (response) => {
        this.toastrService.success('Registro eliminado');
        this.getAccountHeader();

      },
      (err) => {
        console.error(err);
        Swal.fire({
          type: 'error',
          text: err.error,
        });
        
        
      }
    );
    
  }

  goToDetail( header: ThirdPartyHeaderInfo): void {
    this.showDetail.emit(header);
  }
}
