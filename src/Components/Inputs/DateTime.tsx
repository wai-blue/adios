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
    + " " + ('0' + d.getHours()).slice(-2)
    + ":" + ('0' + d.getMinutes()).slice(-2)
    + ":" + ('0' + d.getSeconds()).slice(-2)
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

  renderReadableInfo(value: any) {
    let days = moment(value).diff(moment(), 'days');
    return <>
      <div className="text-gray-400">{
        days < -365 ? "(more than a year ago)" :
        days < -30*6 ? "(more than 6 months ago)" :
        days < -30*3 ? "(more than 3 months ago)" :
        days < -30 ? "(more than a month ago)" :
        days < -14 ? "(more than 2 weeks ago)" :
        days < -7 ? "(more than a week ago)" :
        days < -1 ? "(" + (-days) + " days ago)" :
        days == -1 ? "(yesterday)" :
        days == 0 ? "(today)" :
        days == 1 ? "(tomorrow)" :
        days > 365 ? "(in a year)" :
        days > 30*6 ? "(in 6-12 months)" :
        days > 30*3 ? "(in 3-6 months)" :
        days > 30 ? "(in 1-3 months)" :
        days > 14 ? "(in 2-4 weeks)" :
        days > 7 ? "(in 1-2 weeks)" :
        days > 1 ? "(in " + days + " days)" :
        null
      }</div>
    </>;
  }

  renderValueElement() {
    let value = this.state.value;

    if (value) {
      switch (this.props.type) {
        case 'datetime':
          value = moment(value).format('DD.MM.YYYY H:mm:s');
        break;
        case 'date':
          value = moment(value).format('DD.MM.YYYY');
        break;
      }

      return <div className="flex gap-2 items-center">
        <i className="fas fa-calendar-days mr-2"></i>
        {value}
        {this.renderReadableInfo(value)}
      </div>
    } else {
      return super.renderValueElement();
    }
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

    return <>
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
      {this.renderReadableInfo(this.state.value)}
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
    </>;
  }
}
