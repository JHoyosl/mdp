import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { Map, MappingFileIndex, MappingIndex } from 'src/app/Interfaces/mapping-file.interface';

import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { MappingFilesService } from 'src/app/services/mappingFiles/mapping-files.service';
import { MatSelectChange } from '@angular/material';

import Swal, { SweetAlertResult } from 'sweetalert2';
import { ToastrService } from 'ngx-toastr';

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
  toDeleteMapping: MappingFileIndex;

  modalInfo = {
    'title':'Asociación de Campos (Mapeo)',
    'body':'body'
  };

  constructor(
    private mappingFileService: MappingFilesService,
    private modalService: NgbModal,
    private toastr: ToastrService,
  ) { }

  ngOnInit() {
    this.getMappingInfo();

  }

  filterChange(event: MatSelectChange){
    this.sourceFilter = event.value;
    this.getMappingInfo();
  }

  getMappingInfo(){

    this.mappingFileService.index(this.sourceFilter).subscribe(
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
    }
    if(action.type === 'edit'){
      this.editMapping = action.map;
      this.selectedIndex = 2;
    }
    if(action.type === 'delete'){
      this.toDeleteMapping = action.map;
      Swal.fire({
        title: 'Confirmación',
        text: `¿Desea eliminar el Mapeo ${action.map.description}?`,
        showConfirmButton: true,
        confirmButtonAriaLabel: 'Eliminar',
        confirmButtonText: 'Eliminar',
        confirmButtonColor: '#d33',
        showCancelButton: true,
        cancelButtonAriaLabel: 'Cancelar',
        cancelButtonText: 'Cancelar',
        cancelButtonColor: '#3085d6'
      }).then((result: SweetAlertResult) => {
        if(result.value){
          this.deleteMapping(this.toDeleteMapping);
        }else{
          this.toDeleteMapping = null;
        }
      });
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

  deleteMapping(mapping: MappingFileIndex){
    this.toDeleteMapping = null;

    this.mappingFileService.deleteMap(mapping.id).subscribe(
      (_) => {
        this.toastr.info('Mapeo Eliminado');
    
        this.getMappingInfo();
      },
      (err) => {
        console.error(err)
      },
    );
  }
}
