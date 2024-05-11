import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TxTypesComponent } from './tx-types.component';

describe('TxTypesComponent', () => {
  let component: TxTypesComponent;
  let fixture: ComponentFixture<TxTypesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TxTypesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TxTypesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
