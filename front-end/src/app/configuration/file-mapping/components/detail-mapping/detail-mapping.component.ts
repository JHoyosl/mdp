import { Component, Input, OnInit } from '@angular/core';
import { MappingIndex, Map, MappingFileIndex } from 'src/app/Interfaces/mapping-file.interface';
import { MappingFilesService } from 'src/app/services/mappingFiles/mapping-files.service';

import Swal from 'sweetalert2';

@Component({
  selector: 'app-detail-mapping',
  templateUrl: './detail-mapping.component.html',
  styleUrls: ['./detail-mapping.component.css']
})
export class DetailMappingComponent implements OnInit {

  @Input() map: MappingFileIndex;
  
  mappingIndex: MappingIndex[];
  detailMap: Map[];

  constructor(private mappingFileService: MappingFilesService,) { }

  ngOnInit() {
    const type = this.map.type === 'conciliar_externo' 
      ? 'external'
      : 'internal';
      Swal.fire({
        title: 'Procesando',
        allowOutsideClick: false,
        showConfirmButton: false,
        imageUrl: 'assets/images/2.gif',
  
      });
    this.mappingFileService.getMapIndex(type).subscribe(
      (response) => {
        Swal.close();
        this.mappingIndex = response;
        this.detailMap = this.map.map;
        
      },
      (err) => {
        Swal.close();
        console.error(err)
      }
    );
  }

  getIndexName(mapIndex: number){
    return this.mappingIndex
      .find((index) => index.id === mapIndex).description;
  }

}
