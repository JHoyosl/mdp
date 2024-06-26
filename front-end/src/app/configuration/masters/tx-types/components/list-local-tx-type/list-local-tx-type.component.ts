import { Component, EventEmitter, Input, OnInit, Output, ViewChild } from '@angular/core';
import { MatPaginator, MatSort, MatTableDataSource } from '@angular/material';
import { ToastrService } from 'ngx-toastr';
import { Observable } from 'rxjs';
import { LocalTxType } from 'src/app/Interfaces/txType.interface';
import { TxTypeService } from 'src/app/services/tx-type/tx-type.service';

import Swal from 'sweetalert2';

@Component({
  selector: 'app-list-local-tx-type',
  templateUrl: './list-local-tx-type.component.html',
  styleUrls: ['./list-local-tx-type.component.css']
})
export class ListLocalTxTypeComponent implements OnInit {

  @Output() deleted = new EventEmitter<{source: 'local', status: boolean}>();
  @Output() toEdit = new EventEmitter<{source: 'local', txType: LocalTxType}>();
  @Input() 
  set localInfo(val: LocalTxType[]){
    this.dataSource.data = val;
  }

  @ViewChild(MatPaginator) paginator: MatPaginator;
  @ViewChild(MatSort) sort: MatSort;
  
  filter$: Observable<string>;
  dataSource = new MatTableDataSource<LocalTxType>([]);
  displayedColumns = ['description', 'tx', 'reference', 'type', 'sign', 'Actions' ];
  
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
      });
  }

  edit(el: LocalTxType){
    this.toEdit.emit({source: 'local', txType: el});
  }

  delete(el: LocalTxType){

    this.txTypeService.deleteLocal(el.id).subscribe(
      (_) => {
        this.toastr.success('Tx eliminada');
        this.deleted.emit({source:'local', status: true});
      },
      (err) => {
        console.error(err);
        this.toastr.error(err.error);
        this.deleted.emit({source:'local', status: false});
      },
      () => Swal.close()
    );
  }
}
