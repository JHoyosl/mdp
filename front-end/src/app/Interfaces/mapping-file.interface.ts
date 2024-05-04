import { Bank } from "./bank.interface"

export interface MappingFileIndex {
  'id': number,
  'bank': Bank,
  'company': any,
  'header': number,
  'description': string,
  'createdBy': any,
  'type': string,
  'map': map[], 
  'base': [string[]]
  'separator': string, 
  'extension': string,
}

export interface map {
  fileColumn: number,
  header: string,
  mapIndex: number,
  value: string
}