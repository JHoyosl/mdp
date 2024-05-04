import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { MappingFileIndex } from '../Interfaces/mapping-file.interface';
import { GenericResponse } from '../Interfaces/shared.interfaces';
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

  uploadMappingFile(skipTop, file):Observable<any>{
    const body = new FormData();
      body.append('file', file)
      body.append('skipTop', skipTop);

    return this.httpClient.post(`${this.baseUrl}/MappingFileToArray`, body);
  }

  getMapIndex(type): Observable<any>{
    const params = new HttpParams().append('type', type);

    return this.httpClient.get(`${this.baseUrl}/getMapIndex`, {params: params});
  }
}
