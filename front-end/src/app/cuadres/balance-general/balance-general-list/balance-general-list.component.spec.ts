import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BalanceGeneralListComponent } from './balance-general-list.component';

describe('BalanceGeneralListComponent', () => {
  let component: BalanceGeneralListComponent;
  let fixture: ComponentFixture<BalanceGeneralListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BalanceGeneralListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BalanceGeneralListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
