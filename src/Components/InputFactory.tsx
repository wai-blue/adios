import React, { Component } from 'react'

import ReactQuill, {Value} from 'react-quill';
import 'react-quill/dist/quill.snow.css';

import InputLookup from "./Inputs/Lookup";
import InputVarchar from "./Inputs/Varchar";
import InputPassword from "./Inputs/Password";
import InputTextarea from "./Inputs/Textarea";
import InputInt from "./Inputs/Int";
import InputBoolean from "./Inputs/Boolean";
import InputColor from "./Inputs/Color";
import InputFile from "./Inputs/File";
import InputImage from "./Inputs/Image";
import InputTags from "./Inputs/Tags";
import InputDateTime from "./Inputs/DateTime";
import InputEnumValues from "./Inputs/EnumValues";

export function InputFactory(inputProps: any): JSX.Element {
  let inputToRender: JSX.Element = <></>;

  if (inputProps.description.enumValues) {
    inputToRender = <InputEnumValues {...inputProps} enumValues={inputProps.description.enumValues} enumCssClasses={inputProps.description.enumCssClasses}/>
  } else {
    if (typeof inputProps.description.reactComponent === 'string' && inputProps.reactComponent !== '') {
      inputToRender = globalThis.app.renderReactElement(inputProps.description.reactComponent, inputProps) ?? <></>;
    } else {
      switch (inputProps.description.type) {
        case 'varchar': inputToRender = <InputVarchar {...inputProps} />; break;
        case 'password': inputToRender = <InputPassword {...inputProps} />; break;
        case 'text': inputToRender = <InputTextarea {...inputProps} />; break;
        case 'float': case 'int': inputToRender = <InputInt {...inputProps} />; break;
        case 'boolean': inputToRender = <InputBoolean {...inputProps} />; break;
        case 'lookup': inputToRender = <InputLookup {...inputProps} />; break;
        case 'color': inputToRender = <InputColor {...inputProps} />; break;
        case 'tags': inputToRender = <InputTags {...inputProps} model={inputProps.description.model} recordId={inputProps.record.id} />; break;
        case 'file': inputToRender = <InputFile {...inputProps} />; break;
        case 'image': inputToRender = <InputImage {...inputProps} />; break;
        case 'datetime': case 'date': case 'time': inputToRender = <InputDateTime {...inputProps} type={inputProps.type} />; break;
        case 'editor':
          inputToRender = (
            <div
              className={'h-100 form-control ' + `${this.state.invalidInputs[inputProps.inputName] ? 'is-invalid' : 'border-0'}`}>
              <ReactQuill
                theme="snow"
                value={this.state.data[inputProps.inputName] as Value}
                onChange={(value) => this.inputOnChangeRaw(inputProps.inputName, value)}
                className="w-100"
              />
            </div>
          );
          break;
        default: inputToRender = <InputVarchar {...inputProps} />;
      }
    }
  }

  return inputToRender;
}
