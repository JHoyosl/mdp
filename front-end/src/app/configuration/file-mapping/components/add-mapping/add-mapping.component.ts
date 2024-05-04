import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { MappingFileConstants } from 'src/app/constants/maapingFileConstants';
import { BankRequestsService } from 'src/app/services/bank/bank-requests.service';
import { MappingFilesService } from 'src/app/services/mapping-files.service';
import { ToastrService } from 'ngx-toastr';
import { mergeMap } from 'rxjs/operators';
import { forkJoin, zip } from 'rxjs';

@Component({
  selector: 'app-add-mapping',
  templateUrl: './add-mapping.component.html',
  styleUrls: ['./add-mapping.component.css']
})
export class AddMappingComponent implements OnInit {

  @ViewChild('fileInput') fileInput: ElementRef;
  
  selectedType: string;
  separator: string;
  dateFormat: string;
  fileName = '';
  showBank = false;
  
  bankList: Bank[];

  mappingDateFormat = MappingFileConstants.DATE_FORMAT;
  
  formMapping: FormGroup = new FormGroup({});

  constructor(
    private fb: FormBuilder, 
    private bankRequestsService: BankRequestsService,
    private mappingFilesService: MappingFilesService,
    private toastr: ToastrService
  ) { 
    
  }

  ngOnInit() {
    this.setForm();
    this.getBankList();
    console.log(mapp);
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
    this.formMapping.patchValue({ file: file});
  }

  setForm():void{
    this.formMapping = this.fb.group({
      type: ['', [Validators.required]],
      bank: [''],
      description: ['Pruebas cargues', [Validators.required]],
      separator: ['', [Validators.required]],
      dateFormat: ['', [Validators.required]],
      skipTop: ['', [Validators.required]],
      skipBottom: ['', [Validators.required]],
      file: [null, [Validators.required]],
    });

    this.formMapping.get('type').valueChanges.subscribe(
      (value) => {
        this.showBank = value === 'externo' ? true : false;
        if(!this.showBank){
          this.formMapping.get('bank').patchValue('');
        }
      });
  }

  submitFile(){
    if(this.formMapping.invalid){
      this.toastr.error('Error en el formulario');
      return;
    }

    const file = this.formMapping.get('file').value;
    const skipTop = this.formMapping.get('skipTop').value;
    const type = this.formMapping.get('type').value;

    zip(
      this.mappingFilesService.uploadMappingFile(skipTop, file),
      this.mappingFilesService.getMapIndex(type)
    ).subscribe(
      (response) => console.log(response),
      (err) => console.error(err)
    );

    console.log(file, skipTop);
  }
}

export const mapp = [
  [
      "Fecha Movimiento",
      "Código Transacción",
      "Nombre Transacción",
      "Documento",
      "Débitos",
      "Créditos",
      "Referencia1",
      "Referencia2",
      "Valor Efectivo",
      "Valor Cheque"
  ],
  [
      "2020/08/04",
      "5503",
      "IVA",
      "0000000",
      "$2.679,00",
      "$0,00",
      "-",
      "-",
      "$0,00",
      "$0,00"
  ]
];
