import React, { createRef } from 'react'
import ImageUploading, { ImageType } from 'react-images-uploading';
import * as uuid from 'uuid';
import { Input, InputProps, InputState } from '../Input'

interface FileInputProps extends InputProps {
  type: 'file' | 'image',
  uploadButtonText?: string,
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
      isInitialized: true,
    };
  }

  onFileChange(files: Array<any>) {
    let file: any = files[0];

    this.onChange({
      fileName: file ? file.file.name : null,
      fileData: file ? file.fileData : null,
      fileSize: file ? parseInt(file.fileSize) : null,
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
    if (this.state.value.fileName) {
      return this.state.value.fileName;
    } else if (this.state.value) {
      return this.state.value;
    } else {
      return '';
    }
  }

  getFileSize(): number {
    if (this.state.value.fileSize) {
      return this.state.value.fileSize;
    } else {
      return 0;
    }
  }

  renderValueElement() {
    return (this.state.value ? <>
      <a
        href={this.getFileUrl()}
        target='_blank'
        onClick={(e) => { e.stopPropagation(); }}
        className="btn btn-primary-outline btn-small"
      >
        <span className="icon"><i className="fa-solid fa-up-right-from-square"></i></span>
        <span className="text">{this.getFileName()}</span>
        {this.getFileSize() > 0 ? <span className="text">({Math.round(this.getFileSize() * 100 / 1024) / 100} kB)</span> : null}
      </a>
    </> : <></>);
  }

  renderInputElement() {
    return <>
      {this.renderValueElement()}
      {this.state.readonly ? null : <>
        <ImageUploading
          ref={this.refInput}
          value={this.state.value && this.state.value['fileData'] != null
            ? [this.state.value]
            : []
          }
          onChange={(files: Array<ImageType>, addUpdateIndex: any) => this.onFileChange(files)}
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
                <span className="text">{this.props.uploadButtonText ?? this.translate("Upload file", 'ADIOS\\Core\\Loader::Components\\Inputs\\File')}</span>
              </button>
            </div>
          )}
        </ImageUploading>
      </>}
    </>;
  }
}
