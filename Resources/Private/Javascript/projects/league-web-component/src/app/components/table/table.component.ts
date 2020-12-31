import {ChangeDetectorRef, Component, Input, OnInit} from '@angular/core';
import {RepositoryService} from "../../shared/repository.service";
import {Table} from "../../shared/models/Table";
import {Observable} from "yalento";

@Component({
  selector: 'app-yalento-league-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit {

  @Input() id: string;

  table$: Observable<Table>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {
    this.table$ = this.repositoryService.selectOneByIdentifier<Table>(this.id, 'Table');
  }

}
