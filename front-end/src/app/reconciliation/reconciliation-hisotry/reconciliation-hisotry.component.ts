import { animate, state, style, transition, trigger } from '@angular/animations';
import { Component, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from '@angular/material';
import { Router } from '@angular/router';
import { ReconciliationItem } from 'src/app/Interfaces/reconciliation.interface';
import { ReconciliationService } from 'src/app/services/reconciliation/reconciliation.service';

@Component({
  selector: 'app-reconciliation-hisotry',
  templateUrl: './reconciliation-hisotry.component.html',
  styleUrls: ['./reconciliation-hisotry.component.css'],
  animations: [
    trigger('detailExpand', [
      state('collapsed', style({height: '0px', minHeight: '0'})),
      state('expanded', style({height: '*'})),
      transition('expanded <=> collapsed', animate('225ms cubic-bezier(0.4, 0.0, 0.2, 1)')),
    ]),
  ],
})
export class ReconciliationHisotryComponent implements OnInit {

  @ViewChild(MatPaginator) paginator: MatPaginator;
  
  private reconciliationItems: ReconciliationItem[];
  
  dataSource = new MatTableDataSource<ReconciliationItem>();
  
  columnsToDisplay: string[] = [
    'process',
    'dates',
    'type',
    'step',
    'status',
    'Actions'
  ];

  subColumnsToDisplay: string[] = [
    'name',
    'bankAccount',
    'externalDebit',
    'externalCredit',
    'localCredit',
    'localDebit',

  ];
  

  constructor(private reconciliationService: ReconciliationService,
      private router: Router
    ) { }

  ngOnInit() {
    this.getAccountProcess();
  }

  getAccountProcess(){
    this.reconciliationService.getAccountProcess().subscribe(
      (response) => {
        const groupedData = [];
        let tmpArray = [];
        const data = response.reduce((acc, curr) => {
          if(acc == curr.process){
            tmpArray.push(curr);  
          }else{
            groupedData.push(tmpArray);
            tmpArray = [];
          }
          return curr.process;
        }, response[0].process);

        groupedData.push(tmpArray);
        this.reconciliationItems = groupedData.filter(el => el.length > 0);
        this.dataSource.data = this.reconciliationItems;
      },
      (err) => {
        console.error(err);
      }
    );
  }

  goToProcess(event: Event, element:ReconciliationItem){
    event.stopPropagation();
    if(element[0].type === 'INIT'){
      this.router.navigate([`/conciliar/initAcc/${element[0].process}`]);
    }else{
      this.router.navigate([`/conciliar/process/${element[0].process}`]);
    }
  }
}
