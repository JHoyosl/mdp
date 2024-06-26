import { Component, OnInit } from '@angular/core';
import { MatDialog, MatTableDataSource } from '@angular/material';
import { map } from 'jquery';
import { ToastrService } from 'ngx-toastr';
import { concat, merge, zip } from 'rxjs';
import { concatMap, mergeMap, switchMap, tap } from 'rxjs/operators';
import { BalanceList } from 'src/app/Interfaces/cuadres.interface';
import { AgreementsRequestService } from 'src/app/services/cuadres/agreements/agreements-request.service';
import { AgreementsService } from 'src/app/services/cuadres/agreements/agreements.service';
import { CuadresRequestsService } from 'src/app/services/cuadres/cuadres-requests.service';
import { CuadresService } from 'src/app/services/cuadres/cuadres.service';
import { ConfirmDialogComponent } from 'src/app/shared/components/confirm-dialog/confirm-dialog.component';
import Swal from 'sweetalert2';


@Component({
  selector: 'app-balance-general-list',
  templateUrl: './balance-general-list.component.html',
  styleUrls: ['./balance-general-list.component.css']
})
export class BalanceGeneralListComponent implements OnInit {

  dataSource = new MatTableDataSource<BalanceList>();
  
  displayedColumns: string[] = [
    'date',
    'Actions'
  ];
  
  response = '';
  constructor(
    private toastr: ToastrService,
    private cuadresRequestsService: CuadresRequestsService,
    private cuadresService: CuadresService,
    private agreementsRequestService: AgreementsRequestService,
    private agreementsService: AgreementsService,
    public dialog: MatDialog,
  ) { }

  ngOnInit() {
    this.cuadresService.balanceList$.subscribe(
      (response) => {
        if(!response){
          this.getList();
        }
        this.dataSource.data = response;
      }
    );
    
  }
  getList(){
    
    this.cuadresRequestsService.getBalanceIndex()
      .subscribe((response) => Swal.close());
    
  }

  goToDetail(element){
    this.cuadresService.setSelectedDate(element.date);
  }

  deleteUpload(el: BalanceList){

    this.cuadresRequestsService.deletBalanceUploaded(el.id.toString()).pipe(
      concatMap((response) =>{
        this.cuadresService.setBalanceList(null);
        return this.agreementsRequestService.getAgreementsIndex()
      }),
      concatMap((response) => {
        this.agreementsService.setAgreementList(response);
        return this.cuadresRequestsService.getBalanceIndex();
      })
    ).subscribe(
      (response) => {
      },
      (err) => {
        console.error(err)
      }
    );
  }

  openConfirmDelete(element: BalanceList){
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      disableClose: true,
      width: '500px',
    });
    
    dialogRef.componentInstance.title = 'Confirmar';
    dialogRef.componentInstance.message = 
      `¿Desea eliminar el cargue para la fecha ${element.date.split(' ').slice(0,1)}?`;

    dialogRef.componentInstance.confirm = () => {
      this.deleteUpload(element);
      dialogRef.close();
    };

    dialogRef.componentInstance.cancel = () => dialogRef.close();

  }
}
