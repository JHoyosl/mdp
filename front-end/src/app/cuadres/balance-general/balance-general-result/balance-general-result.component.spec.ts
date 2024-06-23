import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BalanceGeneralResultComponent } from './balance-general-result.component';

describe('BalanceGeneralResultComponent', () => {
  let component: BalanceGeneralResultComponent;
  let fixture: ComponentFixture<BalanceGeneralResultComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BalanceGeneralResultComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BalanceGeneralResultComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
