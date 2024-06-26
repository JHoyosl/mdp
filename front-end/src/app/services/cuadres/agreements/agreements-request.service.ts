import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';
import { AgreementsService } from './agreements.service';
import { Observable } from 'rxjs';
import { AgreeementsResult, AgreementsHeader, AgreementsRequestUpload } from 'src/app/Interfaces/cuadres.interface';
import { GenericResponse } from 'src/app/Interfaces/shared.interfaces';
import { map } from 'rxjs/operators';

@Injectable({
  providedIn: 'root'
})
export class AgreementsRequestService {

  private baseUrl = `${environment.url}cuadresOperativos/agreements`;
  
  constructor(
    private httpClient: HttpClient, 
    private agreementsService: AgreementsService
  ) { }

  getAgreementsIndex():Observable<AgreementsHeader[]>{
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}`).pipe(
      map((response) => response.data )
    );
  }

  getAgreementsResult(date: string, overwrite: boolean = false):Observable<AgreeementsResult[]> {
    const params = new HttpParams()
      .append('date', date)
      .append('overwrite', `${overwrite}`);
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/result`,{params}).pipe(
      map((response) => response.data )
    );
  }

  uploadAgreements(data: AgreementsRequestUpload):Observable<AgreementsHeader>{
    const formData = new FormData();
    formData.append('date', data.date);
    formData.append('file', data.file);

    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/upload`,formData).pipe(
      map((response) => response.data)
    );
  }

  deleteAgreements(id: number):Observable<string>{
    return this.httpClient.delete<GenericResponse>(`${this.baseUrl}/${id}`).pipe(
      map(response => response.data)
    )
  }

  downloadBalanceResult(date: string){
    const params = new HttpParams()
      .append('date', date);

    return this.httpClient
      .get(`${this.baseUrl}/downloadResult`,{params, responseType:'arraybuffer'});
  }
}
