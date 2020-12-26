import { ChangeDetectorRef, Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-yalento-league-club',
  templateUrl: './club.component.html',
  styleUrls: ['./club.component.scss']
})
export class ClubComponent implements OnInit {

  public constructor(private readonly changeDetectorRef: ChangeDetectorRef) { }

  ngOnInit(): void {

  }

  observeContent() {
    this.changeDetectorRef.detectChanges();
  }
}
