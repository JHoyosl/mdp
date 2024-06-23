import { Component, Inject, OnInit } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material';
import { UploadCuadreDialog } from 'src/app/Interfaces/cuadres.interface';

@Component({
  selector: 'app-confirm-dialog',
  templateUrl: './confirm-dialog.component.html',
  styleUrls: ['./confirm-dialog.component.css']
})
export class ConfirmDialogComponent implements OnInit {

  title = 'NO TITLE';
  message = 'NO TEXT';

  confirm = () => {};
  cancel = () => {};

  constructor(
    public dialogRef: MatDialogRef<ConfirmDialogComponent>, 
    @Inject(MAT_DIALOG_DATA) public data: UploadCuadreDialog
  ) { }

  ngOnInit() {
  }

  cancelClick(){
    this.cancel();
  }
  
  confirmClick(){
    this.confirm();
  }
}
