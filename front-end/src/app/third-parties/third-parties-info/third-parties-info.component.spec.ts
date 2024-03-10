import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ThirdPartiesInfoComponent } from './third-parties-info.component';

describe('ThirdPartiesInfoComponent', () => {
  let component: ThirdPartiesInfoComponent;
  let fixture: ComponentFixture<ThirdPartiesInfoComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ThirdPartiesInfoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ThirdPartiesInfoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
