import React, { Component } from 'react'
import { Input, InputProps, InputState } from '../Input'
import Varchar from './Varchar'
import * as uuid from 'uuid';

interface HyperlinkInputState extends InputState {
  showPredefinedValues: boolean,
}

export default class Hyperlink extends Varchar<InputProps, HyperlinkInputState> {
  static defaultProps = {
    inputClassName: 'hyperlink',
    id: uuid.v4(),
    type: 'text',
    placeholder: 'https://',
  }

  constructor(props: InputProps) {
    super(props);

    this.state = this.getStateFromProps(props);
  }

  getStateFromProps(props: InputProps) {
    return {
      ...super.getStateFromProps(props), // Parent state
      showPredefinedValues: false,
      isInitialized: true,
    };
  }

  renderValueElement() {
    if (this.state.value) {
      return <>
        <a
          href={this.state.value}
          target='_blank'
          onClick={(e) => { e.stopPropagation(); }}
          className="btn btn-blue-outline btn-small max-w-60"
        >
          <span className="icon"><i className="fa-solid fa-up-right-from-square"></i></span>
          <span className="text">{this.state.value ? this.state.value : ''}</span>
        </a>
        <button className="btn btn-transparent btn-small ml-2">
          <span className="icon"><i className="fa-solid fa-pencil"></i></span>
        </button>
      </>;
    } else {
      return <span className="no-value"></span>;
    }
  }

  renderInputElement() {
    return <div className="w-full flex gap-2">
      {super.renderInputElement()}
      <a
        href={this.state.value}
        target='_blank'
        onClick={(e) => { e.stopPropagation(); }}
        className="btn btn-transparent"
      >
        <span className="icon"><i className="fa-solid fa-up-right-from-square"></i></span>
      </a>
    </div>
  }
}
