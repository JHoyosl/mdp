import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { MatDialog } from '@angular/material';
import { ApiRequestService } from '../services/api-request.service';
import { ToastrService } from 'ngx-toastr';
import { NgbTabset } from '@ng-bootstrap/ng-bootstrap';
import { UploadFileComponent } from './components/upload-file/upload-file.component';
import { CuadresService } from '../services/cuadres/cuadres.service';
import { Subject, Subscribable } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import Swal from 'sweetalert2';
import { CuadresRequestsService } from '../services/cuadres/cuadres-requests.service';

@Component({
  selector: 'app-cuadres',
  templateUrl: './cuadres.component.html',
  styleUrls: ['./cuadres.component.css']
})
export class CuadresComponent implements OnInit, OnDestroy {

  @ViewChild('tabSet')
  private tabSet: NgbTabset;
  
  dateSubscriber
  selectedIndex = 0;
  balanceList: any = [];
  response = 'text';

  componentDestroyed$: Subject<boolean> = new Subject()
  constructor(
    public dialog: MatDialog,
    private toastr: ToastrService,
    private cuadreServices: CuadresService,
    private cuadresRequestService: CuadresRequestsService
  ) { 
    this.cuadreServices.selectedDate$.pipe(takeUntil(this.componentDestroyed$)).subscribe(
      (response) => {
        this.selectedIndex = response ? 1 : 0;
      },
      (err) => console.error(err)
    )
  }

  ngOnInit() {
  }

  openDialog(): void {
    const dialogRef = this.dialog.open(UploadFileComponent, {
      disableClose: true,
      width: 'auto',
      data: {
        tile: 'Cargue Balance',
        source: 'balance'
      }
    });
    dialogRef.componentInstance.title = 'Cargue Balance';

    dialogRef.afterClosed().subscribe(result => {
      if(result === 'success'){
        this.cuadreServices.setBalanceList(null);
        Swal.fire({
          title: 'Procesando',
          allowOutsideClick: false,
          showConfirmButton: false,
          imageUrl: 'assets/images/2.gif',
    
        });
        this.cuadresRequestService.getBalanceIndex().subscribe(()=> Swal.close());
      }
      
    });
  }

  ngOnDestroy(): void {
    this.componentDestroyed$.next();
    this.componentDestroyed$.complete();
  }
}
