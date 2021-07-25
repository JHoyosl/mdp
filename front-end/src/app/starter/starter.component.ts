import { Component, AfterViewInit } from '@angular/core';
import { AuthServiceService } from '../services/auth-service.service';
@Component({
  templateUrl: './starter.component.html'
})
export class StarterComponent implements AfterViewInit {
  subtitle: string;
  constructor( private auth: AuthServiceService) {
    this.subtitle = 'This is some text within a card block.';
  }
  

  test(){
      
    this.auth.refreshToken();
  
  }
  ngAfterViewInit() {}
}
