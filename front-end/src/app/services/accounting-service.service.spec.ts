import { TestBed } from '@angular/core/testing';

import { AccountingServiceService } from './accounting.service';

describe('AccountingServiceService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: AccountingServiceService = TestBed.get(AccountingServiceService);
    expect(service).toBeTruthy();
  });
});
