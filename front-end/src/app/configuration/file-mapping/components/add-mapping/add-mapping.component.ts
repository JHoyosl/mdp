import { Component, ElementRef, EventEmitter, OnInit, Output, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { MappingFileConstants } from 'src/app/constants/maapingFileConstants';
import { BankRequestsService } from 'src/app/services/bank/bank-requests.service';
import { MappingFilesService } from 'src/app/services/mappingFiles/mapping-files.service';
import { ToastrService } from 'ngx-toastr';
import { zip } from 'rxjs';
import { MappingIndex, StoreMappingRequest, Map } from 'src/app/Interfaces/mapping-file.interface';
import Swal from 'sweetalert2';

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
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.bankRequestsService.index().subscribe(
      (response) => {
        const sortByName = response.sort((a,b) => a.name < b.name ? -1 : 1) 
        this.bankList = sortByName
      },
      (err) => console.error(err),
      () => Swal.close()
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
    const mapped: Map[] = [];
    const mappingIdCheck = [];
    this.mappingArray.forEach((element, index) => {
      mapped.push({
        fileColumn: index,
        mapIndex: this.formMapping.get(index.toString()).value,
        value: element.value,
        header: element.description,
      });
      mappingIdCheck.push(this.formMapping.get(index.toString()).value);
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

  // check for mandatory mapping
  let hasError = false;
   this.mappingIndex
    .filter((item) => item.type.toString() === '1')
    .forEach((item) => {
      if(!mappingIdCheck.includes(item.id)){
        this.toastr.error(`${item.description} es Obligatorio`);
        hasError = true;
      }
    })
    if(hasError){
      return;
    }
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    this.mappingFilesService.store(storeRequest).subscribe(
      (response) => {
        this.mappingResult.emit(true);
      },
      (err) => console.error(err),
      () => Swal.close()
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
    Swal.fire({
      title: 'Procesando',
      allowOutsideClick: false,
      showConfirmButton: false,
      imageUrl: 'assets/images/2.gif',

    });
    zip(
      this.mappingFilesService.mappingFileToArray(skipTop, file),
      this.mappingFilesService.getMapIndex(type)
    ).subscribe(
      (response) => {
        this.disabledButtons = !this.disabledButtons;
        this.formFileMapping.disable();
        this.mappingIndex = response[1]
          .sort((a:MappingIndex,b:MappingIndex) => a.description < b.description ? -1 : 1);

        this.mappingArray = this.pairMappingArray(response[0]);
        this.setFormMapping(this.mappingArray);
      },
      (err) => console.error(err),
      () => Swal.close()
    );
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

