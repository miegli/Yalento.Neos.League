import { NgModule } from '@angular/core';
import { ObserversModule } from '@angular/cdk/observers';
import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from '../app-routing.module';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { FormsModule } from '@angular/forms';


@NgModule({
  declarations: [],
  imports: [
    FormsModule,
    ObserversModule,
    BrowserModule,
    AppRoutingModule,
    NoopAnimationsModule
  ],
  exports: [
    FormsModule,
    ObserversModule,
    BrowserModule,
    AppRoutingModule,
    NoopAnimationsModule
  ]
})
export class SharedModule {}
