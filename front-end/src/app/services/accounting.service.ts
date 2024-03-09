import { HttpClient } from '@angular/common/http';
import { Injectable, OnInit } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { AccountingHeader, AccountingHeaderResponse, AccountingUploadInfo } from '../Interfaces/accounting.interface';
import { map } from 'rxjs/operators';
import { AccountingModel } from '../models/accounting.model';
import { GenericResponse } from '../Interfaces/shared.interfaces';

@Injectable({
  providedIn: 'root'
})
export class AccountingService {

  private baseUrl = `${environment.url}accounting`;

  constructor(private httpClient: HttpClient) { 

  }

  index(): Observable<AccountingHeader[]> {
    
    return this.httpClient.get<AccountingHeaderResponse>(this.baseUrl).pipe(
      map(( response ) => {
        return response.data.map(
          (raw) => AccountingModel.toInterface(raw)
        );
      })
    );
  }

  uploadAccountingInfo(accountingUploadInfo: AccountingUploadInfo): Observable<any>{
    const body =  new FormData();
    body.append('accountingInfo',accountingUploadInfo.file);
    body.append('startDate',accountingUploadInfo.startDate);
    body.append('endDate',accountingUploadInfo.endDate);

    return this.httpClient.post(`${this.baseUrl}/uploadAccountingInfo`, body);
  }

  deleteLastHeader(accountingHeader: AccountingHeader): Observable<GenericResponse>{
    const body = {
      'id': accountingHeader.id,
      'startDate': accountingHeader.startDate,
      'endDate': accountingHeader.endDate,
    };

    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/deleteLastUpload`, body);

  }
}
