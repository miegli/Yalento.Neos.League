import {ChangeDetectorRef, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {Observable} from "yalento";
import {RepositoryService} from "../../shared/repository.service";
import {Team} from "../../shared/models/Team";

@Component({
  selector: 'app-yalento-league-team',
  templateUrl: './team.component.html',
  styleUrls: ['./team.component.scss']
})
export class TeamComponent implements OnInit, OnChanges {

  @Input() id: string;

  team$: Observable<Team>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {


  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['id']) {
      this.team$ = this.repositoryService.selectOneByIdentifier<Team>(changes['id']['currentValue'], 'Team');
    }
  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}
