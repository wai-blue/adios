import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import * as uuid from 'uuid';
import Modal, { ModalProps } from "./Modal";

export default class ModalForm extends Modal {
  static defaultProps = {
    type: 'centered',
  }

  render(): JSX.Element {
    if (this.state.isOpen) {
      return <>
        <div
          key={this.props.uid}
          id={"adios-modal-" + this.props.uid}
          className={"modal " + this.props.type}
        >
          <div className="modal-inner">
            {this.props.children}
          </div>
        </div>
      </>;
    } else {
      return <></>;
    }
  } 
}
