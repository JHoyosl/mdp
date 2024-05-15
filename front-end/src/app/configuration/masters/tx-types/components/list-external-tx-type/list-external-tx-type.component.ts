import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { MatPaginator, MatSort, MatTableDataSource } from '@angular/material';
import { ToastrService } from 'ngx-toastr';
import { Observable } from 'rxjs';
import { filter, skipUntil, skipWhile, takeWhile } from 'rxjs/operators';
import { ExternalTxType } from 'src/app/Interfaces/txType.interface';
import { TxTypeService } from 'src/app/services/tx-type/tx-type.service';

import Swal from 'sweetalert2';

@Component({
  selector: 'app-list-external-tx-type',
  templateUrl: './list-external-tx-type.component.html',
  styleUrls: ['./list-external-tx-type.component.css']
})
export class ListExternalTxTypeComponent implements OnInit {
  @Output() deleted = new EventEmitter<{source: 'external', status: boolean}>();
  @Output() toEdit = new EventEmitter<{source: 'external', txType: ExternalTxType}>();

  @Input() 
  set externalInfo(val: ExternalTxType[]){
    this.dataSource.data = val;

  }
  
  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;
  
  dataSource = new MatTableDataSource<ExternalTxType>([]);
  displayedColumns = ['bank', 'description', 'tx', 'reference', 'type', 'sign', 'Actions' ];
  filter$: Observable<string>;

  constructor(
    private txTypeService: TxTypeService,
    private toastr: ToastrService
  ) { 
    this.filter$ = this.txTypeService.filter$;
  }

  ngOnInit() {
    this.dataSource.sort = this.sort;
    this.dataSource.paginator = this.paginator;
    this.filter$.subscribe(
      (response) => {
        if(response){
          this.dataSource.filter = response.trim().toLowerCase();
        }
      }
    );
  }

  edit(el: ExternalTxType){
    this.toEdit.emit({source: 'external', txType: el});
  }

  delete(el: ExternalTxType){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.txTypeService.deleteExternal(el.id).subscribe(
      (_) => {
        this.toastr.success('Tx eliminada');
        this.deleted.emit({source:'external', status: true});
      },
      (err) => {
        console.error(err);
        this.toastr.error(err.error);
        this.deleted.emit({source:'external', status: false});
      },
      () => Swal.close()
    );
  }
}
