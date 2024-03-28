import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SetBalanceComponent } from './set-balance.component';

describe('SetBalanceComponent', () => {
  let component: SetBalanceComponent;
  let fixture: ComponentFixture<SetBalanceComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SetBalanceComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SetBalanceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
