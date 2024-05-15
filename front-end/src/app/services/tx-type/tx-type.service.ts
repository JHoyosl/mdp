import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { GenericResponse } from 'src/app/Interfaces/shared.interfaces';
import { CreateExternalTxRequest, CreateLocalTxRequest, ExternalTxType, LocalTxType, UpdateExternalTxRequest, UpdateLocalTxRequest } from 'src/app/Interfaces/txType.interface';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class TxTypeService {

  private externalUrl = `${environment.url}externalTxType`;
  private localUrl = `${environment.url}localTxType`;

  private filter = new BehaviorSubject<string>(null);
  filter$: Observable<string> = this.filter.asObservable();

  constructor(private httpClient: HttpClient) { 

  }

  setFilter(value:string){
    this.filter.next(value);
  }

  // REQUESTS
  indexExternal():Observable<ExternalTxType[]>{
    return this.httpClient.get<GenericResponse>(`${this.externalUrl}`).pipe(
      map((response) => response.data)
    );
  }

  indexLocal():Observable<LocalTxType[]>{
    return this.httpClient.get<GenericResponse>(`${this.localUrl}`).pipe(
      map(response => response.data)
    );
  }

  storeExternal(request: CreateExternalTxRequest): Observable<ExternalTxType>{
    return this.httpClient.post<GenericResponse>(`${this.externalUrl}`, request).pipe(
      map((response) => response.data)
    );
  }
  
  storeLocal(request: CreateLocalTxRequest): Observable<LocalTxType>{
    return this.httpClient.post<GenericResponse>(`${this.localUrl}`, request).pipe(
      map((response) => response.data)
    );
  }

  deleteExternal(id: number): Observable<string>{
    return this.httpClient.delete<GenericResponse>(`${this.externalUrl}/${id}`).pipe(
      map((respone) => respone.data)
    );
  }

  deleteLocal(id: number): Observable<string>{
    return this.httpClient.delete<GenericResponse>(`${this.localUrl}/${id}`).pipe(
      map((response) => response.data)
    );
  }

  updateLocal(id: number, txTypeInfo: UpdateLocalTxRequest): Observable<LocalTxType>{
    return this.httpClient.put<GenericResponse>(`${this.localUrl}/${id}`, txTypeInfo).pipe(
      map((response) => response.data)
    );
  }

  updateExternal(id: number, txTypeInfo: UpdateExternalTxRequest): Observable<ExternalTxType>{
    return this.httpClient.put<GenericResponse>(`${this.externalUrl}/${id}`, txTypeInfo).pipe(
      map((response) => response.data)
    );
  }
}
