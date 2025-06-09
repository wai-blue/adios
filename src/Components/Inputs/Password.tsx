import React, { Component } from 'react'
import { Input, InputProps, InputState } from '../Input'
import * as uuid from 'uuid';

interface PasswordInputProps extends InputProps {
}

interface PasswordInputState extends InputState {
  visible: boolean,
}

export default class Password extends Input<PasswordInputProps, PasswordInputState> {
  static defaultProps = {
    inputClassName: 'password',
    id: uuid.v4(),
    type: 'text',
  }

  refInputConfirm: any;

  constructor(props: PasswordInputProps) {
    super(props);

    this.state = {
      ...this.state,
      value: '',
      visible: false,
      isInitialized: true,
    };

    this.refInputConfirm = React.createRef();
  }

  onChange() {
    const val1 = this.refInput.current.value;
    const val2 = this.refInputConfirm.current.value;
    super.onChange([val1, val2]);
  }

  renderValueElement() {
    return <span>***</span>;
  }

  renderInputElement() {
    const password1 = this.state.value[0] ?? '';
    const password2 = this.state.value[1] ?? '';

    return <>
      <div className={"block pr-2"}>
        <input
          ref={this.refInput}
          type={this.state.visible ? 'text' : 'password'}
          value={password1}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => this.onChange()}
          placeholder={this.translate("New password", 'ADIOS\\Core\\Loader::Components\\Inputs\\Password')}
          className={
            (this.state.invalid ? 'is-invalid' : '')
            + " " + (this.props.cssClass ?? "")
            + " " + (this.state.readonly ? "bg-muted" : "")
            + " " + (password1 == password2 ? "" : "bg-red-100")
          }
          disabled={this.state.readonly}
        />
        <input
          type={this.state.visible ? 'text' : 'password'}
          value={password2}
          ref={this.refInputConfirm}
          onChange={(e: React.ChangeEvent<HTMLInputElement>) => this.onChange()}
          placeholder={this.translate("Confirm new password", 'ADIOS\\Core\\Loader::Components\\Inputs\\Password')}
          className={
            (this.state.invalid ? 'is-invalid' : '')
            + " " + (this.props.cssClass ?? "")
            + " " + (this.state.readonly ? "bg-muted" : "")
            + " " + (password1 == password2 ? "" : "bg-red-100")
          }
          disabled={this.state.readonly}
        />
      </div>
      <span
        className="btn btn-light"
        onClick={() => { this.setState({visible: !this.state.visible}); }}
      >
        <span className="icon"><i className={"fas " + (this.state.visible ? "fa-low-vision" : "fa-eye")}></i></span>
      </span>
    </>;
  }
}
