import {NgModule} from '@angular/core';
import {NzTableModule} from 'ng-zorro-antd/table';
import {NzTypographyModule} from 'ng-zorro-antd/typography';
import {NzSpaceModule} from 'ng-zorro-antd/space';
import {NzCardModule} from 'ng-zorro-antd/card';
import {NzIconModule} from 'ng-zorro-antd/icon';

@NgModule({
  declarations: [],
  imports: [
    NzTableModule,
    NzTypographyModule,
    NzSpaceModule,
    NzCardModule,
    NzIconModule.forChild([])
  ],
  exports: [
    NzTableModule,
    NzTypographyModule,
    NzSpaceModule,
    NzCardModule,
    NzIconModule
  ]
})
export class NgZorroAntdModule {
}
