import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { zip } from 'rxjs';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { Map, MappingFileIndex, MappingIndex, updateMappingRequest } from 'src/app/Interfaces/mapping-file.interface';
import { MappingFileConstants } from 'src/app/constants/maapingFileConstants';
import { BankRequestsService } from 'src/app/services/bank/bank-requests.service';
import { MappingFilesService } from 'src/app/services/mappingFiles/mapping-files.service';

import Swal from 'sweetalert2';

@Component({
  selector: 'app-edit-mapping',
  templateUrl: './edit-mapping.component.html',
  styleUrls: ['./edit-mapping.component.css']
})
export class EditMappingComponent implements OnInit {

  @Input() mapping: MappingFileIndex;
  @Output() action = new EventEmitter<string>();

  formEditMapping: FormGroup = new FormGroup({});
  formMap: FormGroup = new FormGroup({});

  baseMap: {description: string, value: string}[];
  bankList: Bank[];
  mappingIndex: MappingIndex[] = [];
  showBank = false;
  mappingDateFormat = MappingFileConstants.DATE_FORMAT;
  
  constructor(
    private fb: FormBuilder, 
    private bankRequestsService: BankRequestsService,
    private mappingFilesService: MappingFilesService,
  ) { 
    
  }

  ngOnInit() {
    this.setForm(this.mapping);
    this.baseMap = this.mapping.base;
    this.setFormMap(this.mapping.map);
    this.getData(this.mapping.type);
  }

  getData(mapType: string){
    const type = mapType === 'conciliar_externo' 
      ? 'external'
      : 'internal';
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    zip(
      this.bankRequestsService.index(),
      this.mappingFilesService.getMapIndex(type)
    ).subscribe(
      (response) => {
        Swal.close();
        const sortByName = response[0].sort((a,b) => a.name < b.name ? -1 : 1) 
        this.bankList = sortByName;
        this.mappingIndex = response[1];
      },
      (err) => {
        console.error(err);
        Swal.close();
      }
    );
   
  }
  setFormMap(map: Map[]){
    map.forEach((item,  index) => {
      this.formMap.addControl(
        index.toString(), 
        new FormControl(item.mapIndex)
      )
    })
  }
  setForm(mapping: MappingFileIndex){
    const type = mapping.type === 'conciliar_externo' 
      ? 'external'
      : 'internal';
    this.formEditMapping = this.fb.group({
      type: [{value:type, disabled: true}, [Validators.required]],
      description: [{value:mapping.description, disabled: true}, [Validators.required]],
      separator: [mapping.separator, [Validators.required]],
      dateFormat: [mapping.dateFormat, [Validators.required]],
      skipTop: [mapping.skipTop,[Validators.required]],
      skipBottom: [mapping.skipBottom, [Validators.required]],
    });

    this.showBank = type === 'external' ? true : false;
    if(type === 'external'){
      this.formEditMapping.addControl(
        'bank', 
        new FormControl({value: this.mapping.bank.id, disabled: true},[Validators.required])
      );
    }
  }

  submitEdit(){
    // this.action.emit('success');
    const mapped: Map[] = [];
    this.baseMap.forEach((element, index) => {
      if(this.formMap.get(index.toString()).value !== ""){
        mapped.push({
          fileColumn: index,
          mapIndex: this.formMap.get(index.toString()).value,
          value: element.value,
          header: element.description,
        });
      }
    });
    const data: updateMappingRequest = {
      id: this.mapping.id,
      description: this.formEditMapping.get('description').value,
      dateFormat: this.formEditMapping.get('dateFormat').value,
      separator: this.formEditMapping.get('separator').value,
      skipTop: this.formEditMapping.get('skipTop').value,
      skipBottom: this.formEditMapping.get('skipBottom').value,
      map: mapped,
    }

    this.mappingFilesService.patchMap(data).subscribe(
      (response) => {
        console.log(response);
      },
      (err) => console.log(err)
    );
  }

  cancel(){
    this.action.emit('cancel');
  }


}
