import {ChangeDetectorRef, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {Observable} from "yalento";
import {RepositoryService} from "../../shared/repository.service";
import {TableTeam} from "../../shared/models/TableTeam";

@Component({
  selector: 'app-yalento-league-table-team',
  templateUrl: './table-team.component.html',
  styleUrls: ['./table-team.component.scss']
})
export class TableTeamComponent implements OnInit, OnChanges {

  @Input() id: string;

  tableTeam$: Observable<TableTeam>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {


  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['id']) {
      this.tableTeam$ = this.repositoryService.selectOneByIdentifier<TableTeam>(changes['id']['currentValue'], 'TableTeam');
    }
  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}

