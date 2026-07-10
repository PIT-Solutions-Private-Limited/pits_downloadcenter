import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormGroup, FormBuilder, FormArray, ReactiveFormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { NgxPaginationModule, PaginationInstance } from 'ngx-pagination';

import { DownloadCenterService } from './download-center.service';

import { Subject } from 'rxjs';
import { takeUntil, take } from 'rxjs/operators';

interface FilterConfig {
  keyword_search: string;
  category: any[];
  file_types: string[];
  cPage: string;
}

@Component({
  selector: 'app-download-center',
  imports: [CommonModule, ReactiveFormsModule, NgxPaginationModule],
  templateUrl: './download-center.component.html'
})
export class DownloadCenterComponent implements OnInit, OnDestroy {
  public baseURL: any = document.getElementById('baseURL');
  public loaderimageuri: any = document.getElementById('loaderimageuri');
  public orderByField = '';
  public reverseSort = true;
  public listData: any = {};
  public loading = false;
  public filterFormGroup!: FormGroup;
  public filterConfig: FilterConfig = { keyword_search: '', category: [], file_types: [], cPage: '1' };
  public config: PaginationInstance = { id: 'custom', itemsPerPage: 10, currentPage: 1 };

  private _categoryList: any = {};
  private _initialListData = '[]';
  private _ids: any[] = [];
  private _traverseObj: any = {};

  private _unsubscribe$ = new Subject<void>();

  constructor(
    private _downloadCenterService: DownloadCenterService,
    private _router: Router,
    private _activatedRoute: ActivatedRoute,
    private _fb: FormBuilder
  ) { }

  ngOnInit() {
    this._initFilterFormGroup();
    this._fetchData();
    this._listenFilterFormValueChanges();
  }

  get categoryControls() {
    return (this.filterFormGroup.get('category') as FormArray).controls;
  }

  private _initFilterFormGroup(): void {
    this.filterFormGroup = this._fb.group({
      keyword_search: [''],
      category: this._fb.array([this._createCategoryGroup()])
    });
  }

  private _createCategoryGroup(): FormGroup {
    return this._fb.group({
      categoryId: ['']
    });
  }

  private _fetchData(): void {
    this.loading = true;
    this._downloadCenterService.getData()
      .pipe(takeUntil(this._unsubscribe$))
      .subscribe({
        next: (data: any) => {
          this.loading = false;
          this.listData = data;
          this._categoryList[0] = data['categories'];
          this._initialListData = JSON.stringify(this.listData);
          this.config.itemsPerPage = data['config']['paginationcount'];
          this._listenActivatedRoute();
        },
        error: (err) => {
          throw err;
        }
      });
  }

  private _listenActivatedRoute(): void {
    this._activatedRoute.queryParams
      .pipe(take(1))
      .subscribe((params) => {
        let categories: any[] = [];
        if (params['category']) {
          const categoryArray = params['category'].split(',');
          categories = categoryArray.map((id: any) => ({ categoryId: id }));
          categoryArray.forEach((id: any, i: number) => {
            this.onCategoryChange(id, i);
          });
        }
        params['cPage'] && (this.config.currentPage = this.filterConfig.cPage = params['cPage']);
        this.filterConfig.file_types = params['file_types'] ? params['file_types'].split(',') : [];
        this.filterConfig.category = categories;
        this.filterFormGroup.patchValue({
          keyword_search: params['keyword_search'] || '',
          category: categories
        });
      });
  }

  private _listenFilterFormValueChanges(): void {
    this.filterFormGroup.valueChanges
      .pipe(takeUntil(this._unsubscribe$))
      .subscribe((res) => {
        Object.assign(this.filterConfig, res);
        this._filterList(this.filterConfig);
      });
  }

  public onCategoryChange(id: any, index: number): void {
    const items = this.filterFormGroup.get('category') as FormArray;
    const itemControls = items.controls;
    while (itemControls.length !== (index + 1)) {
      itemControls.pop();
    }
    if (!id) {
      return itemControls[index].setValue({ categoryId: id });
    }
    const sub = this.getCategoryList(index).filter(data => data['id'] === +id)[0]['input'] || [];
    if (sub.length) {
      this._categoryList[index + 1] = sub;
      items.push(this._createCategoryGroup());
    }
  }

  private _filterList(config: FilterConfig): void {
    this._setRouting(config);
    const category = config['category'].filter(data => !!data['categoryId']).map(data => data['categoryId']);
    if (category.length) {
      this._ids = [];
      this._setTraverseObj(this.listData['categories'].filter((d: any) => d.id === +category[0])[0], +category[category.length - 1]);
      this._traverse(this._traverseObj);
    }
    this.listData['files'] = JSON.parse(this._initialListData)['files']
      .filter(this._keyWordFilter.bind(this))
      .filter(this._categoryFilter.bind(this))
      .filter(this._fileTypeFilter.bind(this));
    !!this.orderByField && this.sortFileList(this.orderByField, true);
    this._clampCurrentPage();
  }

  // Filtering can shrink the list below the current page (e.g. searching while
  // on page 4, or a deep link like ?cPage=4&keyword_search=...), which would
  // render an empty page while still announcing results. Clamp to the last
  // available page and reflect it in the URL.
  private _clampCurrentPage(): void {
    const total = (this.listData['files'] || []).length;
    const perPage = +this.config.itemsPerPage || 1;
    const lastPage = Math.max(1, Math.ceil(total / perPage));
    if (+this.config.currentPage > lastPage) {
      this.config.currentPage = lastPage;
      this.filterConfig.cPage = `${lastPage}`;
      this._setRouting(this.filterConfig);
    }
  }

  private _setRouting(filterConfig: FilterConfig): void {
    const config: any = Object.assign({}, filterConfig);
    config['category'] = config['category'].filter((data: any) => !!data['categoryId']).map((data: any) => data['categoryId']);
    const params: any = Object.assign({}, this._activatedRoute.snapshot.queryParams, config);
    Object.keys(params)
      .forEach((key) => {
        params[key] = key in config ? `${config[key]}` : params[key];
        !params[key] && delete params[key];
      });
    this._router.navigate([], { relativeTo: this._activatedRoute, queryParams: params });
  }

  private _setTraverseObj(obj: any, id: number): void {
    if (obj && obj['id'] === id) {
      this._traverseObj = obj;
    }
    Object.values(obj || {}).forEach((val) => {
      if (Array.isArray(val)) {
        val.forEach((el: any) => {
          if (el && typeof el === 'object') {
            (el['id'] === id) ? (this._traverseObj = el) : this._setTraverseObj(el, id);
          }
        });
      }
    });
  }

  private _traverse(obj: any): void {
    Object.entries(obj || {}).forEach(([key, val]) => {
      (key === 'id') && this._ids.push(val);
      if (Array.isArray(val)) {
        val.forEach((el: any) => {
          if (el && typeof el === 'object') {
            this._traverse(el);
          }
        });
      }
    });
  }

  private _keyWordFilter(data: any): boolean {
    const keyword_search = this.filterConfig.keyword_search;
    const searchKeys = ['title', ...(!+this.listData['config']['hideSizeColumn'] ? ['size'] : []), 'extension'];
    const searchString = searchKeys.map(key => data[key]).join('').toLowerCase();
    return !keyword_search || searchString.indexOf(keyword_search.toLowerCase().trim()) !== -1;
  }

  private _categoryFilter(data: any): boolean {
    const category = this.filterConfig.category.filter(res => !!res['categoryId']).map(res => res['categoryId']);
    if (category.length) {
      const dataCat = data['categories'].map((id: any) => +id);
      const categories = [...dataCat, ...this._ids];
      return Array.from(new Set(categories)).length !== categories.length;
    }
    return true;
  }

  private _fileTypeFilter(data: any): boolean {
    const fileTypes = this.filterConfig.file_types;
    const mergedTypes = [...fileTypes, ...data['dataType']];
    return !fileTypes.length || Array.from(new Set(mergedTypes)).length !== mergedTypes.length;
  }

  public patchFileTypes(id: string, checked: boolean): void {
    const types = this.filterConfig.file_types;
    const index = types.indexOf(id);
    (checked) ? (index === -1) && this.filterConfig.file_types.push(id) : this.filterConfig.file_types.splice(index, 1);
    this._filterList(this.filterConfig);
  }

  public resetFieldByType(field_type: string): void {
    const formValue = this.filterFormGroup.getRawValue();
    if (!formValue[field_type]) {
      return;
    }
    formValue[field_type] = '';
    this.filterFormGroup.patchValue(formValue);
  }

  public getCategoryList(i: number): any[] {
    return this._categoryList[i] || [];
  }

  public trackByFn(index: number, item: any): any {
    return item.id;
  }

  public sortFileList(order_by: string, skip?: boolean): void {
    this.orderByField = order_by;
    !skip && (this.reverseSort = !this.reverseSort);
    const sort_order = !this.reverseSort ? 'asc' : 'desc';
    this.listData['files'] = this._orderBy(this.listData['files'], order_by, sort_order);
  }

  private _orderBy(list: any[], key: string, order: 'asc' | 'desc'): any[] {
    const dir = order === 'asc' ? 1 : -1;
    return [...(list || [])].sort((a, b) => {
      const av = a && a[key];
      const bv = b && b[key];
      if (av === bv) {
        return 0;
      }
      return (av < bv ? -1 : 1) * dir;
    });
  }

  public onPageChange(page: number): void {
    this.filterConfig.cPage = page + '';
    this.config.currentPage = page;
    this._setRouting(this.filterConfig);
  }

  ngOnDestroy() {
    this._unsubscribe$.next();
    this._unsubscribe$.complete();
  }

}
