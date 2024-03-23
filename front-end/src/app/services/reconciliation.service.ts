import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { ReconciliationItem } from '../Interfaces/reconciliation.interface';
import { GenericResponse } from '../Interfaces/shared.interfaces';
import { ReconciliationModel } from '../models/reconciliation.model';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class ReconciliationService {

  private baseUrl = `${environment.url}reconciliation`;

  constructor(private httpClient: HttpClient) { }

  getAccountProcess(): Observable<ReconciliationItem[]> {
    
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/getAccountProcess`)
      .pipe(
        map((response) => response.data.map((obj) => ReconciliationModel.AccountProcessToInterface(obj)))
      )
      
      
  }
}
