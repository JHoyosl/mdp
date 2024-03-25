import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReconciliationHisotryComponent } from './reconciliation-hisotry.component';

describe('ReconciliationHisotryComponent', () => {
  let component: ReconciliationHisotryComponent;
  let fixture: ComponentFixture<ReconciliationHisotryComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReconciliationHisotryComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReconciliationHisotryComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
