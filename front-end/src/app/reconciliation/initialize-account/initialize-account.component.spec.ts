import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InitializeAccountComponent } from './initialize-account.component';

describe('InitializeAccountComponent', () => {
  let component: InitializeAccountComponent;
  let fixture: ComponentFixture<InitializeAccountComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InitializeAccountComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InitializeAccountComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
