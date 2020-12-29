import {Injectable} from '@angular/core';
import {BehaviorSubject, finalize, Observable, Repository, Subject, switchMap, takeUntil} from "yalento";
import {Fallback} from "./models/Fallback";
import {Table} from "./models/Table";
import {Models} from "./interfaces/models";
import {EMPTY, of} from "rxjs";
import {Club} from "./models/Club";
import firebase from 'firebase/app';
import 'firebase/firestore';
import {ConfigurationService} from "./configuration.service";
import {HttpClient} from "@angular/common/http";
import {Configuration} from "./models/Configuration";

@Injectable({
  providedIn: 'root'
})
export class RepositoryService {

  private models: { [P in keyof Models]: Models[P] } = {
    Fallback: Fallback,
    Table: Table,
    Club: Club
  }

  private configuration: Configuration;

  constructor(private readonly httpClient: HttpClient, private configurationService: ConfigurationService) {
    this.configuration = this.configurationService.getConfiguration();
  }

  public getRepository<T>(name: string): Repository<T> {
    const globalName = 'yalento-global-repository-' + name;
    if ((document.getRootNode() as any)[globalName] === undefined) {
      (document.getRootNode() as any)[globalName] = new Repository(this.models[name] || this.models['fallback'], name);
      if (!this.configuration.restApiBaseUrl) {
        ((document.getRootNode() as any)[globalName] as Repository<any>).connectFirestore(this.getFirebaseApp().firestore());
      }
    }
    return (document.getRootNode() as any)[globalName];
  }

  /**
   * select one by identifier
   * @param identifier
   * @param model
   */
  public selectOneByIdentifier<T>(identifier: string | BehaviorSubject<string>, model: keyof Models): Observable<T> {

    const repository = this.getRepository<T>(model);
    const destroyed = new Subject<any>();

    if (this.configuration.restApiBaseUrl) {
      if (typeof identifier === 'string') {
        this.loadOneByIdentifier<T>(repository, identifier, model);
      } else {
        identifier.asObservable().pipe(
          takeUntil(destroyed)
        ).subscribe((id) => this.loadOneByIdentifier<T>(repository, id, model));

      }
    }

    return repository.select({
      where: 'identifier LIKE ?',
      params: [identifier]
    }).getReadOnlyResultsAsObservable().pipe(
      switchMap((tables) => tables.length ? of(tables[0]) : EMPTY),
      finalize(() => {
        destroyed.next();
      })
    )


  }

  /**
   *
   * @private
   */
  private getFirebaseApp(): any {
    const globalName = 'yalento-global-firebase-app';
    if ((document.getRootNode() as any)[globalName] === undefined) {
      (document.getRootNode() as any)[globalName] = firebase.initializeApp(this.configuration.firebaseConfiguration);
    }
    return (document.getRootNode() as any)[globalName];
  }

  /**
   * load one by identifier from Rest endpoint
   * @param repository
   * @param identifier
   * @param model
   * @private
   */
  private loadOneByIdentifier<T>(repository: Repository<T>, identifier: string, model: keyof Models) {

    if (!this.configuration.restApiBaseUrl) {
      return;
    }

    this.httpClient.get(`${this.configuration.restApiBaseUrl}/${identifier}`).toPromise().then(
      (data) => {
        console.log('try to add', data);
        repository.create({identifier, ...data as any}, identifier, null, 'firestore').then((c) => console.log('created', c)).catch((e) => console.log('e', e))
      }
    ).catch((e) => console.log(e));

  }


}
