import { Bank } from './bank.interface'

export interface MappingFileIndex {
  'id': number,
  'bank': Bank,
  'company': any,
  'header': number,
  'description': string,
  'createdBy': any,
  'type': string,
  'map': Map[], 
  'base': [string[]]
  'separator': string, 
  'extension': string,
}

export interface Map {
  fileColumn: number,
  header: string,
  mapIndex: number,
  value: string
}

export interface MappingIndex{
  'id': number,
  'description': string,
  'type': number,
}

export interface StoreMappingRequest {
  'type': string,
  'bankId'?: string, 
  'description': string,
  'dateFormat': string,
  'separator': string,
  'skipTop': number,
  'skipBottom': number,
  'map': Map[],
  'base': any[],
}