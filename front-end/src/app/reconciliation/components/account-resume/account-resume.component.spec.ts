import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccountResumeComponent } from './account-resume.component';

describe('AccountResumeComponent', () => {
  let component: AccountResumeComponent;
  let fixture: ComponentFixture<AccountResumeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccountResumeComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccountResumeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
