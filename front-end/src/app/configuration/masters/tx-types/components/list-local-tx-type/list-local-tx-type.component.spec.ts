import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListLocalTxTypeComponent } from './list-local-tx-type.component';

describe('ListLocalTxTypeComponent', () => {
  let component: ListLocalTxTypeComponent;
  let fixture: ComponentFixture<ListLocalTxTypeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListLocalTxTypeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListLocalTxTypeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
