import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ReconciliationBalanceComponent } from './reconciliation-balance.component';

describe('ReconciliationBalanceComponent', () => {
  let component: ReconciliationBalanceComponent;
  let fixture: ComponentFixture<ReconciliationBalanceComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ReconciliationBalanceComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ReconciliationBalanceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
