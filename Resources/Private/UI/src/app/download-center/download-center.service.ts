import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

import { environment } from './../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class DownloadCenterService {

  constructor(private _http: HttpClient) { }

  getData(): Observable<object> {
    const el = document.getElementById('actionURL');
    const actionURL = el ? el['value'] : environment.actionURL;
    return this._http.get(`${actionURL}`);
  }
}
