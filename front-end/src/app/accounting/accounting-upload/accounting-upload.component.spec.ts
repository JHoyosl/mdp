import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountingUploadComponent } from './accounting-upload.component';

describe('AccountingUploadComponent', () => {
  let component: AccountingUploadComponent;
  let fixture: ComponentFixture<AccountingUploadComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccountingUploadComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountingUploadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
