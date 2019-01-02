import { HttpClientModule } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';

import { DownloadCenterService } from './download-center.service';

describe('DownloadCenterService', () => {
  beforeEach(() => TestBed.configureTestingModule({
    imports: [HttpClientModule]
  }));

  it('should be created', () => {
    const service: DownloadCenterService = TestBed.get(DownloadCenterService);
    expect(service).toBeTruthy();
  });
});
