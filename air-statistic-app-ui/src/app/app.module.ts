import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgApexchartsModule } from 'ng-apexcharts';
import { DashboardModule } from './page/dashboard/dashboard.module';
import { SharedModule } from './shared/shared.module';
import { MenuComponent } from './menu/menu.component';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { BackendInterceptor } from './config/backend.interceptor';

@NgModule({
  declarations: [
    AppComponent,
    MenuComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule,
    ReactiveFormsModule,
    NgApexchartsModule,
    DashboardModule,
    SharedModule,
    HttpClientModule,
  ],
  bootstrap: [AppComponent],
  providers: [
    {provide: HTTP_INTERCEPTORS, useClass: BackendInterceptor, multi: true},
  ]
})
export class AppModule {
}