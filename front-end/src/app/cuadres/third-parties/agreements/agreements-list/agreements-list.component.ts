import { Component, OnDestroy, OnInit } from '@angular/core';
import { MatDialog, MatTableDataSource } from '@angular/material';
import { Subject } from 'rxjs';
import { mergeMap, takeUntil } from 'rxjs/operators';
import { AgreementsHeader } from 'src/app/Interfaces/cuadres.interface';
import { AgreementsRequestService } from 'src/app/services/cuadres/agreements/agreements-request.service';
import { AgreementsService } from 'src/app/services/cuadres/agreements/agreements.service';
import { ConfirmDialogComponent } from 'src/app/shared/components/confirm-dialog/confirm-dialog.component';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-agreements-list',
  templateUrl: './agreements-list.component.html',
  styleUrls: ['./agreements-list.component.css']
})
export class AgreementsListComponent implements OnInit, OnDestroy {

  dataSource = new MatTableDataSource<AgreementsHeader>([]);

  displayedColumns = [
    'balanceDate',
    'Actions'
  ]

  componentDestoryed$: Subject<boolean> = new Subject();
  constructor(
    private dialog: MatDialog,
    private agreementsRequestService: AgreementsRequestService,
    private agreementsService: AgreementsService
  ) { }

  ngOnInit() {
    this.agreementsService.agreementsList$
    .pipe(takeUntil(this.componentDestoryed$))
    .subscribe(
      (agreeemnts) => this.dataSource.data = agreeemnts
    );
    this.getAgreements();
  }

  getAgreements(){
    if(this.dataSource.data){
      return;
    }


    this.agreementsRequestService.getAgreementsIndex().subscribe(
      (response) => {
    
        this.agreementsService.setAgreementList(response);
      },
      (err) => console.error(err)
    );
  }

  uploadAgreement(element: AgreementsHeader){
    const date = element.balanceDate.split(' ').splice(0,1).join('');
    this.agreementsService.setSelectedDate(date);
    this.agreementsService.setSelectedIndex(2);
  }

  openConfirmDelete(element: AgreementsHeader){
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      disableClose: true,
      width: '500px',
    });
    
    dialogRef.componentInstance.title = 'Confirmar';
    dialogRef.componentInstance.message = 
      `¿Desea eliminar el cargue para la fecha ${element.balanceDate.split(' ').slice(0,1).join('')}?`;

    dialogRef.componentInstance.confirm = () => {
      Swal.fire({
        title: 'Procesando',
        allowOutsideClick: false,
        showConfirmButton: false,
        imageUrl: 'assets/images/2.gif',
  
      });
      this.agreementsRequestService.deleteAgreements(element.agreementId)
      .pipe(
        mergeMap(() => this.agreementsRequestService.getAgreementsIndex())
      )
      .subscribe(
        (response) => {
          this.agreementsService.setAgreementList(response)
        },
        (err) => console.error(err),
        () => Swal.close()

      );
      dialogRef.close();
    };

    dialogRef.componentInstance.cancel = () => dialogRef.close();

  }

  goToDetail(element: AgreementsHeader){
    this.agreementsService.setSelectedIndex(1);
    this.agreementsService.setSelectedResult(element.agreementDate.split(' ').slice(0,1).join(''));
  }

  ngOnDestroy(): void {
    this.componentDestoryed$.next(true);
    this.componentDestoryed$.complete();
  }


}
