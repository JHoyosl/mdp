import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ListMappingComponent } from './list-mapping.component';

describe('ListMappingComponent', () => {
  let component: ListMappingComponent;
  let fixture: ComponentFixture<ListMappingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ListMappingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ListMappingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
