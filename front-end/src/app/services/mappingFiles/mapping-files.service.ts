import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { MappingFileIndex, MappingIndex, StoreMappingRequest, updateMappingRequest } from '../../Interfaces/mapping-file.interface';
import { GenericResponse } from '../../Interfaces/shared.interfaces';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class MappingFilesService {

  private baseUrl = `${environment.url}mappingFiles`;

  constructor(private httpClient: HttpClient) { }
  
  index(source: 'thirdParty' | 'accounting' | 'all'): Observable<MappingFileIndex[]>{
    const params = new HttpParams().append('source', source);

    return this.httpClient.get<GenericResponse>(`${this.baseUrl}`, { params }).pipe(
      map((response) => response.data)
    );
  }

  store(data: StoreMappingRequest): Observable<MappingFileIndex>{
    const body = new FormData();
    body.append('type', data.type);
    body.append('bankId', data.bankId);
    body.append('description', data.description);
    body.append('dateFormat', data.dateFormat);
    body.append('separator', data.separator);
    body.append('skipTop', data.skipTop.toString());
    body.append('skipBottom', data.skipBottom.toString());
    body.append('map', JSON.stringify(data.map));
    body.append('base', JSON.stringify(data.base));
      

    return this.httpClient.post<GenericResponse>(`${this.baseUrl}`, body).pipe(
      map((response) => response.data)
    );

  }

  mappingFileToArray(skipTop, file):Observable<[string[]]>{
    const body = new FormData();
      body.append('file', file)
      body.append('skipTop', skipTop);
    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/MappingFileToArray`, body).pipe(
      map((response) => response.data)
    );
  }

  getMapIndex(type): Observable<MappingIndex[]>{
    const params = new HttpParams().append('type', type);
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/getMapIndex`, {params: params}).pipe(
      map((response) => response.data)
    )
  }

  patchMap(data: updateMappingRequest): Observable<MappingIndex>{
    return this.httpClient.patch<GenericResponse>(`${this.baseUrl}`,data).pipe(
      map((respone) => respone.data)
    )
  }

  deleteMap(mapId: number):Observable<string>{
    return this.httpClient.delete<GenericResponse>(`${this.baseUrl}/${mapId}`).pipe(
      map((respone)=> respone.data.toString())
    );
  }
}
