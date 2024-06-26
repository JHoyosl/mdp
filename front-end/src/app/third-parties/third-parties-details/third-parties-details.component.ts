import { Component, Input, OnInit, ViewChild } from '@angular/core';
import { MatPaginator, MatTableDataSource } from '@angular/material';
import { ThirdPartyAccount, ThirdPartyHeaderInfo, ThirdPartyHeaderItems } from 'src/app/Interfaces/thirdParties.interface';
import { ThirdPartiesService } from 'src/app/services/third-parties.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-third-parties-details',
  templateUrl: './third-parties-details.component.html',
  styleUrls: ['./third-parties-details.component.css']
})
export class ThirdPartiesDetailsComponent implements OnInit {

  @Input() header: ThirdPartyHeaderInfo;

  @ViewChild(MatPaginator) paginator: MatPaginator;


  accountList: ThirdPartyAccount[] = [];
  
  dataSource = new MatTableDataSource<ThirdPartyHeaderItems>();
  displayedColumns: string[] = [
    'fechaMovimiento', 
    'descripcion', 
    'referencia1', 
    'valorDebito', 
    'valorCredito', 
    'valorDebitoCredito'
  ];


  constructor(private thirdPartiesService: ThirdPartiesService) { }

  ngOnInit() {
    if(this.header){
      this.getHeaderItems(this.header);
    }

  }

  getHeaderItems(header: ThirdPartyHeaderInfo): void {

    this.thirdPartiesService.getThirdPartyItems(header).subscribe(
      (response) => {
        this.dataSource.data = response;
    
      },
      (err) => {
    
        console.error(err);
      }
    );
  }
  

}
