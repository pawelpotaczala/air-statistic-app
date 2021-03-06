import { Routes } from '@angular/router';
import { DetailTablePageComponent } from '../detail/detail-table-page/detail-table-page.component';
import { DetailDiagramPageComponent } from '../detail/detail-diagram-page/detail-diagram-page.component';
import { DashboardComponent } from './dashboard.component';
import { StationDetailPageWrapperComponent } from '../detail/station-detail-page-wrapper/station-detail-page-wrapper.component';
import { DetailPageComponent } from '../detail/detail-page.component';

export const DASHBOARD_ROUTES: Routes = [
  {
    path: '',
    component: DashboardComponent,
  },
  {
    path: 'detail/station/:stationCode',
    component: StationDetailPageWrapperComponent,
    children: [
      {
        path: '', component: DetailPageComponent,
        data: {
          backUrl: '/dashboard',
        }
      },
      {
        path: 'diagram/:standCode', component: DetailDiagramPageComponent,
        data: {
          backUrl: '/../..'
        }
      },
      {
        path: 'table/:standCode', component: DetailTablePageComponent,
        data: {
          backUrl: '/../..'
        }
      }
    ]
  }
];
