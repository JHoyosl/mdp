import { Component, OnInit } from '@angular/core';
import { ThirdPartiesService } from '../services/third-parties.service';
import { ThirdPartyAccount, ThirdPartyHeaderInfo } from '../Interfaces/thirdParties.interface';
import { MatSelectChange, MatTabChangeEvent } from '@angular/material';
import Swal from 'sweetalert2';
import { finalize } from 'rxjs/operators';

@Component({
  selector: 'app-third-parties',
  templateUrl: './third-parties.component.html',
  styleUrls: ['./third-parties.component.css']
})
export class ThirdPartiesComponent implements OnInit {

  selectedValue = null;
  selectedIndex = 0;
  selectedAccount: ThirdPartyAccount;
  accountList: ThirdPartyAccount[] = [];
  detailHeader: ThirdPartyHeaderInfo;

  constructor( private thirdPartiesService: ThirdPartiesService ) { }

  ngOnInit() {
    this.getAccountsList();
  }

  getAccountsList(): void {

    this.thirdPartiesService.getThirdPartiesAccounts().subscribe(
      (response) => {
        this.accountList = response;
    
      },
      (err) => {
        console.error(err);
    
      }
    );
  }

  changeSelectedAccount(accountId: number ): void {

    this.selectedAccount = 
      this.accountList.find((account) => account.id === accountId);
    
  }

  showDetail( header: ThirdPartyHeaderInfo ){
    this.detailHeader = header;
    this.selectedIndex = 2;
  }
  
  successUpload(event): void {
    this.selectedAccount = 
      this.accountList.find((account) => account.id === this.selectedValue);
    this.selectedIndex = 0;

  }

  cancelUpload(){
    this.selectedIndex = 0;
  }

}
