import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable, Repository, switchMap} from "yalento";
import {Fallback} from "./models/Fallback";
import {Table} from "./models/Table";
import {Models} from "./interfaces/models";
import {EMPTY, of} from "rxjs";
import {Club} from "./models/Club";
import firebase from 'firebase/app';
import 'firebase/firestore';
import {ConfigurationService} from "./configuration.service";

@Injectable({
  providedIn: 'root'
})
export class RepositoryService {

  models: { [P in keyof Models]: Models[P] } = {
    Fallback: Fallback,
    Table: Table,
    Club: Club
  }

  constructor() {
  }

  public getRepository<T>(name: string): Repository<T> {
    const globalName = 'yalento-global-repository-' + name;
    if ((document.getRootNode() as any)[globalName] === undefined) {
      (document.getRootNode() as any)[globalName] = new Repository(this.models[name] || this.models['fallback'], name);
      ((document.getRootNode() as any)[globalName] as Repository<any>).connectFirestore(this.getFirebaseApp().firestore());
    }
    return (document.getRootNode() as any)[globalName];
  }

  /**
   * select one by identifier
   * @param identifier
   * @param repository
   */
  public selectOneByIdentifier<T>(identifier: string | BehaviorSubject<string>, repository: keyof Models): Observable<T> {

    return this.getRepository<T>(repository).select({
      where: 'identifier LIKE ?',
      params: [identifier]
    }).getReadOnlyResultsAsObservable().pipe(
      switchMap((tables) => tables.length ? of(tables[0]) : EMPTY))
  }

  /**
   *
   * @private
   */
  private getFirebaseApp(): any {
    const globalName = 'yalento-global-firebase-app';
    if ((document.getRootNode() as any)[globalName] === undefined) {
      const configurationService = new ConfigurationService();
      (document.getRootNode() as any)[globalName] = firebase.initializeApp(configurationService.firebaseConfiguration);
    }
    return (document.getRootNode() as any)[globalName];
  }


}
