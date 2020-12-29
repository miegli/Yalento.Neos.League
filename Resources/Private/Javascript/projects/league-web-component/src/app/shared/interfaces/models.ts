import {Table} from "../models/Table";
import {Fallback} from "../models/Fallback";
import {Club} from "../models/Club";

export interface Models {
  Fallback: typeof Fallback
  Club: typeof Club;
  Table: typeof Table;
}
