import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddTxTypeComponent } from './add-tx-type.component';

describe('AddTxTypeComponent', () => {
  let component: AddTxTypeComponent;
  let fixture: ComponentFixture<AddTxTypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddTxTypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddTxTypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
