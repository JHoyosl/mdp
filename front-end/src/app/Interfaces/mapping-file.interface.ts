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
  'base': {description: string, value: string}[];
  'separator': string, 
  'extension': string,
  'dateFormat': string,
  'skipTop': number,
  'skipBottom': number,
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

export interface updateMappingRequest {
  id?: number,
  description: string,
  dateFormat: string,
  separator: string,
  skipTop: number,
  skipBottom: number,
  map: Map[] | string,
}

// TODO: FIX INTERFACES

export interface MappedFile {
  id: number,
  bank: Bank,
  company: any,
  header: number,
  description: string,
  createdBy: any,
  type: string
  map: Map[]
  base: Base[]
  separator: string
  extension: string
  skipTop: number
  skipBottom: number
  dateFormat: string
}

export interface Base {
  description: string
  value: string
}