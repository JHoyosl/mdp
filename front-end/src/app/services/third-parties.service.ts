import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from 'src/environments/environment';
import { ThirdPartyAccount, ThirdPartyAccountInfoUpload, ThirdPartyAccountResponse, ThirdPartyHeaderInfo, ThirdPartyHeaderInfoResponse, ThirdPartyHeaderItems, ThirdPartyHeaderItemsResponose } from '../Interfaces/thirdParties.interface';
import { map } from 'rxjs/operators';
import { GenericResponse } from '../Interfaces/shared.interfaces';
import { thirdPartiesModel } from '../models/third-parties.model';

@Injectable({
  providedIn: 'root'
})
export class ThirdPartiesService {

  private baseUrl = `${environment.url}thirdParties`;

  constructor(private httpClient: HttpClient) { }

  getThirdPartiesAccounts(): Observable<ThirdPartyAccount[]> {

    return this.httpClient.get<ThirdPartyAccountResponse>(this.baseUrl)
      .pipe(
        map((response) => {
          return response.data;
        })
      );
  }

  getAccountHeaderInfo(accountId: number ): Observable<ThirdPartyHeaderInfo[]>{

    const params = new HttpParams().append('accountId', accountId.toString() );

    return this.httpClient.get<ThirdPartyHeaderInfoResponse>(
      `${this.baseUrl}/getAccountHeaderInfo`, { params } )
    .pipe(
      map((response) => response.data.map( 
        (thirParty) => thirdPartiesModel.thirdPartiesToInterface(thirParty)))
      );
  }

  getThirdPartyItems( header: ThirdPartyHeaderInfo ): Observable<ThirdPartyHeaderItems[]> {
    const params = new HttpParams()
      .append('headerId', header.id.toString());

    return this.httpClient.get<ThirdPartyHeaderItemsResponose>(`${this.baseUrl}/getHeaderItems`, { params })
      .pipe( map((response) => response.data.map(
        (row) => thirdPartiesModel.thirdPartyHeaderItemsToInterface(row)
      )));
  }


  uploadThirdPartiesInfo(thirdPartyAccountInfoUpload: ThirdPartyAccountInfoUpload ):  Observable<GenericResponse>{
    const body = new FormData();
    body.append('accountId',thirdPartyAccountInfoUpload.accountId);
    body.append('file',thirdPartyAccountInfoUpload.file);
    body.append('startDate',thirdPartyAccountInfoUpload.startDate);
    body.append('endDate',thirdPartyAccountInfoUpload.endDate);

    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/uploadAccountInfo`, body);

  }

  deleteLastUpload(thirdPartyAccount: ThirdPartyHeaderInfo){
    
    const body = new HttpParams()
      .append('accountId', thirdPartyAccount.accountId.toString())
      .append('headerId', thirdPartyAccount.id.toString())
      .append('startDate', thirdPartyAccount.startDate)
      .append('endDate', thirdPartyAccount.endDate);
    
    return this.httpClient.post<GenericResponse>(`${this.baseUrl}/deleteLastUpload`, body);
  }
}
