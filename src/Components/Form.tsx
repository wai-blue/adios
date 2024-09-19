import React, { Component } from 'react';
import * as uuid from 'uuid';

import Notification from "./Notification";
import { ProgressBar } from 'primereact/progressbar';
import { Tooltip } from 'primereact/tooltip';
import request from "./Request";

import Swal, {SweetAlertOptions} from "sweetalert2";

import { adiosError, deepObjectMerge } from "./Helper";

import Table from "./Table";
import { InputProps } from "./Input";
import { InputFactory } from "./InputFactory";

interface Content {
  [key: string]: ContentCard | any;
}

interface ContentCard {
  title: string
}

export interface FormEndpoint {
  describeForm: string,
  getRecord: string,
  saveRecord: string,
}

export interface FormPermissions {
  canCreate?: boolean,
  canRead?: boolean,
  canUpdate?: boolean,
  canDelete?: boolean,
}

export interface FormColumns {
  [key: string]: any;
}

export interface FormRecord {
  [key: string]: any;
}

export interface FormUi {
  title?: string,
  subTitle?: string,
  saveButtonText?: string,
  addButtonText?: string,
}

export interface FormDescription {
  columns?: FormColumns,
  defaultValues?: FormRecord,
  permissions?: FormPermissions,
  ui?: FormUi,
}

export interface FormProps {
  isInitialized?: boolean,
  parentTable?: any,
  uid?: string,
  model: string,
  id?: any,
  prevId?: any,
  nextId?: any,
  readonly?: boolean,
  content?: Content,
  layout?: Array<Array<string>>,
  hideOverlay?: boolean,
  showInModal?: boolean,
  showInModalSimple?: boolean,
  isInlineEditing?: boolean,
  customEndpointParams?: any,

  tag?: string,
  context?: any,
  children?: any,

  description?: FormDescription,
  endpoint?: FormEndpoint,

  onChange?: () => void,
  onClose?: () => void,
  onSaveCallback?: (form: Form<FormProps, FormState>, saveResponse: any) => void,
  onDeleteCallback?: () => void,
}

export interface FormState {
  isInitialized: boolean,
  id?: any,
  prevId?: any,
  nextId?: any,
  readonly?: boolean,
  content?: Content,

  description: FormDescription,
  record: FormRecord,
  endpoint: FormEndpoint,
  customEndpointParams: any,

  creatingRecord: boolean,
  updatingRecord: boolean,
  isInlineEditing: boolean,
  invalidInputs: Object,
  tabs?: any,
  folderUrl?: string,
  layout?: string,
  params: any,
  invalidRecordId: boolean,
}

export default class Form<P, S> extends Component<FormProps, FormState> {
  static defaultProps = {
    uid: '_form_' + uuid.v4().replace('-', '_'),
  }

  newState: any;

  model: String;
  components: Array<React.JSX.Element> = [];

  jsxContentRendered: boolean = false;
  jsxContent: JSX.Element;

  constructor(props: FormProps) {
    super(props);

    if (this.props.uid) {
      globalThis.app.reactElements[this.props.uid] = this;
    }

    this.state = this.getStateFromProps(props);
  }

  getStateFromProps(props: FormProps) {
    return {
      isInitialized: false,
      endpoint: props.endpoint ? props.endpoint : (globalThis.app.config.defaultFormEndpoint ?? {
        describeForm: 'api/form/describe',
        saveRecord: 'api/record/save',
        getRecord: 'api/record/get',
      }),
      id: props.id,
      prevId: props.prevId,
      nextId: props.nextId,
      readonly: props.readonly,
      description: props.description ?? {
        columns: {},
        defaultValues: {},
        permissions: {
          canCreate: false,
          canRead: false,
          canUpdate: false,
          canDelete: false,
        },
        ui: {},
      },
      content: props.content,
      layout: this.convertLayoutToString(props.layout),
      creatingRecord: props.id ? props.id == -1 : false,
      updatingRecord: props.id ? props.id != -1 : false,
      isInlineEditing: props.isInlineEditing ? props.isInlineEditing : false,
      invalidInputs: {},
      record: {},
      params: null,
      invalidRecordId: false,
      customEndpointParams: this.props.customEndpointParams ?? {},
    };
  }


  /**
   * This function trigger if something change, for Form id of record
   */
  componentDidUpdate(prevProps: FormProps, prevState: FormState) {
    let newState: any = {};
    let setNewState: boolean = false;

    if (this.props.isInitialized != prevProps.isInitialized) {
      newState.isInitialized = this.props.isInitialized;
      setNewState = true;
    }

    if (prevProps.id !== this.props.id) {
      newState = this.getStateFromProps(this.props);
      newState.id = this.props.id;

      // this.checkIfIsEdit();
      this.loadFormDescription();

      newState.invalidInputs = {};
      newState.creatingRecord = this.props.id ? this.props.id <= 0 : false;
      newState.updatingRecord = this.props.id ? this.props.id > 0 : false;
      setNewState = true;
    }

    if (setNewState) {
      this.setState(newState);
    }
  }

  componentDidMount() {
    this.initTabs();
    this.loadFormDescription();
  }

  getEndpointUrl(action: string) {
    return this.state.endpoint[action] ?? '';
  }

  getEndpointParams(): object {
    return {
      model: this.props.model,
      id: this.state.id ? this.state.id : 0,
      tag: this.props.tag,
      maxRelationLevel: 10,
      __IS_AJAX__: '1',
      ...this.state.customEndpointParams
    };
  }

  loadFormDescription() {
    console.log(this.props.description);
    if (this.props.description) {
      this.setState({
        description: this.props.description,
        readonly: !(this.props.description.permissions?.canUpdate || this.props.description.permissions?.canCreate),
      }, () => {
        if (this.state.id !== -1) {
          this.loadRecord();
        } else {
          this.setRecord(this.props.description?.defaultValues ?? {});
        }
      });
    } else {
      request.post(
        this.getEndpointUrl('describeForm'),
        this.getEndpointParams(),
        {},
        (description: any) => {
          const defaultValues = deepObjectMerge(this.state.description.defaultValues ?? {}, description.defaultValues);

          this.setState({
            description: description,
            readonly: !(description.permissions?.canUpdate || description.permissions?.canCreate),
          }, () => {
            if (this.state.id !== -1) {
              this.loadRecord();
            } else {
              this.setRecord(defaultValues);
            }
          });
        }
      );
    }
  }

  reload() {
    this.setState({isInitialized: false}, () => {
      this.loadFormDescription();
    });
  }

  loadRecord() {
    request.post(
      this.getEndpointUrl('getRecord'),
      this.getEndpointParams(),
      {},
      (record: any) => {
        if (this.state.id != -1 && !record.id) {
          this.setState({isInitialized: true, invalidRecordId: true});
        } else {
          this.setRecord(record);
        }
      }
    );
  }

  setRecord(record: any) {
    record = this.onAfterRecordLoaded(record);
    this.setState({isInitialized: true, record: record}, () => {
      this.onAfterFormInitialized();
    });
  }

  onBeforeSaveRecord(record) {
    // to be overriden
    return record;
  }

  onAfterSaveRecord(saveResponse) {
    if (this.props.onSaveCallback) this.props.onSaveCallback(this, saveResponse);
  }

  saveRecord() {
    this.setState({
      invalidInputs: {}
    });

    let record = { ...this.state.record, id: this.state.id };

    console.log('_RELATIONS', this.state.record._RELATIONS);

    (this.state.record._RELATIONS ?? []).map((relName) => { console.log('deleting', relName); delete record[relName]; });

    console.log('record', record);

    record = this.onBeforeSaveRecord(record);

    request.post(
      this.getEndpointUrl('saveRecord'),
      {
        ...this.getEndpointParams(),
        record: record
      },
      {},
      (saveResponse: any) => {
        this.onAfterSaveRecord(saveResponse);
      },
      (err: any) => {
        if (err.status == 422) {
          this.setState({
            invalidInputs: err.data.invalidInputs
          });
        }
      }
    );
  }

  normalizeRecord(record: any): any {
    return record;
  }

  updateRecord(changedValues: any) {
    const record = this.normalizeRecord(this.state.record);
    this.setState({record: deepObjectMerge(record, changedValues)});
  }

  onAfterRecordLoaded(record: any) {
    return record;
  }

  onAfterFormInitialized() {
  }

  changeTab(changeTabName: string) {
    let tabs: any = {};

    Object.keys(this.state.tabs).map((tabName: string) => {
      tabs[tabName] = {
        active: tabName == changeTabName
      };
    });

    this.setState({
      tabs: tabs
    });
  }

  /*
    * Initialize form tabs is are defined
    */
  initTabs() {
    if (this.state.content?.tabs == undefined) return;

    let tabs: any = {};
    let firstIteration: boolean = true;

    Object.keys(this.state.content?.tabs).map((tabName: string) => {
      tabs[tabName] = {
        active: firstIteration
      };

      firstIteration = false;
    });

    this.setState({
      tabs: tabs
    });
  }

  convertLayoutToString(layout?: Array<Array<string>>): string {
    //@ts-ignore
    return layout?.map(row => `"${row}"`).join('\n');
  }

  /**
   * Render tab
   */
  renderContent(): JSX.Element {
    if (this.state.description?.columns == null) {
      return adiosError(`No columns specified for ${this.props.model}. Did the controller return definition of columns?`);
    }

    if (this.state.content?.tabs) {
      let tabs: any = Object.keys(this.state.content.tabs).map((tabName: string) => {
        return this.renderTabs(tabName, this.state.content?.tabs[tabName]);
      })

      return tabs;
    } else {
      return this.renderTabs("default", this.state.content);
    }
  }

  /*
    * Render tab content
    * If tab is not set, use default tabName else use activated one
    */
  renderTabs(tabName: string, content: any) {
    if (
      tabName == "default"
      || (this.state.tabs && this.state.tabs[tabName]['active'])
    ) {

      let key = 0;

      return (
        <div
          key={tabName}
        >
          {content != null
            ? Object.keys(content).map((contentArea: string) => {
              return this._renderContentItem(key++, contentArea, content[contentArea]);
            })
            : this.state.record != null ? (
              Object.keys(this.state.description?.columns ?? {}).map((columnName: string) => {
                return this.inputWrapper(columnName);
              })
            ) : ''
          }
        </div>
      );
    } else {
      return <></>;
    }
  }

  /**
   * Render content item
   */
  _renderContentItem(key: number, contentItemArea: string, contentItemParams: undefined | string | Object | Array<string>): JSX.Element {
    if (contentItemParams == undefined) return <b style={{color: 'red'}}>Content item params are not defined</b>;

    let contentItemKeys = Object.keys(contentItemParams);
    if (contentItemKeys.length == 0) return <b style={{color: 'red'}}>Bad content item definition</b>;

    let contentItemName = contentItemArea == "inputs"
      ? contentItemArea : contentItemKeys[0];

    let contentItem: JSX.Element | null;

    switch (contentItemName) {
      case 'input':
        contentItem = this.inputWrapper(contentItemParams['input'] as string);
        break;
      case 'inputs':
        //@ts-ignore
        contentItem = (contentItemParams['inputs'] as Array<string>).map((columnName: string) => {
          return this.inputWrapper(columnName)
        });
        break;
      case 'html':
        contentItem = (<div dangerouslySetInnerHTML={{__html: contentItemParams['html']}}/>);
        break;
      default:
        contentItem = globalThis.app.renderReactElement(
          contentItemName,
          {
            ...contentItemParams[contentItemName],
            ...{
              parentRecordId: this.state.id,
              parentFormModel: this.props.model,
            }
          }
        );

        if (contentItem !== null) {
          this.components.push(contentItem);
        }

        break;
    }

    return (
      <div key={key} style={{gridArea: contentItemArea}}>
        {contentItem}
      </div>
    );
  }

  buildInputParams(columnName: string, customInputParams?: any) {
    if (!customInputParams) customInputParams = {};
    let stateColDef = (this.state.description?.columns ? this.state.description?.columns[columnName] ?? {} : {});
    customInputParams = {...stateColDef, ...customInputParams};

    return {...customInputParams, ...{readonly: this.state.readonly}};
  }

  getDefaultInputProps() {
    return {
      uid: this.props.uid + '_' + uuid.v4(),//columnName,
      parentForm: this,
      context: this.props.context ? this.props.context : this.props.uid,
      isInitialized: false,
      isInlineEditing: this.state.isInlineEditing,
      showInlineEditingButtons: false, // !this.state.isInlineEditing,
      onInlineEditCancel: () => {
      },
      onInlineEditSave: () => {
        this.saveRecord();
      }
    };
  }

  /**
   * Render different input types
   */
  input(columnName: string, customInputParams?: any, onChange?: any): JSX.Element {
    const inputParams = this.buildInputParams(columnName, customInputParams);
    const record = this.state.record ?? {};
    const columns = this.state.description?.columns ?? {};

    const inputProps: InputProps = {
      ...this.getDefaultInputProps(),
      params: inputParams,
      value: record[columnName] ?? '',
      columnName: columnName,
      invalid: this.state.invalidInputs[columnName] ?? false,
      readonly: this.props.readonly || columns[columnName]?.readonly || columns[columnName]?.disabled,
      cssClass: inputParams.cssClass ?? '',
      onChange: (value: any) => {
        let record = {...this.state.record};
        record[columnName] = value;
        this.setState({record: record}, () => {
          if (this.props.onChange) this.props.onChange();
        });
      }
    };

    return InputFactory(inputProps);
  }

  inputWrapper(columnName: string, customInputParams?: any) {

    const inputParams = this.buildInputParams(columnName, customInputParams);

    return columnName == 'id' ? <></>: this.inputWrapperCustom(
      columnName,
      inputParams,
      inputParams.title,
      <>
        {this.input(columnName, inputParams)}

        {inputParams.description
          ? <>
            <Tooltip target={'#' + this.props.uid + '_' + columnName + ' .input-description'} />
            <i
              className="input-description fas fa-info"
              data-pr-tooltip={inputParams.description}
              data-pr-position="top"
            ></i>
          </>
          : null
        }
      </>
    );
  }

  inputWrapperCustom(columnName: string, params: any, label: string|JSX.Element, body: string|JSX.Element): JSX.Element {
    return <>
      <div
        id={this.props.uid + '_' + columnName}
        className={"input-wrapper" + (params.required == true ? " required" : "")}
        key={columnName}
      >
        <label className="input-label" htmlFor={this.props.uid + '_' + columnName}>
          {label}
        </label>

        <div className="input-body" key={columnName}>
          {body}
        </div>

      </div>
    </>;
  }

  renderSaveButton(): JSX.Element {
    let id = this.state.id ? this.state.id : 0;

    return <>
      {this.state.description?.permissions?.canUpdate ? <button
        onClick={() => this.saveRecord()}
        className={
          "btn btn-success"
          + (id <= 0 && this.state.description?.permissions?.canCreate || id > 0 && this.state.description?.permissions?.canUpdate ? "d-block" : "d-none")
        }
      >
        {this.state.updatingRecord
          ? (
            <>
              <span className="icon"><i className="fas fa-save"></i></span>
              <span className="text"> {this.state.description?.ui?.saveButtonText ?? globalThis.app.translate("Save")}</span>
            </>
          )
          : (
            <>
              <span className="icon"><i className="fas fa-plus"></i></span>
              <span className="text"> {this.state.description?.ui?.addButtonText ?? globalThis.app.translate("Add")}</span>
            </>
          )
        }
      </button> : null}
    </>;
  }

  renderEditButton(): JSX.Element {
    return <>
      {this.state.description?.permissions?.canUpdate ? <button
        onClick={() => this.setState({ isInlineEditing: true })}
        className="btn btn-transparent"
      >
        <span className="icon"><i className="fas fa-pencil-alt"></i></span>
        <span className="text">{globalThis.app.translate('Edit')}</span>
      </button> : null}
    </>;
  }

  renderCloseButton(): JSX.Element {
    return (
      <button
        className="btn btn-light"
        type="button"
        data-dismiss="modal"
        aria-label="Close"
        onClick={this.props.onClose}
      ><span className="text">&times;</span></button>
    );
  }

  renderHeaderLeft(): JSX.Element {
    return <>
      {this.props.showInModal ? this.renderCloseButton() : null}
      {this.state.isInlineEditing ? this.renderSaveButton() : this.renderEditButton()}
    </>;
  }

  renderHeaderRight(): JSX.Element {
    const prevId = this.state?.prevId ?? 0;
    const nextId = this.state?.nextId ?? 0;

    return <>
      {prevId || nextId ? <>
        <button
          onClick={() => {
            if (prevId && this.props.parentTable) {
              this.props.parentTable.openForm(prevId);
            }
          }}
          className={"btn btn-transparent" + (prevId ? "" : " btn-disabled")}
        >
          <span className="icon"><i className="fas fa-angle-left"></i></span>
        </button>
        <button
          onClick={() => {
            if (nextId && this.props.parentTable) {
              this.props.parentTable.openForm(nextId);
            }
          }}
          className={"btn btn-transparent" + (nextId ? "" : " btn-disabled")}
        >
          <span className="icon"><i className="fas fa-angle-right"></i></span>
        </button>
      </> : null}
      {/* {this.state.isEdit ?
        <button
          onClick={() => this.deleteRecord(this.state.id ? this.state.id : 0)}
          className={"btn btn-danger btn-icon-split ml-2 " + (this.state.permissions.canDelete ? "d-block" : "d-none")}
        >
          <span className="icon"><i className="fas fa-trash"></i></span>
          <span className="text">{globalThis.app.translate('Delete')}</span>
        </button>
        : ''
      } */}
    </>;
  }

  renderFooter(): JSX.Element|null { return null; }

  renderTitle(): JSX.Element {
    let title = this.state.description?.ui?.title ??
      (this.state.updatingRecord
          ? globalThis.app.translate('Record') + ' #' + (this.state.record?.id ?? '-')
          : globalThis.app.translate('New record')
      )
    ;
    let subTitle = this.state.description?.ui?.subTitle ?? this.props.model;

    return <>
      <h2>{title}</h2>
      <small>{subTitle}</small>
    </>
  }

  render() {

    if (!this.state.isInitialized || !this.state.record) {
      return (
        <div className="p-4 h-full flex items-center">
          <ProgressBar mode="indeterminate" style={{ flex: 1, height: '30px' }}></ProgressBar>
        </div>
      );
    }

    if (this.state.invalidRecordId) {
      return <>
        <div className="alert alert-danger m-1">
          Unable to load record.
        </div>
      </>
    }

    let formTitle = this.renderTitle();
    let formContent = this.renderContent();
    let formFooter = this.renderFooter();

    if (this.props.showInModal) {
      return <>
        <div className="modal-header">
          <div className="modal-header-left">{this.renderHeaderLeft()}</div>
          <div className="modal-header-title">{formTitle}</div>
          <div className="modal-header-right">{this.renderHeaderRight()}</div>
        </div>
        <div className="modal-body">{formContent}</div>
        {formFooter ? <div className="modal-footer">{formFooter}</div> : null}
      </>;
    } else {
      return <>
        <div id={"adios-form-" + this.props.uid} className="adios component form">
          <div className="form-header">
            <div className="form-header-left">{this.renderHeaderLeft()}</div>
            <div className="form-header-title">{formTitle}</div>
            <div className="form-header-right">{this.renderHeaderRight()}</div>
          </div>
          <div className="form-body">{formContent}</div>
          {formFooter ? <div className="form-footer">{formFooter}</div> : null}
        </div>
      </>;
    }
  }
}
