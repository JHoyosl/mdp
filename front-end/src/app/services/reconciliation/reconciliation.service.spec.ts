import { TestBed } from '@angular/core/testing';

import { ReconciliationService } from './reconciliation.service';

describe('ReconciliationService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: ReconciliationService = TestBed.get(ReconciliationService);
    expect(service).toBeTruthy();
  });
});
