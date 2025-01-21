import React, { Component } from 'react';
import axios, { AxiosError, AxiosResponse } from "axios";
import ApiError from './Request';
import ApiResponse from './Request';

class Dispatch {


  public get<T>(
    url: string,
    queryParams: Record<string, any>,
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    document.body.classList.add("ajax-loading");
    axios.get<T, AxiosResponse<ApiResponse<T>>>(url, {
      params: queryParams
    }).then(res => {
      const responseData: ApiResponse<T> = res.data;
      document.body.classList.remove("ajax-loading");
      if (successCallback) successCallback(responseData);
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
    axios.post<T, AxiosResponse<ApiResponse<T>>>(url, postData, {
      params: queryParams
    }).then(res => {
      const responseData: ApiResponse<T> = res.data;
      document.body.classList.remove("ajax-loading");
      if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public put<T>(
    url: string,
    putData: Record<string, any>,
    queryParams?: Record<string, string>|{},
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    axios.put<T, AxiosResponse<ApiResponse<T>>>(url, putData, {
      params: queryParams
    }).then(res => {
      const responseData: ApiResponse<T> = res.data;
      if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public patch<T>(
    url: string,
    patchData: Record<string, any>,
    queryParams?: Record<string, string>|{},
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    axios.patch<T, AxiosResponse<ApiResponse<T>>>(url, patchData, {
      params: queryParams
    }).then(res => {
      const responseData: ApiResponse<T> = res.data;
      if (successCallback) successCallback(responseData);
    }).catch((err: AxiosError<ApiError>) => this.catchHandler(url, err, errorCallback));
  }

  public delete<T>(
    url: string,
    queryParams: Record<string, any>,
    successCallback?: (data: ApiResponse<T>) => void,
    errorCallback?: (data: any) => void,
  ): void {
    axios.delete<T, AxiosResponse<ApiResponse<T>>>(url, {
      params: queryParams
    }).then(res => {
      const responseData: ApiResponse<T> = res.data;
      if (successCallback) successCallback(responseData);
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
      console.error('ADIOS: Dispatch @ ' + url + ' unknown error.');
      console.error(err);
      this.fatalErrorNotification("Unknown error");
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

const dispatch = new Dispatch();
export default dispatch;
