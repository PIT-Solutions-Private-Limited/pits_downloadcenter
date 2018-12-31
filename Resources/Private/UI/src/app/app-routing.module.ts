import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { DownloadCenterComponent } from './download-center/download-center.component';

const routes: Routes = [
  { path: '', component: DownloadCenterComponent },
  { path: '**', redirectTo: '/', pathMatch: 'full' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
