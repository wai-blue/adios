import React, { Component } from 'react'
import { Input, InputProps, InputState } from '../Input'
import Varchar from './Varchar'
import * as uuid from 'uuid';

interface TelInputState extends InputState {
  showPredefinedValues: boolean,
}

export default class Tel extends Varchar<InputProps, TelInputState> {
  static defaultProps = {
    inputClassName: 'tel',
    id: uuid.v4(),
    type: 'text',
    placeholder: '+1 AAA-BBBBB',
  }

  constructor(props: InputProps) {
    super(props);

    this.state = this.getStateFromProps(props);
  }

  getStateFromProps(props: InputProps) {
    return {
      ...this.state, // Parent state
      showPredefinedValues: false,
    };
  }

  renderValueElement() {
    return (this.state.value ? <>
      <a
        href={"tel:" + this.state.value}
        target='_blank'
        onClick={(e) => { e.stopPropagation(); }}
        className="btn btn-transparent btn-small"
      >
        <span className="icon"><i className="fa-solid fa-phone"></i></span>
        <span className="text">{this.state.value}</span>
      </a>
    </> : <></>);
  }

}
