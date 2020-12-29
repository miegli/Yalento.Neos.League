import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ConfigurationService {

  firebaseConfiguration = {
    apiKey: '__FIREBASE_CONFIGURATION__apiKey__',
    authDomain: '__FIREBASE_CONFIGURATION__apiKey__',
    databaseURL: '__FIREBASE_CONFIGURATION__apiKey__',
    projectId: '__FIREBASE_CONFIGURATION__apiKey__',
    storageBucket: '__FIREBASE_CONFIGURATION__apiKey__',
    messagingSenderId: '__FIREBASE_CONFIGURATION__apiKey__',
    appId: '__FIREBASE_CONFIGURATION__apiKey__',
    measurementId: '__FIREBASE_CONFIGURATION__apiKey__'
  }

  constructor() {
    this.loadConfiguration();
  }

  private loadConfiguration() {

    if (window['yalento'] && window['yalento']['firebaseConfiguration']) {
      this.firebaseConfiguration = window['yalento']['firebaseConfiguration'];
      return;
    }

  }

}
