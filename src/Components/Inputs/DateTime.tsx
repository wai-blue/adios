import React, { Component } from 'react'
import { Input, InputProps, InputState } from '../Input'
import "flatpickr/dist/themes/material_blue.css";
import Flatpickr from "react-flatpickr";
import moment, { Moment } from "moment";
import * as uuid from 'uuid';

export const dateToEUFormat = (dateString: string): string => {
  if (!dateString || dateString.length != 10) {
    return '';
  } else {
    let d = new Date(dateString);

    return ('0' + d.getDate()).slice(-2) + "."
      + ('0' + (d.getMonth() + 1)).slice(-2)
      + "." + d.getFullYear()
      ;
  }
}

export const datetimeToEUFormat = (dateString: string): string => {
  let d = new Date(dateString);

  return ('0' + d.getDate()).slice(-2) + "."
    + ('0' + (d.getMonth() + 1)).slice(-2)
    + "." + d.getFullYear()
    + " " + ('0' + d.getHours()).slice(-2) + ":" + ('0' + d.getMinutes()).slice(-2)
    ;
}

interface DateTimeInputProps extends InputProps {
  type: 'date' | 'time' | 'datetime',
}

export default class DateTime extends Input<DateTimeInputProps, InputState> {
  static defaultProps = {
    inputClassName: 'datetime',
    id: uuid.v4(),
  }

  fp: any

  options: any = {
    allowInput: false,
    locale: {
      weekdays: {
        shorthand: ['Ne.', 'Po.', 'Ut.', 'St.', 'Št.', 'Pi.', 'So.'],
        longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
      },
      months: {
        shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Máj', 'Jún', 'Júl', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
        longhand: ['Január', 'Február', 'Marec', 'Apríl', 'Máj', 'Jún', 'Júl', 'August', 'September', 'Október', 'November', 'December']
      },
      weekStart: 1
    }
  };

  constructor(props: DateTimeInputProps) {
    super(props);

    this.fp = React.createRef();

    switch (props.type) {
      case 'datetime':
        this.options = {...this.options, enableTime: true, showMonths: 2, dateFormat: 'd.m.Y H:m:s'};
      break;
      case 'date':
        this.options = {...this.options, showMonths: 2, weekNumbers: true, dateFormat: 'd.m.Y'};
      break;
      case 'time':
        this.options = {
          ...this.options,
          ...{
            dateFormat: 'H:m',
            enableTime: true,
            noCalendar: true,
            time_24hr: true,
            minuteIncrement: 15,
            showMonths: 2,
          }
        };
      break;
    }
  }

  onChange(value: any) {
    if (value === null) {
      value = '';
    } else if (value != '') {
      switch (this.props.type) {
        case 'datetime':
          value = moment(value).format('YYYY-MM-DD H:mm:s');
        break;
        case 'date':
          value = moment(value).format('YYYY-MM-DD');
        break;
        case 'time':
          value = moment(value).format('HH:mm');
        break;
      }
    }

    super.onChange(value);
  }

  renderValueElement() {
    let value = this.state.value;

    switch (this.props.type) {
      case 'datetime':
        value = moment(value).format('DD.MM.YYYY H:mm:s');
      break;
      case 'date':
        value = moment(value).format('DD.MM.YYYY');
      break;
    }

    return value;
  }

  renderInputElement() {
    let value: any = this.state.value;
    let defaultPlaceholder;

    switch (this.props.type) {
      case 'datetime':
        value = datetimeToEUFormat(this.state.value);
        defaultPlaceholder = 'Year-Month-Day Hour:Min:Sec';
      break;
      case 'date':
        value = dateToEUFormat(this.state.value);
        defaultPlaceholder = 'Year-Month-Day';
      break;
      case 'time':
        this.options = {
          ...this.options,
          ...{
            dateFormat: 'H:i',
            enableTime: true,
            noCalendar: true,
            time_24hr: true,
            minuteIncrement: 15
          }
        };
        defaultPlaceholder = 'Hour:Min:Sec';
      break;
    }

    return (
      <>
        <div style={{minWidth: "8em"}}>
          <Flatpickr
            ref={this.fp}
            value={value}
            onChange={(data: Date[]) => {
              this.onChange(data[0] ?? null)
            }}
            className={
              (this.state.invalid ? 'is-invalid' : '')
              + " " + (this.props.cssClass ?? "")
              + " " + (this.state.readonly ? "bg-muted" : "")
            }
            placeholder={this.props.description?.placeholder ?? defaultPlaceholder}
            disabled={this.state.readonly}
            options={this.options}
          />
        </div>
        {this.state.readonly ? null :
          <button
            className="btn btn-small btn-transparent ml-2"
            onClick={() => {
              if (!this.fp?.current?.flatpickr) return;
              this.fp.current.flatpickr.clear();
            }}
          >
            <span className="icon"><i className="fas fa-times"></i></span>
          </button>
         }
      </>
    );
  }
}
