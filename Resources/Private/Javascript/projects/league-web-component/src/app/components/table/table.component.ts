import {ChangeDetectorRef, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {RepositoryService} from "../../shared/repository.service";
import {Table} from "../../shared/models/Table";
import {Observable} from "yalento";

@Component({
  selector: 'app-yalento-league-table',
  templateUrl: './table.component.html',
  styleUrls: ['./table.component.scss']
})
export class TableComponent implements OnInit, OnChanges {

  @Input() id: string;

  table$: Observable<Table>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {


  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['id']) {
      this.table$ = this.repositoryService.selectOneByIdentifier<Table>(changes['id']['currentValue'], 'Table');
    }
  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}
