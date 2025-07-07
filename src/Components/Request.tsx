import React, { Component } from 'react';
import axios, { AxiosError, AxiosResponse } from "axios";
import Swal, {SweetAlertOptions} from "sweetalert2";

interface ApiResponse<T> {
  data: T;
}

interface ApiError {
  message: string;
}

class Request {

  getAppUrl(): string {
    if (!globalThis.app.config.appUrl) {
      console.warn('ADIOS.Request: appUrl is not set. Your AJAX requests might not work. To suppress this warning, set appUrl to empty value.')
      console.warn('To set the value add a script tag in HTML head section and set window.configEnv.appUrl..')
      console.warn('To suppress this warning, set may set appUrl to an empty value.')
    };

    return globalThis.app.config.appUrl + '/';
  }

  alertOnError(responseData: any) {
    Swal.fire({
      title: '<div style="text-align:left">🥴 Ooops</div>',
      html: responseData.errorHtml,
      width: '80vw',
      padding: '1em',
      color: "#ad372a",
      background: "white",
      backdrop: `rgba(123,12,0,0.2)`
    });
    // Notification.error(response.error);
  }

  public get<T>(
    url: string,
    queryParams: Record<string, any>,
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    document.body.classList.add("ajax-loading");
    axios.get<T, AxiosResponse<ApiResponse<T>>>(this.getAppUrl() + url, {
      params: queryParams
    }).then(res => {
      const responseData: any = res.data;
      document.body.classList.remove("ajax-loading");
      if (responseData.errorHtml) this.alertOnError(responseData);
      else if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public post<T>(
    url: string,
    postData: Record<string, any>,
    queryParams?: Record<string, string>|{},
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    document.body.classList.add("ajax-loading");
    axios.post<T, AxiosResponse<ApiResponse<T>>>(this.getAppUrl() + url, postData, {
      params: queryParams
    }).then(res => {
      const responseData: any = res.data;
      document.body.classList.remove("ajax-loading");
      if (responseData.errorHtml) this.alertOnError(responseData);
      else if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public put<T>(
    url: string,
    putData: Record<string, any>,
    queryParams?: Record<string, string>|{},
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    axios.put<T, AxiosResponse<ApiResponse<T>>>(this.getAppUrl() + url, putData, {
      params: queryParams
    }).then(res => {
      const responseData: any = res.data;
      if (responseData.errorHtml) this.alertOnError(responseData);
      else if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public patch<T>(
    url: string,
    patchData: Record<string, any>,
    queryParams?: Record<string, string>|{},
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    axios.patch<T, AxiosResponse<ApiResponse<T>>>(this.getAppUrl() + url, patchData, {
      params: queryParams
    }).then(res => {
      const responseData: any = res.data;
      if (responseData.errorHtml) this.alertOnError(responseData);
      else if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public delete<T>(
    url: string,
    queryParams: Record<string, any>,
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    axios.delete<T, AxiosResponse<ApiResponse<T>>>(this.getAppUrl() + url, {
      params: queryParams
    }).then(res => {
      const responseData: any = res.data;
      if (responseData.errorHtml) this.alertOnError(responseData);
      else if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  private catchHandler(
    url: string,
    err: AxiosError<ApiError>,
    errorCallback?: (data: any) => void
  ) {
    if (err.response) {
      if (err.response.status == 500) {
        this.fatalErrorNotification(err.response.data);
      } else {
        this.fatalErrorNotification(err.response.data);
        console.error('ADIOS: ' + err.code, err.config?.url, err.config?.params, err.response.data);
        if (errorCallback) errorCallback(err.response);
      }
    } else {
      console.error('ADIOS: Request @ ' + url + ' unknown error.');
      console.error(err);
      // this.fatalErrorNotification("Unknown error");
    }
  }

  private fatalErrorNotification(error: any) {
    if (typeof error == 'string') {
      globalThis.app.showDialogDanger(error);
    } else {
      globalThis.app.showDialogDanger(globalThis.app.makeErrorResultReadable(error));
    }
  }

}

const request = new Request();
export default request;
