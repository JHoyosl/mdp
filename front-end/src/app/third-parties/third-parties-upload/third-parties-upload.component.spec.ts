import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ThirdPartiesUploadComponent } from './third-parties-upload.component';

describe('ThirdPartiesUploadComponent', () => {
  let component: ThirdPartiesUploadComponent;
  let fixture: ComponentFixture<ThirdPartiesUploadComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ThirdPartiesUploadComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ThirdPartiesUploadComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
