import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { ReconciliationBalanceUpload, ReconciliationIniUpload, ReconciliationIniUploadResponse, ReconciliationItem, ReconciliationResume, ReconciliationResumeResponse, ReonciliationBalance } from '../../Interfaces/reconciliation.interface';
import { GenericResponse } from '../../Interfaces/shared.interfaces';
import { ReconciliationModel } from '../../models/reconciliation.model';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ReconciliationService {

  private baseUrl = `${environment.url}reconciliation`;

  constructor(private httpClient: HttpClient) { }

  getAccountResume(): Observable<ReconciliationResume[]>{
    return this.httpClient.get<ReconciliationResumeResponse>(`${this.baseUrl}/getAccountResume`)
    .pipe(
      map((response) => response.data.map(
        (obj) => ReconciliationModel.ReconciliationResumeToInterfac(obj))));
  }

  getAccountProcess(): Observable<ReconciliationItem[]> {
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/getAccountProcess`)
      .pipe(
        map((response) => response.data.map((obj) => ReconciliationModel.AccountProcessToInterface(obj))));
  }

  getReconciliationAccount(){
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/getReconciliationAccount`);
  }

  getProcessById(process: string): Observable<ReconciliationItem[]>{
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/getAccountProcessById/${process}`)
      .pipe( 
        map((response) => 
          {
            console.log(response);
            return response.data.map((item) => 
            ReconciliationModel.AccountProcessToInterface(item))
          }
        ));
  }

  getAccountProcessById(process: string){
    return this.httpClient.get(`${this.baseUrl}/getAccountProcessById/${process}`);
  }

  uploadIni(data: ReconciliationIniUpload): Observable<ReconciliationItem[]>{
    const body = new FormData();
    body.append('file', data.file )
    body.append('date', data.date )

    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/iniReconciliation`,body)
      .pipe(
        map((response) => {
          return response.data.map((v) => ReconciliationModel.AccountProcessToInterface(v))
        })
      );
  }

  uploadBalance(process, data: ReconciliationBalanceUpload[]):Observable<any>{
    const params = new HttpParams()
      .append('balance', JSON.stringify(data))
      .append('process', process)
    
    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/setBalance`, params);
  }

  startNewProcess(date: string, ids: number[]): Observable<any>{
    const params = new HttpParams()
      .append('date', date)
      .append('accounts', JSON.stringify(ids));
    return this.httpClient.post<any>(`${this.baseUrl}/startNewProcess`, params);
  }
}
