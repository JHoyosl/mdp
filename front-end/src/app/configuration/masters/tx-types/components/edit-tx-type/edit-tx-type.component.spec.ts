import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EditTxTypeComponent } from './edit-tx-type.component';

describe('EditTxTypeComponent', () => {
  let component: EditTxTypeComponent;
  let fixture: ComponentFixture<EditTxTypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EditTxTypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EditTxTypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
