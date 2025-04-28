import React, { Component } from 'react'
import * as uuid from 'uuid';
import Form from './Form';

export interface TableCellRendererDescription {
}

export interface TableCellRendererProps {
  columnName: string,
  column: any,
  data: Array<any>,
  options: any,
}

export interface TableCellRendererState {
  columnName: string,
  column: any,
  data: Array<any>,
  options: any,
}

export class TableCellRenderer<P extends TableCellRendererProps, S extends TableCellRendererState> extends Component<P, S> {
  state: S;

  constructor(props: P) {
    super(props);

    this.state = {
      columnName: this.props.columnName,
      column: this.props.column,
      data: this.props.data,
      options: this.props.options,
    } as S;
  }

  componentDidUpdate(prevProps: any): void {
    let newState: any = {};
    let setNewState: boolean = false;

    if (this.props.columnName != prevProps.columnName) {
      newState.columnName = this.props.columnName;
      setNewState = true;
    }

    if (this.props.column != prevProps.column) {
      newState.column = this.props.column;
      setNewState = true;
    }

    if (this.props.data != prevProps.data) {
      newState.data = this.props.data;
      setNewState = true;
    }

    if (this.props.options != prevProps.options) {
      newState.options = this.props.options;
      setNewState = true;
    }

    if (setNewState) {
      this.setState(newState);
    }
  }

  render() {
    return this.state.data[this.state.columnName];
  }
}
