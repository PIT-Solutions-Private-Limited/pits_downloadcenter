import { Routes } from '@angular/router';

import { DownloadCenterComponent } from './download-center/download-center.component';

export const routes: Routes = [
  { path: '**', component: DownloadCenterComponent }
];
