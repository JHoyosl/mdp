import { Component, OnInit } from '@angular/core';
import { MatPaginator, MatTableDataSource } from "@angular/material";

@Component({
  selector: 'app-accounting-detail',
  templateUrl: './accounting-detail.component.html',
  styleUrls: ['./accounting-detail.component.css']
})
export class AccountingDetailComponent implements OnInit {

  displayColumns: string[] = [];
  dataSource = new MatTableDataSource<any>();

  constructor() { }

  ngOnInit() {

  }
  
}
