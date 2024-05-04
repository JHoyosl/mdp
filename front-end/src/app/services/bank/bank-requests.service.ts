import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Bank } from 'src/app/Interfaces/bank.interface';
import { GenericResponse } from 'src/app/Interfaces/shared.interfaces';
import { BankModel } from 'src/app/models/bank.model';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root'
})
export class BankRequestsService {

  private baseUrl = `${environment.url}banks`;

  constructor(private httpClient: HttpClient) { }

  index(): Observable<Bank[]>{
    return this.httpClient.get<GenericResponse>(this.baseUrl).pipe(
      map((response) => {
        const data = response.data;
        return data.map((bank:any) => BankModel.toInterface(bank));
      }));
  }
}
