import {Injectable} from '@angular/core';
import {
  finalize,
  IEntity,
  Observable,
  Repository,
  Subject,
  switchMap
} from "yalento";
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
import eachDeep from 'deepdash-es/eachDeep';
import {Team} from "./models/Team";
import {TableTeam} from "./models/TableTeam";

@Injectable({
  providedIn: 'root'
})
export class RepositoryService {

  private models: { [P in keyof Models]: Models[P] } = {
    Fallback: Fallback,
    Table: Table,
    Club: Club,
    Team: Team,
    TableTeam: TableTeam
  }

  private configuration: Configuration;

  constructor(private readonly httpClient: HttpClient, private configurationService: ConfigurationService) {
    this.configuration = this.configurationService.getConfiguration();
  }

  public getRepository<T>(name: string): Repository<T> {
    const globalName = 'yalento-global-repository-' + name;
    if ((document.getRootNode() as any)[globalName] === undefined) {
      (document.getRootNode() as any)[globalName] = new Repository(this.models[name] || this.models['fallback'], name);
    }
    return (document.getRootNode() as any)[globalName];
  }

  /**
   * select one by identifier
   * @param identifier
   * @param model
   */
  public selectOneByIdentifier<T>(identifier: string, model: keyof Models): Observable<T> {

    const repository = this.getRepository<T>(model);
    const destroyed = new Subject<any>();

    return this.loadOneByIdentifierIfNotExisting(repository, identifier, model).pipe(
      switchMap(() => repository.select({
          where: 'identifier LIKE ?',
          params: [identifier]
        }).getReadOnlyResultsAsObservable().pipe(
        switchMap((results) => results.length ? of(results[0]) : EMPTY),
        finalize(() => {
          destroyed.next();
        }))
      )
    );

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
   * load one by identifier from Rest endpoint only if object is not existing yet
   * @param repository
   * @param identifier
   * @param model
   * @private
   */
  private loadOneByIdentifierIfNotExisting(repository: Repository<any>, identifier: string, model: keyof Models): Observable<void> {

    return new Observable((observer) => {
      repository.exec({
        where: 'identifier LIKE ?',
        params: [identifier],
        limit: 1
      }).then((results) => {
        if (results.length === 0) {
          this.loadOneByIdentifier(repository, identifier, model).toPromise().finally(() => {
            observer.next()
          });
        } else {
          observer.next();
        }
      });
    })


  }

  /**
   * load one by identifier from Rest endpoint
   * @param repository
   * @param identifier
   * @param model
   * @private
   */
  private loadOneByIdentifier<T>(repository: Repository<T>, identifier: string, model: keyof Models): Observable<void> {

    return new Observable((observer) => {

      if (!identifier || !this.configuration.restApiBaseUrl) {
        observer.complete();
        return;
      }

      this.httpClient.get(`${this.configuration.restApiBaseUrl}/nodes/${identifier}`).toPromise().then(
        (data) => {
          repository.create({identifier, ...data as any}, identifier, null, 'firestore')
            .then((entity) => {
              this.postProcessLoadOneByIdentifier(entity, model);
              observer.complete();
            })
            .catch((e) => observer.error(e))
        }
      ).catch((e) => observer.error(e));

    });

  }

  private postProcessLoadOneByIdentifier<T>(entity: IEntity<T>, skipModel: string) {

    let childObjects = {};

    eachDeep(
      entity.getModel(),
      (child, i, parent, ctx) => {
        if (child && child.type !== undefined && child.identifier !== undefined && child.type !== skipModel) {
          if (childObjects[child.type] === undefined) {
            childObjects[child.type] = [];
          }
          childObjects[child.type].push(child);
        }
      }
    );

    // load child objects to corresponding repository
    Object.keys(childObjects).forEach((modelKey) => {
      if (this.models[modelKey] !== undefined) {
        this.getRepository(modelKey).createMany(childObjects[modelKey]).finally();
      }
    })

    childObjects = null;
  }

}
