import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { MappingFileIndex } from 'src/app/Interfaces/mapping-file.interface';
import { MappingFilesService } from 'src/app/services/mapping-files.service';

@Component({
  selector: 'app-file-mapping',
  templateUrl: './file-mapping.component.html',
  styleUrls: ['./file-mapping.component.css']
})
export class FileMappingComponent implements OnInit {

  selectedIndex = 1;
  mappingInfo: MappingFileIndex[];
  formMapping: FormGroup = new FormGroup({});

  constructor(private mappingFileService: MappingFilesService ) { }

  ngOnInit() {
    this.getMappingInfo();
  }

  getMappingInfo(){
    this.mappingFileService.index('all').subscribe(
      (response) => {
        console.log(response);
        this.mappingInfo = response
      },
      (err) => console.error(err)
    );
  }

  initForm(){
    this.formMapping = new FormGroup({
      type: new FormControl(['', [Validators.required]]),
      bankId: new FormControl(['', [Validators.required]]),
      file: new FormControl([null, [Validators.required]]),
      description: new FormControl(['', [Validators.required]]),
    });
  }
}
