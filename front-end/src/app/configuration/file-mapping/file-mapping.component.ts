import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';

import { Map, MappingFileIndex, MappingIndex } from 'src/app/Interfaces/mapping-file.interface';
import { MappingFilesService } from 'src/app/services/mapping-files.service';
import Swal from 'sweetalert2';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-file-mapping',
  templateUrl: './file-mapping.component.html',
  styleUrls: ['./file-mapping.component.css']
})
export class FileMappingComponent implements OnInit {

  @ViewChild('detailModal') 
  private detailModa: ElementRef;
  selectedIndex = 0;
  mappingInfo: MappingFileIndex[];
  detailMap: Map[];
  mappingIndex: MappingIndex[] = [];

  modalInfo = {
    'title':'Asociación de Campos (Mapeo)',
    'body':'body'
  };

  constructor(
    private mappingFileService: MappingFilesService,
    private modalService: NgbModal,
  ) { }

  ngOnInit() {
    this.getMappingInfo();
  }

  getMappingInfo(){
    this.mappingFileService.index('all').subscribe(
      (response) => this.mappingInfo = response,
      (err) => console.error(err)
    );
  }

  mappingResult(event:boolean){
    if(event){
      this.getMappingInfo();
    }
    this.selectedIndex = 0;
  }

  setAction(action: { type: string, map: MappingFileIndex }){
    if(action.type === 'detail'){
      this.openDetailDialog(action.map);
      console.log('show detail');
    }
    if(action.type === 'edit'){
      console.log(`Edit ${action.map.id}`);
    }
  }

  openDetailDialog(map: MappingFileIndex){
    const type = map.type === 'conciliar_externo' 
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
        this.detailMap = map.map;
        this.modalService.open( this.detailModa, { centered: true, size: 'lg' });
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
