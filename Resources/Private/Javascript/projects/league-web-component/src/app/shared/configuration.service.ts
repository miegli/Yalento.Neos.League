import {Injectable} from '@angular/core';
import {Configuration} from "./models/Configuration";

@Injectable({
  providedIn: 'root'
})
export class ConfigurationService {

  private configuration: Configuration = {
    firebaseConfiguration: {
      apiKey: '__FIREBASE_CONFIGURATION__apiKey__',
      authDomain: '__FIREBASE_CONFIGURATION__apiKey__',
      databaseURL: '__FIREBASE_CONFIGURATION__apiKey__',
      projectId: '__FIREBASE_CONFIGURATION__apiKey__',
      storageBucket: '__FIREBASE_CONFIGURATION__apiKey__',
      messagingSenderId: '__FIREBASE_CONFIGURATION__apiKey__',
      appId: '__FIREBASE_CONFIGURATION__apiKey__',
      measurementId: '__FIREBASE_CONFIGURATION__apiKey__'
    },
    restApiBaseUrl: '__REST_API__baseUrl__'
  }


  constructor() {
    this.loadConfiguration();
  }

  public getConfiguration(): Configuration {
    return this.configuration;
  }

  private loadConfiguration() {


    if (window['yalento'] && window['yalento']['firebaseConfiguration']) {
      this.configuration.firebaseConfiguration = window['yalento']['firebaseConfiguration'];
    }

    try {
      const restApiBaseUrlOverride = (document.getRootNode() as any).getElementsByTagName('body')[0].getAttribute('data-yalento-neos-league-base-url');
      if (restApiBaseUrlOverride) {
        this.configuration.restApiBaseUrl = restApiBaseUrlOverride;
      }
    } catch (e) {
    }

  }

}
