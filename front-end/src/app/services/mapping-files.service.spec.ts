import { TestBed } from '@angular/core/testing';

import { MappingFilesService } from './mapping-files.service';

describe('MappingFilesService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: MappingFilesService = TestBed.get(MappingFilesService);
    expect(service).toBeTruthy();
  });
});
