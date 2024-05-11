import { TestBed } from '@angular/core/testing';

import { TxTypeService } from './tx-type.service';

describe('TxTypeService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: TxTypeService = TestBed.get(TxTypeService);
    expect(service).toBeTruthy();
  });
});
