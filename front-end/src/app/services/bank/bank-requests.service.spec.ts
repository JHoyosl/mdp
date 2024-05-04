import { TestBed } from '@angular/core/testing';

import { BankRequestsService } from './bank-requests.service';

describe('BankRequestsService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: BankRequestsService = TestBed.get(BankRequestsService);
    expect(service).toBeTruthy();
  });
});
