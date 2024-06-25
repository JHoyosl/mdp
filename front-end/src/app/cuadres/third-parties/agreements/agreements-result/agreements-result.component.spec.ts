import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AgreementsResultComponent } from './agreements-result.component';

describe('AgreementsResultComponent', () => {
  let component: AgreementsResultComponent;
  let fixture: ComponentFixture<AgreementsResultComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AgreementsResultComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AgreementsResultComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
