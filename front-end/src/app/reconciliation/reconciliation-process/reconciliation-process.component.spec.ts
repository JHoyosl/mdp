import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReconciliationProcessComponent } from './reconciliation-process.component';

describe('ReconciliationProcessComponent', () => {
  let component: ReconciliationProcessComponent;
  let fixture: ComponentFixture<ReconciliationProcessComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReconciliationProcessComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReconciliationProcessComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
