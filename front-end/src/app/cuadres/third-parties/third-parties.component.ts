import { Component, ComponentFactoryResolver, OnInit, ViewChild, ViewContainerRef } from '@angular/core';
import { AgreementsComponent } from './agreements/agreements.component';

@Component({
  selector: 'app-third-parties',
  templateUrl: './third-parties.component.html',
  styleUrls: ['./third-parties.component.css']
})
export class ThirdPartiesComponent implements OnInit {

  @ViewChild('dynamicCcontainer', {read: ViewContainerRef}) dynamicCcontainer: ViewContainerRef;

  selectedValue: string;

  constructor(
    private componentFactoryResolver: ComponentFactoryResolver,
  ) { }

  ngOnInit() {

    this.changeSelectedAccount('agreements');
  }

  loadComponent(component: string){
    this.dynamicCcontainer.clear();
    //TODO: CHECK ONDESTROY 
    switch(component){
      case 'agreements':
        const componentFactory = this.componentFactoryResolver.resolveComponentFactory(AgreementsComponent);
        const componentRef = this.dynamicCcontainer.createComponent(componentFactory);
      break;
    }
  }

  changeSelectedAccount(value:string){
    this.selectedValue = value;
    this.loadComponent(value);
  }
}
