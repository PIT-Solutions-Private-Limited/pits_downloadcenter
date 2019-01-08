import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { PaginationInstance } from 'ngx-pagination';

import { DownloadCenterService } from './download-center.service';

import { Subject } from 'rxjs';
import { takeUntil, take } from 'rxjs/operators';

import * as _ from 'lodash';

interface FilterConfig {
  keyword_search: string;
  category: object[];
  file_types: string[];
  cPage: string;
}

@Component({
  selector: 'app-download-center',
  templateUrl: './download-center.component.html'
})
export class DownloadCenterComponent implements OnInit, OnDestroy {
  public baseURL = document.getElementById('baseURL');
  public orderByField = '';
  public reverseSort = true;
  public listData = {};
  public loading = false;
  public filterFormGroup: FormGroup;
  public filterConfig: FilterConfig = { keyword_search: '', category: [], file_types: [], cPage: '1' };
  public config: PaginationInstance = { id: 'custom', itemsPerPage: 10, currentPage: 1 };

  private _categoryList = {};
  private _initialListData = '[]';
  private _ids = [];
  private _traverseObj = {};

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
      .subscribe((data) => {
        this.loading = false;
        this.listData = data;
        this._categoryList[0] = data['categories'];
        this._initialListData = JSON.stringify(this.listData);
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
        let categories = [];
        if (params['category']) {
          const categoryArray = params['category'].split(',');
          categories = categoryArray.map(id => ({ categoryId: id }));
          categoryArray.forEach((id, i) => {
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

  public onCategoryChange(id: number, index: number): void {
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
      this._setTraverseObj(this.listData['categories'].filter((d) => d.id === +category[0])[0], +category[category.length - 1]);
      this._traverse(this._traverseObj);
    }
    this.listData['files'] = JSON.parse(this._initialListData)['files']
      .filter(this._keyWordFilter.bind(this))
      .filter(this._categoryFilter.bind(this))
      .filter(this._fileTypeFilter.bind(this));
    !!this.orderByField && this.sortFileList(this.orderByField, true);
  }

  private _setRouting(filterConfig: FilterConfig): void {
    const config = Object.assign({}, filterConfig);
    config['category'] = config['category'].filter(data => !!data['categoryId']).map(data => data['categoryId']);
    const params = {};
    Object.keys(config)
      .filter((data) => !!config[data].length)
      .forEach((key) => { params[key] = `${config[key]}`; });
    this._router.navigate([], { relativeTo: this._activatedRoute, queryParams: params });
  }

  private _setTraverseObj(obj: object, id: number): void {
    const that = this;
    _.forIn(obj, (val, key) => {
      (obj['id'] === id) && (that._traverseObj = obj);
      if (_.isArray(val)) {
        val.forEach((el) => _.isObject(el) && ((el['id'] === id) ? (that._traverseObj = el) : that._setTraverseObj(el, id)));
      }
    });
  }

  private _traverse(obj: object): void {
    const that = this;
    _.forIn(obj, (val, key) => {
      (key === 'id') && that._ids.push(val);
      _.isArray(val) && val.forEach((el) => _.isObject(el) && that._traverse(el));
    });
  }

  private _keyWordFilter(data: object): boolean {
    const keyword_search = this.filterConfig.keyword_search;
    const searchKeys = ['title', ...(!+this.listData['config']['hideSizeColumn'] ? ['size'] : []), 'extension'];
    const searchString = searchKeys.map(key => data[key]).join('').toLowerCase();
    return !keyword_search || searchString.indexOf(keyword_search.toLowerCase().trim()) !== -1;
  }

  private _categoryFilter(data: object): boolean {
    const category = this.filterConfig.category.filter(res => !!res['categoryId']).map(res => res['categoryId']);
    if (category.length) {
      const dataCat = data['categories'].map(id => +id);
      const categories = [...dataCat, ...this._ids];
      return Array.from(new Set(categories)).length !== categories.length;
    }
    return true;
  }

  private _fileTypeFilter(data: object): boolean {
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

  public getCategoryList(i): object[] {
    return this._categoryList[i] || [];
  }

  public trackByFn(index, item): void {
    return item.id;
  }

  public sortFileList(order_by: string, skip?: boolean): void {
    this.orderByField = order_by;
    !skip && (this.reverseSort = !this.reverseSort);
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
