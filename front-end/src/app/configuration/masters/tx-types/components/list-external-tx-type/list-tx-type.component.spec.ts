import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListTxTypeComponent } from './list-external-tx-type.component';

describe('ListTxTypeComponent', () => {
  let component: ListTxTypeComponent;
  let fixture: ComponentFixture<ListTxTypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListTxTypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListTxTypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
