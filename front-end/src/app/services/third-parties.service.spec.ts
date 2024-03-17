import { TestBed } from '@angular/core/testing';

import { ThirdPartiesService } from './third-parties.service';

describe('ThirdPartiesService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ThirdPartiesService = TestBed.get(ThirdPartiesService);
    expect(service).toBeTruthy();
  });
});
