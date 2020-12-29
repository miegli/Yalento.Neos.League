import {ChangeDetectorRef, Component, Input, OnInit, SimpleChanges} from '@angular/core';
import {RepositoryService} from "../../shared/repository.service";
import {BehaviorSubject, Observable} from "yalento";
import {Club} from "../../shared/models/Club";

@Component({
  selector: 'app-yalento-league-club',
  templateUrl: './club.component.html',
  styleUrls: ['./club.component.scss']
})
export class ClubComponent implements OnInit {

  @Input() id: string;

  clubIdentifier: BehaviorSubject<string> = new BehaviorSubject<string>(null);
  club$: Observable<Club>;

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef,
                     private readonly repositoryService: RepositoryService) {
  }

  ngOnInit(): void {
    this.club$ = this.repositoryService.selectOneByIdentifier<Club>(this.clubIdentifier, 'Club');
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['id']) {
      this.clubIdentifier.next(this.id);
    }
  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}
