import { Component, ElementRef, EventEmitter, OnInit, Output, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { MappingFileConstants } from 'src/app/constants/maapingFileConstants';
import { BankRequestsService } from 'src/app/services/bank/bank-requests.service';
import { MappingFilesService } from 'src/app/services/mapping-files.service';
import { ToastrService } from 'ngx-toastr';
import { zip } from 'rxjs';
import { MappingIndex, StoreMappingRequest } from 'src/app/Interfaces/mapping-file.interface';

@Component({
  selector: 'app-add-mapping',
  templateUrl: './add-mapping.component.html',
  styleUrls: ['./add-mapping.component.css']
})
export class AddMappingComponent implements OnInit {

  @Output() mappingResult = new EventEmitter<boolean>();

  @ViewChild('fileInput') fileInput: ElementRef;
  
  selectedType: string;
  separator: string;
  dateFormat: string;
  fileName = '';
  showBank = false;
  disabledButtons = false;


  bankList: Bank[];

  mappingDateFormat = MappingFileConstants.DATE_FORMAT;
  mappingIndex: MappingIndex[] = [];
  mappingArray: any;

  formFileMapping: FormGroup = new FormGroup({});
  formMapping: FormGroup = new FormGroup({});

  constructor(
    private fb: FormBuilder, 
    private bankRequestsService: BankRequestsService,
    private mappingFilesService: MappingFilesService,
    private toastr: ToastrService
  ) { 
    
  }

  ngOnInit() {
    this.setFormFileMapping();
    this.getBankList();
  }

  getBankList(){
    this.bankRequestsService.index().subscribe(
      (response) => {
        const sortByName = response.sort((a,b) => a.name < b.name ? -1 : 1) 
        this.bankList = sortByName
      },
      (err) => console.log(err)
    );
  }

  openFileUpload(event: Event){
    event.stopPropagation();
    this.fileInput.nativeElement.click();
  }

  onFileChange(event: Event){
    const file = (event.target as HTMLInputElement).files[0];
    this.fileName = file.name;
    this.formFileMapping.patchValue({ file: file});
  }

  setFormMapping(mappingArray: MappingIndex[]):void{
    mappingArray.forEach((_, index) =>{
      this.formMapping.addControl(index.toString(), new FormControl(''));
    });
    console.log(this.formMapping);
  }

  setFormFileMapping():void{
    this.formFileMapping = this.fb.group({
      type: ['', [Validators.required]],
      description: ['', [Validators.required]],
      separator: ['', [Validators.required]],
      dateFormat: ['', [Validators.required]],
      skipTop: ['0',[Validators.required]],
      skipBottom: ['0', [Validators.required]],
      file: [null, [Validators.required]],
    });

    this.formFileMapping.get('type').valueChanges.subscribe(
      (value) => {
        this.showBank = value === 'external' ? true : false;
        if(!this.showBank){
          this.formFileMapping.removeControl('bank');
        }else{
          this.formFileMapping.addControl('bank', new FormControl('',[Validators.required]));
        }
      });
  }

  submitMapping(){
    const mapped = [];
    this.mappingArray.forEach((element, index) => {
      mapped.push({
        fileColumn: index,
        mapIndex: this.formMapping.get(index.toString()).value,
        value: element.value,
        header: element.description,
      })
    });
    const storeRequest: StoreMappingRequest = {
      type: this.formFileMapping.get('type').value,
      description: this.formFileMapping.get('description').value,
      dateFormat: this.formFileMapping.get('dateFormat').value,
      separator: this.formFileMapping.get('separator').value,
      skipTop: this.formFileMapping.get('skipTop').value,
      skipBottom: this.formFileMapping.get('skipBottom').value,
      map: mapped,
      base: this.mappingArray
    };
    if(storeRequest.type === 'external'){
      storeRequest.bankId = this.formFileMapping.get('bank').value;
    }
    // TODO: validar campos obligatorios y que no hayan repetidos
    this.mappingFilesService.store(storeRequest).subscribe(
      (response) => {
        this.mappingResult.emit(true);
      },
      (err) => console.error(err)
    );
  }

  submitFile(){
    if(this.formFileMapping.invalid){
      this.toastr.error('Error en el formulario');
      return;
    }

    const file = this.formFileMapping.get('file').value;
    const skipTop = this.formFileMapping.get('skipTop').value;
    const type = this.formFileMapping.get('type').value;

    zip(
      this.mappingFilesService.mappingFileToArray(skipTop, file),
      this.mappingFilesService.getMapIndex(type)
    ).subscribe(
      (response) => {
        this.disabledButtons = !this.disabledButtons;
        this.formFileMapping.disable();
        this.mappingIndex = response[1].sort((a:MappingIndex,b:MappingIndex) => {
          return a.description < b.description ? -1 : 1;
        });
        this.mappingArray = this.pairMappingArray(response[0]);
        this.setFormMapping(this.mappingArray);
      },
      (err) => console.error(err)
    );

    console.log(file, skipTop);
  }

  pairMappingArray(data: string[][]){
    return data[0].map((item, index) => {
      return {
        description: item,
        value: data[1][index],
      }
    })
    
  }

  cancelMapping(){
    this.formFileMapping.enable();
    this.disabledButtons = !this.disabledButtons;
    this.mappingArray = [];
    this.formMapping = new FormGroup({});
  }

  backStep(){
    this.mappingResult.emit(false);
    this.setFormFileMapping();
    this.formMapping.reset();
    this.disabledButtons = false;
  }
}

