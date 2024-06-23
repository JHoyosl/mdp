import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OperationalMasterComponent } from './operational-master.component';

describe('OperationalMasterComponent', () => {
  let component: OperationalMasterComponent;
  let fixture: ComponentFixture<OperationalMasterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OperationalMasterComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OperationalMasterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
