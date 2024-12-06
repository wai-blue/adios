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
    return <>
      <a href={this.state.value} target='_blank' onClick={(e) => { e.stopPropagation(); }}>{this.state.value}</a>
      <i className="ml-2 fa-solid fa-up-right-from-square"></i>
    </>;
  }

}
