import React from 'react'
import Compact from '@uiw/react-color-compact';
import * as uuid from 'uuid';
import { Input, InputProps, InputState } from '../Input'

export default class Color extends Input<InputProps, InputState> {
  static defaultProps = {
    inputClassName: 'color',
    id: uuid.v4(),
  }

  constructor(props: InputProps) {
    super(props);

    this.state = {
      ...this.state, // Parent state
      isInitialized: true,
    };
  }

  renderValueElement(): JSX.Element {
    if (this.state.value) {
      return <span style={{backgroundColor: this.state.value}}>&nbsp;&nbsp;&nbsp;&nbsp;</span>
    } else {
      return <span style={{border: '1px solid #EEEEEE'}}>&nbsp;&nbsp;&nbsp;&nbsp;</span>
    }
  }

  renderInputElement() {
    return <>
      <div style={{background: this.state.value, width: '1.5em', height: '1.5em'}} className="mr-2"></div>
      <div className="no-scrollbar" style={{height: '2.75em', overflow: 'auto'}}>
        <Compact
          color={this.state.value}
          style={{
            boxShadow: 'rgb(0 0 0 / 15%) 0px 0px 0px 1px, rgb(0 0 0 / 15%) 0px 8px 16px',
          }}
          onChange={(color: any) => this.onChange(color.hex)}
          // rectRender={(props) => {
          //   console.log(props.key)
          //   if (props.key == 35) {
          //     return <button key={props.key} style={{ width: 15, height: 15, padding: 0, lineHeight: "10px" }} onClick={() => setHex(null)}>x</button>
          //   }
          // }}
        />
      </div>
    </>;
  } 
}
