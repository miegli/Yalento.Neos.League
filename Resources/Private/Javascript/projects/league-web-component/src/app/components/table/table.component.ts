import {ChangeDetectorRef, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {RepositoryService} from "../../shared/repository.service";
import {Table} from "../../shared/models/Table";
import {BehaviorSubject, Observable} from "yalento";

@Component({
  selector: 'app-yalento-league-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit, OnChanges {

  @Input() id: string;

  tableIdentifier: BehaviorSubject<string> = new BehaviorSubject<string>(null);
  table$: Observable<Table>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {
    this.table$ = this.repositoryService.selectOneByIdentifier<Table>(this.tableIdentifier, 'Table');
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['id']) {
      this.tableIdentifier.next(this.id);
    }
  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}
