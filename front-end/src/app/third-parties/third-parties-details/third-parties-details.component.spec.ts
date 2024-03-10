import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ThirdPartiesDetailsComponent } from './third-parties-details.component';

describe('ThirdPartiesDetailsComponent', () => {
  let component: ThirdPartiesDetailsComponent;
  let fixture: ComponentFixture<ThirdPartiesDetailsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ThirdPartiesDetailsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ThirdPartiesDetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
