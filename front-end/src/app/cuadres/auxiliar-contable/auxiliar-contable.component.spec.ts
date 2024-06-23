import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AuxiliarContableComponent } from './auxiliar-contable.component';

describe('AuxiliarContableComponent', () => {
  let component: AuxiliarContableComponent;
  let fixture: ComponentFixture<AuxiliarContableComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AuxiliarContableComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AuxiliarContableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
