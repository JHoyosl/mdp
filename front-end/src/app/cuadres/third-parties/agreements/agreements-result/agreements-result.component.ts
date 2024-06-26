import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from '@angular/material';
import { Observable, Subject } from 'rxjs';
import { mergeMap, takeUntil } from 'rxjs/operators';
import { AgreeementsResult } from 'src/app/Interfaces/cuadres.interface';
import { AgreementsRequestService } from 'src/app/services/cuadres/agreements/agreements-request.service';
import { AgreementsService } from 'src/app/services/cuadres/agreements/agreements.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-agreements-result',
  templateUrl: './agreements-result.component.html',
  styleUrls: ['./agreements-result.component.css']
})
export class AgreementsResultComponent implements OnInit, OnDestroy {

  @ViewChild(MatPaginator) paginator: MatPaginator;
  
  date: string = null;

  dataSource = new MatTableDataSource<AgreeementsResult>([]);
  displayedColumns = [
    'account',
    'line',
    'name',
    'saldoActual',
    'sumSalcuo',
    'difference',
  ];

  destroyed$ = new Subject<boolean>();
  constructor(
    private agreementsRequestService: AgreementsRequestService,
    private agreementsService: AgreementsService
  ) { 
    this.dataSource.paginator = this.paginator;
  }

  ngOnInit() {

    this.agreementsService.selectedResult$
      .pipe(
        takeUntil(this.destroyed$),
        mergeMap((response) =>{
          this.date = response;
          return this.agreementsRequestService.getAgreementsResult(response)
        })
      ).subscribe(
        (response) => {
          this.dataSource.data = response;
        },
        (err) => {
      
          console.error(err)
        }
      );
  }

  download(){
    this.agreementsRequestService.downloadBalanceResult(this.date).subscribe(
      (response) => {
        const myBlob = new Blob([response], {type: 'application/vnd.oasis.opendocument.spreadsheet'});
        const downloadUrl = URL.createObjectURL(myBlob);
        const a = document.createElement('a');

        a.href = downloadUrl;
        a.download = `convenios_${this.date}.xlsx`;
        a.click();
      }
    );
  }

  ngOnDestroy(): void {
    this.destroyed$.next(true);
    this.destroyed$.complete();
  }
}
