import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';

import { AppRoutingModule } from './../app-routing.module';
import { DownloadCenterComponent } from './download-center.component';

describe('DownloadCenterComponent', () => {
  let component: DownloadCenterComponent;
  let fixture: ComponentFixture<DownloadCenterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [DownloadCenterComponent],
      imports: [ReactiveFormsModule, NgxPaginationModule, HttpClientModule, AppRoutingModule]
    })
      .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DownloadCenterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
