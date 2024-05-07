import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DetailMappingComponent } from './detail-mapping.component';

describe('DetailMappingComponent', () => {
  let component: DetailMappingComponent;
  let fixture: ComponentFixture<DetailMappingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DetailMappingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DetailMappingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
