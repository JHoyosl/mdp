import { Component, Input, OnInit } from '@angular/core';
import { MappingFileIndex } from 'src/app/Interfaces/mapping-file.interface';

@Component({
  selector: 'app-list-mapping',
  templateUrl: './list-mapping.component.html',
  styleUrls: ['./list-mapping.component.css']
})
export class ListMappingComponent implements OnInit {

  @Input() type: 'thirdParty' | 'accounting';
  @Input() data: MappingFileIndex;

  displayedColumns = [
    'bank',
    'description',
    'separator',
    'createdBy',
    'Actions'
  ];

  constructor() { }

  ngOnInit() {
    console.log(this.data);
  }

  signToText(sign: string): string{
    switch(sign){
      case ',':
        return 'coma (,)';
      case '.':
        return 'punto (.)';
      default:
          return '';
    }
  }

}
