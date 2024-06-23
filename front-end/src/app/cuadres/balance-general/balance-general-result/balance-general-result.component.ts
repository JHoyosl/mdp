import { Component, OnDestroy, OnInit } from '@angular/core';
import { MatTableDataSource } from '@angular/material';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { BalanceResultResponse } from 'src/app/Interfaces/cuadres.interface';
import { CuadresRequestsService } from 'src/app/services/cuadres/cuadres-requests.service';
import { CuadresService } from 'src/app/services/cuadres/cuadres.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-balance-general-result',
  templateUrl: './balance-general-result.component.html',
  styleUrls: ['./balance-general-result.component.css']
})
export class BalanceGeneralResultComponent implements OnInit, OnDestroy {

  accountingDataSource = new MatTableDataSource<any>();
  operationalDataSource = new MatTableDataSource<any>();
  sourceData: BalanceResultResponse = null;

  resultColumns = [
    'cuenta_balance',
    'descripcion',
    'naturaleza',
    'tipo_saldo',
    'saldo_actual',
  ];

  componentDestroy$: Subject<boolean> = new Subject();
  constructor(
    private cuadresRequestService: CuadresRequestsService,
    private cuadresServices: CuadresService
  ) { }

  ngOnInit() {
    this.cuadresServices.selectedDate$.pipe(takeUntil(this.componentDestroy$)).subscribe(
      (response) => {
        if(response){
          this.getBalanceResult(response);
        }
      },
      (err) => console.error(err)
    );
    
  }

  getBalanceResult(date){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.cuadresRequestService.getBalanceResult(date).subscribe(
      (response) => {
        Swal.close();
        this.sourceData = response;
        this.accountingDataSource.data = response.nautralezaContable;
        this.operationalDataSource.data = response.nautralezaOperativa;
      },
      (err) => {
        Swal.close();
        console.error(err)
      }
    );
  }

  ngOnDestroy(): void {
    this.componentDestroy$.next(true);
    this.componentDestroy$.complete();
  }
}
