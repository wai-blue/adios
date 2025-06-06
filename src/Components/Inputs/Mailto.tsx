import React, { Component } from 'react'
import { Input, InputProps, InputState } from '../Input'
import Varchar from './Varchar'
import * as uuid from 'uuid';

interface MailtoInputState extends InputState {
  showPredefinedValues: boolean,
}

export default class Mailto extends Varchar<InputProps, MailtoInputState> {
  static defaultProps = {
    inputClassName: 'mailto',
    id: uuid.v4(),
    type: 'text',
    placeholder: '@',
  }

  constructor(props: InputProps) {
    super(props);

    this.state = this.getStateFromProps(props);
  }

  getStateFromProps(props: InputProps) {
    return {
      ...this.state, // Parent state
      showPredefinedValues: false,
      isInitialized: true,
    };
  }

  renderValueElement() {
    return (this.state.value ? <>
      <a
        href={"mailto://" + this.state.value}
        target='_blank'
        onClick={(e) => { e.stopPropagation(); }}
        className="btn btn-transparent btn-small"
      >
        <span className="icon"><i className="fa-solid fa-at"></i></span>
        <span className="text">{this.state.value}</span>
      </a>
    </> : <></>);
  }

}
