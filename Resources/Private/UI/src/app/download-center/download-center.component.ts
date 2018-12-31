import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormGroup, FormBuilder } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { PaginationInstance } from 'ngx-pagination';

import { DownloadCenterService } from './download-center.service';

import { Subject } from 'rxjs';
import { takeUntil, take } from 'rxjs/operators';

import * as _ from 'lodash';

interface FilterConfig {
  keyword_search: string;
  category: string;
  sub_category: string;
  file_types: string[];
  cPage: string;
}

@Component({
  selector: 'app-download-center',
  templateUrl: './download-center.component.html'
})
export class DownloadCenterComponent implements OnInit, OnDestroy {
  public orderByField = '';
  public reverseSort = true;
  public listData = {};
  public initialListData = '[]';
  public subCategories = [];
  public loading = false;
  public filterFormGroup: FormGroup;
  public filterConfig: FilterConfig = { keyword_search: '', category: '', sub_category: '', file_types: [], cPage: '1' };
  public config: PaginationInstance = { id: 'custom', itemsPerPage: 10, currentPage: 1 };

  private _unsubscribe$ = new Subject();

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

  private _initFilterFormGroup(): void {
    this.filterFormGroup = this._fb.group({
      keyword_search: [''],
      category: [''],
      sub_category: ['']
    });
  }

  private _fetchData(): void {
    this.loading = true;
    this._downloadCenterService.getData()
      .pipe(takeUntil(this._unsubscribe$))
      .subscribe((data) => {
        this.loading = false;
        this.listData = data;
        this.initialListData = JSON.stringify(data);
        this.config.itemsPerPage = data['config']['paginationcount'];
        this._listenActivatedRoute();
      }, err => {
        throw err;
      });
  }

  private _listenActivatedRoute(): void {
    this._activatedRoute.queryParams
      .pipe(take(1))
      .subscribe((params) => {
        params['cPage'] && (this.config.currentPage = this.filterConfig.cPage = params['cPage']);
        params['category'] && this.fetchSubCategory(params['category']);
        this.filterConfig.file_types = params['file_types'] ? params['file_types'].split(',') : [];
        this.filterFormGroup.patchValue(params);
      });
  }

  private _listenFilterFormValueChanges() {
    this.filterFormGroup.valueChanges
      .pipe(takeUntil(this._unsubscribe$))
      .subscribe((res) => {
        Object.assign(this.filterConfig, res);
        this._filterList(this.filterConfig);
      });
  }

  private _filterList(config: FilterConfig): void {
    this._setRouting(config);
    this.listData['files'] = JSON.parse(this.initialListData)['files'];
    this.listData['files'] = this.listData['files']
      .filter(this._keyWordFilter.bind(this))
      .filter(this._categoryFilter.bind(this))
      .filter(this._subCategoryFilter.bind(this))
      .filter(this._fileTypeFilter.bind(this));
  }

  private _setRouting(config: FilterConfig): void {
    const queryParams = _.join(Object.keys(config).filter((key) => !!config[key].length).map((key) => `${key}=${config[key]}`), '&&');
    this._router.navigateByUrl(`/?${queryParams}`);
  }

  private _keyWordFilter(data: object) {
    const keyword_search = this.filterConfig.keyword_search;
    const searchString = (data['title'] + data['size'] + data['extension']).toLowerCase();
    return !keyword_search || searchString.indexOf(keyword_search.toLowerCase().trim()) !== -1;
  }

  private _categoryFilter(data: object) {
    const categoryId = this.filterConfig.category;
    const subCategories = this.fetchSubCategory(+categoryId, true).map(sub => `${sub['id']}`);
    const categories = [categoryId, ...subCategories];
    return !categoryId || categories.indexOf(data['categories'][0]) !== -1;
  }

  private _fileTypeFilter(data: object) {
    const fileTypes = this.filterConfig.file_types;
    const mergedTypes = [...fileTypes, ...data['dataType']];
    return !fileTypes.length || Array.from(new Set(mergedTypes)).length !== mergedTypes.length;
  }

  private _subCategoryFilter(data: object) {
    const sub_category = this.filterConfig.sub_category;
    return !sub_category || data['categories'][0] === sub_category;
  }

  public fetchSubCategory(id: number, resetDisable?: boolean): object[] {
    const data = this.listData['categories'].filter((category) => category.id === +id);
    this.subCategories = data.length ? data[0]['input'] : [];
    !resetDisable && this.resetFieldByType('sub_category');
    return this.subCategories;
  }

  public patchFileTypes(id: string, checked: boolean) {
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
    if (field_type === 'category') {
      this.subCategories = [];
      formValue['sub_category'] = '';
    }
    this.filterFormGroup.patchValue(formValue);
  }

  public trackByFn(index, item) {
    return item.id;
  }

  public sortFileList(order_by) {
    this.orderByField = order_by;
    this.reverseSort = !this.reverseSort;
    const sort_order = !this.reverseSort ? 'asc' : 'desc';
    this.listData['files'] = _.orderBy(this.listData['files'], order_by, sort_order);
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
