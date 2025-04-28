import React, { Component } from 'react'
import { TableCellRenderer, TableCellRendererProps, TableCellRendererState } from '../TableCellRenderer'

export default class HyperlinkTableCellRenderer extends TableCellRenderer<TableCellRendererProps, TableCellRendererState> {
  render() {
    if (this.state.data && this.state.data[this.state.columnName]) {
      return <>
        <a
          href={this.state.data[this.state.columnName]}
          target='_blank'
          // onClick={(e) => { e.stopPropagation(); }}
          className="btn btn-blue-outline btn-small"
        >
          <span className="icon"><i className="fa-solid fa-up-right-from-square"></i></span>
          <span className="text">{this.state.data[this.state.columnName]}</span>
        </a>
      </>
    } else {
      return null;
    }
  }
}
