import { CUSTOM_ELEMENTS_SCHEMA, DoBootstrap, Injector, NgModule } from '@angular/core';
import { createCustomElement } from '@angular/elements';
import { SharedModule } from './shared/shared.module';
import { ClubComponent } from './components/club/club.component';

declare let customElements: any;

@NgModule({
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
  declarations: [
    ClubComponent
  ],
  imports: [
    SharedModule
  ],
  providers: [],
  entryComponents: []
})
export class AppModule implements DoBootstrap {

  constructor(private injector: Injector) {

  }

  ngDoBootstrap() {
    customElements.define('app-yalento-league-club', createCustomElement(ClubComponent, { injector: this.injector }));
  }
}
