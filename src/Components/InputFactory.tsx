import React, { Component } from 'react'

// import ReactQuill, {Value} from 'react-quill';
// import 'react-quill/dist/quill.snow.css';

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
  let description: any = inputProps.description;

  if (!description) {
    return <div className="alert alert-warning">No description for input [{inputProps.inputName}]. Check console for error log.</div>
  }

  try {
    if (description.enumValues) {
      inputToRender = <InputEnumValues {...inputProps} enumValues={description.enumValues} enumCssClasses={description.enumCssClasses}/>
    } else {
      if (typeof description.reactComponent === 'string' && description.reactComponent !== '') {
        inputToRender = globalThis.app.renderReactElement(description.reactComponent, inputProps) ?? <></>;
      } else {
        switch (description.type ?? '') {
          case 'varchar': inputToRender = <InputVarchar {...inputProps} />; break;
          case 'password': inputToRender = <InputPassword {...inputProps} />; break;
          case 'text': inputToRender = <InputTextarea {...inputProps} />; break;
          case 'json': inputToRender = <InputTextarea {...inputProps} />; break;
          case 'float': case 'int': inputToRender = <InputInt {...inputProps} />; break;
          case 'boolean': inputToRender = <InputBoolean {...inputProps} />; break;
          case 'lookup': inputToRender = <InputLookup {...inputProps} />; break;
          case 'color': inputToRender = <InputColor {...inputProps} />; break;
          case 'tags': inputToRender = <InputTags {...inputProps} model={description.model} recordId={inputProps.record.id} />; break;
          case 'file': inputToRender = <InputFile {...inputProps} />; break;
          case 'image': inputToRender = <InputImage {...inputProps} />; break;
          case 'datetime': case 'date': case 'time': inputToRender = <InputDateTime {...inputProps} type={description.type} />; break;
          // case 'editor':
          //   inputToRender = (
          //     <div
          //       className={'h-100 form-control ' + `${this.state.invalidInputs[inputProps.inputName] ? 'is-invalid' : 'border-0'}`}>
          //       <ReactQuill
          //         theme="snow"
          //         value={this.state.data[inputProps.inputName] as Value}
          //         onChange={(value) => this.inputOnChangeRaw(inputProps.inputName, value)}
          //         className="w-100"
          //       />
          //     </div>
          //   );
          //   break;
          default: inputToRender = <InputVarchar {...inputProps} />;
        }
      }
    }
  } catch (e) {
    inputToRender = <div className="alert alert-danger">Failed to initialize input [{inputProps.inputName}]. Check console for error log.</div>
    console.error('Failed to initialize input for ' + inputProps.inputName);
    console.error(e);
  }

  return inputToRender;
}
