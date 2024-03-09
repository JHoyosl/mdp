import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountingInfoComponent } from './accounting-info.component';

describe('AccountingInfoComponent', () => {
  let component: AccountingInfoComponent;
  let fixture: ComponentFixture<AccountingInfoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccountingInfoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountingInfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
