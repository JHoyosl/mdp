import { TestBed } from '@angular/core/testing';

import { ReconciliationProcessService } from './reconciliation-process.service';

describe('ReconciliationProcessService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ReconciliationProcessService = TestBed.get(ReconciliationProcessService);
    expect(service).toBeTruthy();
  });
});
