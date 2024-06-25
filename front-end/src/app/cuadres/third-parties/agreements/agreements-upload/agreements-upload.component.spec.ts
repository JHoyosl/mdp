import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AgreementsUploadComponent } from './agreements-upload.component';

describe('AgreementsUploadComponent', () => {
  let component: AgreementsUploadComponent;
  let fixture: ComponentFixture<AgreementsUploadComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AgreementsUploadComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AgreementsUploadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
