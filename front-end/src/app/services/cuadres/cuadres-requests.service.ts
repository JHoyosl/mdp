import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, from, observable, of } from 'rxjs';
import { map } from 'rxjs/operators';
import { BalanceList, BalanceResultResponse, uploadCuadreRequest } from 'src/app/Interfaces/cuadres.interface';
import { GenericResponse } from 'src/app/Interfaces/shared.interfaces';
import { environment } from 'src/environments/environment';
import { CuadresService } from './cuadres.service';
import Swal from 'sweetalert2';

@Injectable({
  providedIn: 'root'
})
export class CuadresRequestsService {
  
  private baseUrl = `${environment.url}cuadresOperativos`;
  
  constructor(
    private httpClient: HttpClient, 
    private cuadresService: CuadresService
  ) { }

  getBalanceIndex(): Observable<BalanceList[]> {
    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/balanceGeneral`).pipe(
      map(response => {
        this.cuadresService.setBalanceList(response.data);
        return response.data;
      })
    );
  }

  getBalanceResult(date: string):Observable<BalanceResultResponse>{
    const params = new HttpParams()
      .append('date',date);

    return this.httpClient.get<GenericResponse>(`${this.baseUrl}/balanceGeneral/getBalanceNaturaleza`,{params}).pipe(
      map( result => result.data));
  }

  uploadCuadreInfo(data: uploadCuadreRequest):Observable<any>{
    const formData = new FormData();
    formData.append('date', data.date);
    formData.append('file', data.file);
    
    const url = `${this.baseUrl}/balanceGeneral/uploadBalance`;

    return this.httpClient.post(url,formData);
  }

  deletBalanceUploaded(id: string):Observable<boolean>{
    return this.httpClient.delete<GenericResponse>(`${this.baseUrl}/balanceGeneral/${id}`).pipe(
      map((response) => {
        if(response.data === 'success'){
          return true;
        }
        return false;
      })
    )
  }
}
