import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountBalanceTableComponent } from './account-balance-table.component';

describe('AccountBalanceTableComponent', () => {
  let component: AccountBalanceTableComponent;
  let fixture: ComponentFixture<AccountBalanceTableComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccountBalanceTableComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountBalanceTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
