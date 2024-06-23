import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CuadresComponent } from './cuadres.component';

describe('CuadresComponent', () => {
  let component: CuadresComponent;
  let fixture: ComponentFixture<CuadresComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CuadresComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CuadresComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
