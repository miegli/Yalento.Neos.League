import {CUSTOM_ELEMENTS_SCHEMA, DoBootstrap, Injector, NgModule} from '@angular/core';
import {createCustomElement} from '@angular/elements';
import {SharedModule} from './shared/shared.module';
import {ClubComponent} from './components/club/club.component';
import {TableComponent} from './components/table/table.component';
import {TeamComponent} from './components/team/team.component';
import {TableTeamComponent} from './components/table-team/table-team.component';
import {NZ_I18N} from 'ng-zorro-antd/i18n';
import {de_DE} from 'ng-zorro-antd/i18n';
import {registerLocaleData} from '@angular/common';
import de from '@angular/common/locales/de';
import {FormsModule} from '@angular/forms';
import {HttpClientModule} from '@angular/common/http';
import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {NgZorroAntdModule} from "./shared/ng-zorro-antd/ng-zorro-antd.module";

registerLocaleData(de);

declare let customElements: any;

@NgModule({
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
  declarations: [
    ClubComponent,
    TableComponent,
    TeamComponent,
    TableTeamComponent,
  ],
  imports: [
    SharedModule,
    FormsModule,
    HttpClientModule,
    BrowserAnimationsModule,
    NgZorroAntdModule
  ],
  providers: [{provide: NZ_I18N, useValue: de_DE}],
  entryComponents: []
})
export class AppModule implements DoBootstrap {

  constructor(private injector: Injector) {

  }

  ngDoBootstrap() {
    customElements.define('app-yalento-league-club', createCustomElement(ClubComponent, {injector: this.injector}));
    customElements.define('app-yalento-league-table', createCustomElement(TableComponent, {injector: this.injector}));
    customElements.define('app-yalento-league-team', createCustomElement(TeamComponent, {injector: this.injector}));
    customElements.define('app-yalento-league-table-team', createCustomElement(TableTeamComponent, {injector: this.injector}));
  }
}
