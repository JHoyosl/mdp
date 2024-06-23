import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { OperaionalListComponent } from './operaional-list.component';

describe('OperaionalListComponent', () => {
  let component: OperaionalListComponent;
  let fixture: ComponentFixture<OperaionalListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ OperaionalListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(OperaionalListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
