import {Team} from "./Team";
import {Tournament} from "./Tournament";

export class Table {
  identifier: string;
  title: string;
  name: string;
  teams: Team[];
  tournaments: Tournament[];
}
