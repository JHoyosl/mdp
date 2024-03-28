import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BeginProcessComponent } from './begin-process.component';

describe('BeginProcessComponent', () => {
  let component: BeginProcessComponent;
  let fixture: ComponentFixture<BeginProcessComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BeginProcessComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BeginProcessComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
