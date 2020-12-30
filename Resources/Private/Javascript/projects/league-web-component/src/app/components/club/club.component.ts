import {ChangeDetectorRef, Component, Input, OnChanges, OnInit, SimpleChanges} from '@angular/core';
import {RepositoryService} from "../../shared/repository.service";
import {Observable} from "yalento";
import {Club} from "../../shared/models/Club";

@Component({
  selector: 'app-yalento-league-club',
  templateUrl: './club.component.html',
  styleUrls: ['./club.component.scss']
})
export class ClubComponent implements OnInit, OnChanges {

  @Input() id: string;

  club$: Observable<Club>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {


  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['id']) {
      this.club$ = this.repositoryService.selectOneByIdentifier<Club>(changes['id']['currentValue'], 'Club');
    }
  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}
