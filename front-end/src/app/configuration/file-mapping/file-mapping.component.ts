import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { Map, MappingFileIndex, MappingIndex } from 'src/app/Interfaces/mapping-file.interface';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { MappingFilesService } from 'src/app/services/mappingFiles/mapping-files.service';
import { MatSelectChange } from '@angular/material';

import Swal from 'sweetalert2';

@Component({
  selector: 'app-file-mapping',
  templateUrl: './file-mapping.component.html',
  styleUrls: ['./file-mapping.component.css']
})
export class FileMappingComponent implements OnInit {

  @ViewChild('detailModal') 
  private detailModa: ElementRef;

  sourceFilter: 'thirdParty' | 'accounting' | 'all' = 'accounting';
  selectedIndex = 0;
  mappingInfo: MappingFileIndex[];
  detailMap: Map[];
  mappingIndex: MappingIndex[] = [];
  detailMapInfo: MappingFileIndex;
  editMapping: MappingFileIndex;

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

  filterChange(event: MatSelectChange){
    this.getMappingInfo(event.value);
  }

  getMappingInfo(source: 'thirdParty' | 'accounting' | 'all' = 'accounting'){
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.mappingFileService.index(source).subscribe(
      (response) => this.mappingInfo = response,
      (err) => console.error(err),
      () => Swal.close()
    );
  }

  mappingResult(event:boolean){
    if(event){
      this.getMappingInfo();
    }
    this.selectedIndex = 0;
  }

  editAction(action: string){
    if(action === 'success'){
      this.selectedIndex = 0;
      this.getMappingInfo();
    }
    if(action === 'cancel'){
      this.selectedIndex = 0;
    }
  }
  listAction(action: { type: string, map: MappingFileIndex }){
    if(action.type === 'detail'){
      this.openDetailDialog(action.map);
      console.log('show detail');
    }
    if(action.type === 'edit'){
      this.editMapping = action.map;
      this.selectedIndex = 2;
      console.log(`Edit ${action.map.id}`);
    }
  }

  openDetailDialog(map: MappingFileIndex){
    this.detailMapInfo = map;
    this.modalService.open( this.detailModa, { centered: true, size: 'lg' });
  }

  getIndexName(mapIndex: number){
    return this.mappingIndex
      .find((index) => index.id === mapIndex).description;
  }

}
