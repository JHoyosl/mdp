import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ConciliarComponent } from './reconciliation.component';

describe('ConciliarComponent', () => {
  let component: ConciliarComponent;
  let fixture: ComponentFixture<ConciliarComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ConciliarComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ConciliarComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
