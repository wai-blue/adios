import React, { Component } from 'react'
import { Input, InputProps, InputState } from '../Input'
import * as uuid from 'uuid';
import AsyncSelect from 'react-select/async'
import AsyncCreatable from 'react-select/async-creatable'
import request from '../Request'

interface VarcharInputState extends InputState {
  data: Array<any>,
  showPredefinedValues: boolean,
}

export default class Varchar<P, S> extends Input<InputProps, VarcharInputState> {
  static defaultProps = {
    inputClassName: 'varchar',
    id: uuid.v4(),
    type: 'text',
  }

  constructor(props: InputProps) {
    super(props);

    this.state = this.getStateFromProps(props);
  }

  getStateFromProps(props: InputProps) {
    return {
      ...this.state, // Parent state
      data: [],
      showPredefinedValues: true,
    };
  }

  componentDidMount() {
    super.componentDidMount();
    if (this.props.description?.autocomplete) {
      this.loadData();
    }
  }

  componentDidUpdate(prevProps: InputProps) {
    super.componentDidUpdate(prevProps);

    if (
      this.props.description?.autocomplete
      && this.props.description?.autocomplete.endpoint != prevProps.description?.autocomplete?.endpoint
    ) {
      this.loadData();
    }
  }

  getEndpointUrl(): string
  {
    return this.state.description?.autocomplete?.endpoint ?? '';
  }

  loadData(inputValue: string|null = null, callback: ((option: Array<any>) => void)|null = null) {
    request.post(
      this.getEndpointUrl(),
      {search: inputValue},
      {},
      (data: any) => {
        let dataConv: Array<any> = [];

        for (let i in data) {
          dataConv.push({'label': data[i], 'value': data[i]});
        }

        this.setState({
          isInitialized: true,
          data: dataConv
        });

        if (callback) callback(dataConv);
      }
    );
  }

  renderInputElement() {

    if (this.props.description?.autocomplete) {
      let selectProps = {
        value: {
          label: this.state.value ?? '',
          value: this.state.value ?? '',
        },
        isClearable: true,
        isDisabled: this.state.readonly || !this.state.isInitialized,
        loadOptions: (inputValue: string, callback: any) => this.loadData(inputValue, callback),
        defaultOptions: this.state.data,
        getOptionLabel: (option: any) => { return option.label },
        getOptionValue: (option: any) => { return option.value },
        onChange: (item: any) => { this.onChange(item?.value ?? ''); },
        placeholder: this.props.description?.placeholder,
        className: 'adios-lookup',
        styles: { menuPortal: (base) => ({ ...base, zIndex: 9999 }) },
        // menuPosition: 'fixed',
        menuPortalTarget: document.body,
      }

      if (this.props.description?.autocomplete.creatable) return <AsyncCreatable {...selectProps} />;
      else return <AsyncSelect {...selectProps} />;
    } else {
    
      return <div className="flex gap-2 w-full">
        <input
          type='text'
          value={this.state.value ?? ''}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => this.onChange(e.currentTarget.value)}
          placeholder={this.props.placeholder}
          className={
            (this.state.invalid ? 'is-invalid' : '')
            + " " + (this.props.cssClass ?? "")
            + " " + (this.state.readonly ? "bg-muted" : "")
          }
          disabled={this.state.readonly}
        />
        {this.props.description?.predefinedValues ?
          this.state.showPredefinedValues ?
            <div className="mt-1">
              <select
                onChange={(e) => {
                  this.onChange(e.currentTarget.value);
                }}
              >
                <option value=''></option>
                {this.props.description?.predefinedValues.map((item: string, index: any) => {
                  return <option key={index} value={item}>{item}</option>
                })}
              </select>
            </div>
          :
            <button className="mt-1 btn btn-transparent" onClick={() => { this.setState({showPredefinedValues: true}); }}>
              <span className="text">Choose from predefined options...</span>
            </button>
        : null}
      </div>;
    }
  }
}
