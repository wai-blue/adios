import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import * as uuid from 'uuid';
import Modal, { ModalProps } from "./Modal";

export default class ModalSimple extends Modal {
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
            {this.props.showHeader ? <>
              <div className="modal-header">
                <div className="modal-header-left">{this.props.headerLeft}</div>
                <div className="modal-header-title">{this.props.title}</div>
                <div className="modal-header-right">
                  <button
                    className="btn btn-close"
                    type="button"
                    data-dismiss="modal"
                    aria-label="Close"
                    onClick={() => {
                      if (this.props.onClose) this.props.onClose(this);
                    }}
                  ><span className="icon"><i className="fas fa-xmark"></i></span></button>
                </div>
              </div>
            </> : null}
            <div className="modal-body">{this.props.children}</div>
          </div>
        </div>
      </>;
    } else {
      return <></>;
    }
  } 
}
