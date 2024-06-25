import { Component, OnInit } from '@angular/core';
import { AgreementsService } from 'src/app/services/cuadres/agreements/agreements.service';

@Component({
  selector: 'app-agreements',
  templateUrl: './agreements.component.html',
  styleUrls: ['./agreements.component.css']
})
export class AgreementsComponent implements OnInit {

  selectedIndex = 0;
  constructor(
    private agreementsService: AgreementsService
  ) { }

  ngOnInit() {
    this.agreementsService.selectedIndex.subscribe(
      (index) => this.selectedIndex = index
    );
  }

}
