import React from 'react'
import ImageUploading, { ImageType } from 'react-images-uploading';
import * as uuid from 'uuid';
import { Input, InputProps, InputState } from '../Input'

interface FileInputProps extends InputProps {
  type: 'file' | 'image',
}

interface FileInputState extends InputState {
  files: Array<any>
}

export default class File extends Input<FileInputProps, FileInputState> {
  static defaultProps = {
    inputClassName: 'image',
    id: uuid.v4(),
  }

  constructor(props: FileInputProps) {
    super(props);

    this.state = {
      ...this.state, // Parent state
      files: [],
    };
  }

  onFileChange(files: Array<ImageType>, addUpdateIndex: any) {
    let file: any = files[0];

    this.onChange({
      fileName: file ? file.file.name : null,
      fileData: file ? file.fileData : null,
      // fileSize: image.file.size,
      // fileType: image.file.type
    });

    this.setState({
      files: files
    })

  };

  getFileUrl(): string {
    if (this.state.value) {
      if (this.state.value.fileData) {
        return this.state.value.fileData;
      } else if (this.state.value) {
        return globalThis.app.config.uploadUrl + '/' + this.state.value;
      } else {
        return '';
      }
    } else {
      return '';
    }
  }

  getFileName(): string {
  console.log('gfn', this.state.value);
    if (this.state.value.fileName) {
      return this.state.value.fileName;
    } else if (this.state.value) {
      return this.state.value;
    } else {
      return '';
    }
  }

  renderValueElement() {
    return (this.state.value ? <>
      <a
        href={this.getFileUrl()}
        target='_blank'
        onClick={(e) => { e.stopPropagation(); }}
        className="btn btn-transparent btn-small"
      >
        <span className="icon"><i className="fa-solid fa-up-right-from-square"></i></span>
        <span className="text">{this.getFileName()}</span>
      </a>
    </> : <></>);
  }

  renderInputElement() {
    return <>
      {this.renderValueElement()}
      {this.state.readonly ? null : <>
        <ImageUploading
          value={this.state.value && this.state.value['fileData'] != null
            ? [this.state.value]
            : []
          }
          onChange={(files: Array<ImageType>, addUpdateIndex: any) => this.onFileChange(files, addUpdateIndex)}
          maxNumber={1}
          dataURLKey="fileData"
        >
          {({
            imageList,
            onImageUpload,
            onImageUpdate,
            onImageRemove,
            isDragging,
            dragProps,
          }) => (
            <div className="upload__image-wrapper">
              <button
                className="btn btn-small btn-transparent"
                style={isDragging ? { color: 'red' } : undefined}
                onClick={onImageUpload}
                {...dragProps}
              >
                <span className="icon"><i className="fas fa-cloud-arrow-up"></i></span>
                <span className="text">{this.translate("Upload file")}</span>
              </button>
            </div>
          )}
        </ImageUploading>
      </>}
    </>;
  }
}
