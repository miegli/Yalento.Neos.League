import {Table} from "../models/Table";
import {Fallback} from "../models/Fallback";
import {Club} from "../models/Club";
import {Team} from "../models/Team";
import {TableTeam} from "../models/TableTeam";

export interface Models {
  Fallback: typeof Fallback
  Club: typeof Club;
  Table: typeof Table;
  Team: typeof Team;
  TableTeam: typeof TableTeam;
}
