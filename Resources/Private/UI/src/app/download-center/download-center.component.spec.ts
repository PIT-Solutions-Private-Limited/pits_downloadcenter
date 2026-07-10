import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { of } from 'rxjs';

import { DownloadCenterComponent } from './download-center.component';
import { DownloadCenterService } from './download-center.service';

// NOTE: the service is mocked with a synchronous observable so the full
// template (table + pagination) renders in the first change detection pass;
// assertions after later detectChanges() calls are avoided. This keeps the
// specs robust independent of zone/scheduler behavior (Angular 22.0.x had a
// zone-CD regression where ticks did not refresh non-dirty views,
// see https://github.com/angular/angular/issues/69530).
const mockData = {
  config: { paginationcount: 10 },
  translations: { resultsfound: 'results found' },
  categories: [],
  types: [],
  files: [
    {
      id: 1,
      title: 'Manual',
      size: '1 MB',
      fileType: 'PDF',
      extension: 'pdf',
      dataType: [''],
      categories: [''],
      downloadUrl: '#'
    },
    {
      id: 2,
      title: 'Datasheet',
      size: '2 MB',
      fileType: 'PDF',
      extension: 'pdf',
      dataType: [''],
      categories: [''],
      downloadUrl: '#'
    }
  ]
};

describe('DownloadCenterComponent', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DownloadCenterComponent],
      providers: [
        provideRouter([]),
        {
          provide: DownloadCenterService,
          useValue: { getData: () => of(JSON.parse(JSON.stringify(mockData))) }
        }
      ]
    }).compileComponents();
  });

  it('should create, load data and render the file list', () => {
    const fixture = TestBed.createComponent(DownloadCenterComponent);
    fixture.detectChanges();

    const component = fixture.componentInstance;
    expect(component.loading).toBe(false);
    expect(component.listData.files.length).toBe(2);
    expect(component.config.itemsPerPage).toBe(10);

    const el = fixture.nativeElement as HTMLElement;
    expect(el.querySelector('#total-count')?.textContent).toContain('2');
    expect(el.querySelectorAll('#data-results tbody tr').length).toBe(2);
    expect(el.querySelector('#data-results')?.textContent).toContain('Manual');
  });

  it('should filter the file list by keyword', () => {
    const fixture = TestBed.createComponent(DownloadCenterComponent);
    fixture.detectChanges();

    const component = fixture.componentInstance;
    component.filterFormGroup.patchValue({ keyword_search: 'datasheet' });
    expect(component.listData.files.length).toBe(1);
    expect(component.listData.files[0].title).toBe('Datasheet');
  });

  it('should clamp the current page when filtering shrinks the list', () => {
    const fixture = TestBed.createComponent(DownloadCenterComponent);
    fixture.detectChanges();

    const component = fixture.componentInstance;
    component.config.itemsPerPage = 1; // 2 files -> 2 pages
    component.onPageChange(2);
    expect(component.config.currentPage).toBe(2);

    component.filterFormGroup.patchValue({ keyword_search: 'datasheet' }); // 1 result -> 1 page
    expect(component.listData.files.length).toBe(1);
    expect(component.config.currentPage).toBe(1);
    expect(component.filterConfig.cPage).toBe('1');
  });

  it('should sort the file list by title', () => {
    const fixture = TestBed.createComponent(DownloadCenterComponent);
    fixture.detectChanges();

    const component = fixture.componentInstance;
    component.sortFileList('title');
    expect(component.listData.files.map((f: any) => f.title)).toEqual(['Datasheet', 'Manual']);
    component.sortFileList('title');
    expect(component.listData.files.map((f: any) => f.title)).toEqual(['Manual', 'Datasheet']);
  });
});
