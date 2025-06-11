import React from 'react'
import { Input, InputProps, InputState } from '../Input'
import * as uuid from 'uuid';

interface IntInputProps extends InputProps {
  unit?: string
}

export default class Int extends Input<IntInputProps, InputState> {
  static defaultProps = {
    inputClassName: 'int',
    id: uuid.v4(),
  }

  constructor(props: InputProps) {
    super(props);

    this.state = {
      ...this.state, // Parent state
      isInitialized: true,
    };
  }

  renderInputElement() {
    const decimals = this.props.description?.decimals ?? 0;
    return <input
      ref={this.refInput}
      type="number"
      value={this.state.value}
      onKeyDown={(evt) => evt.key === 'e' && evt.preventDefault()}
      onChange={(e) => this.setState({value: e.currentTarget.value.replace('e', '')})}
      onBlur={(e) => this.onChange(e.currentTarget.value.replace('e', ''))}
      placeholder={this.props.description?.placeholder ?? '0' + (decimals > 0 ? '.' + '0'.repeat(decimals) : '')}
      className={
        "form-control"
        + " " + (this.state.invalid ? 'is-invalid' : '')
        + " " + (this.props.cssClass ?? "")
        + " " + (this.state.readonly ? "bg-muted" : "")
      }
      disabled={this.state.readonly}
    />;
  }
}
