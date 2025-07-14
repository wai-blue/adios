import { createRoot } from "react-dom/client";
import React, { useRef } from 'react';
import ReactDOM from 'react-dom';
import * as uuid from 'uuid';
import {isValidJson, kebabToPascal, camelToKebab, deepObjectMerge} from './Helper';
import Dialog from "./Dialog";

export class ADIOS {
  config: object = {};

  reactComponents: any = {};
  reactElementsWaitingForRender: number = 0;
  reactElements: Object = {};

  primeReactTailwindTheme: any = {
    dataTable: {
      // root: { className: 'bg-primary' },
      headerRow: { className: 'bg-primary' },
    },
  };

  dictionary: any = null;
  lastShownDialogRef: any;
  defaultTranslationContext: string = 'app';

  /**
  * Define attributes which will not removed
  */
  attributesToSkip = [
    'onclick'
  ];

  constructor(config: object) {
    this.config = config;
    globalThis.app = this;
  }

  translate(orig: string, context?: string): string {
    let translated: string = orig;

    if (this.dictionary === null) return orig;

    if (!context) context = this.defaultTranslationContext;

    if (this.dictionary[context] && this.dictionary[context][orig]) {
      translated = this.dictionary[context][orig];
    } else if (this.dictionary['app'] && this.dictionary['app'][orig]) {
      translated = this.dictionary['app'][orig];
    } else {
      console.log('atd', this.dictionary, orig, context);
      this.addToDictionary(orig, context);
    }

    if (translated == '') translated = orig;

    return translated;
  }

  addToDictionary(orig: string, context: string) {
    // to be overriden
  }

  setTranslationContext(context: string) {
    this.defaultTranslationContext = context;
  }

  makeErrorResultReadable(error: any): JSX.Element {
    console.log('makeErrorResultReadable', error, error.code, error.data);

    let message: any = null;

    try {
      message = JSON.parse(error.message);
    } catch(ex) {
      message = {};
    }

    switch (error.code) {
      case 87335:
        return <>
          <b>Some inputs need your attention</b><br/>
          <br/>
          {message.map((item) => <div>{item}</div>)}
        </>;
      break;
      case 1062:
        return <>
          <b>Duplicate entry error</b><br/>
          <br/>
          <div>{error.message}</div>
        </>;
      break;
      default:
        return <>
          <div>Error #{error.code}</div>
          <pre style={{fontSize: '8pt', textAlign: 'left'}}>{error.message}</pre>
        </>;
      break;
    }
  }

  showDialog(content: JSX.Element, props?: any) {
    const root = ReactDOM.createRoot(document.getElementById('app-dialogs'));

    this.lastShownDialogRef = React.createRef();

    props.headerClassName = 'dialog-header ' + (props.headerClassName ?? '');
    props.contentClassName = 'dialog-content ' + (props.contentClassName ?? '');

    root.render(<>
      <Dialog
        ref={this.lastShownDialogRef}
        uid={'app_dialog_' + uuid.v4().replace('-', '_')}
        visible
        style={{minWidth: '50vw'}}
        {...props}
      >{content}</Dialog>
    </>);
  }

  showDialogDanger(content: JSX.Element, props?: any) {
    let defaultProps: any = {
      headerClassName: 'dialog-danger-header',
      contentClassName: 'dialog-danger-content',
      header: "🥴 Ooops",
      footer: <div className={"flex w-full justify-start"}>
        <button
          className="btn btn-transparent"
          onClick={() => { this.lastShownDialogRef.current.hide(); }}
        >
          <span className="icon"><i className="fas fa-check"></i></span>
          <span className="text">OK, I understand</span>
        </button>
      </div>
    };

    if (!props) props = {};

    if (!props.headerClassName) props.headerClassName = defaultProps.headerClassName;
    if (!props.contentClassName) props.contentClassName = defaultProps.contentClassName;
    if (!props.header) props.header = defaultProps.header;
    if (!props.footer) props.footer = defaultProps.footer;

    this.showDialog(content, props);
  }

  showDialogWarning(content: JSX.Element, props?: any) {
    let defaultProps: any = {
      headerClassName: 'dialog-warning-header',
      contentClassName: 'dialog-warning-content',
      header: "Warning",
      footer: <div className={"flex w-full justify-start"}>
        <button
          className="btn btn-transparent"
          onClick={() => { this.lastShownDialogRef.current.hide() }}
        >
          <span className="icon"><i className="fas fa-check"></i></span>
          <span className="text">OK, I understand</span>
        </button>
      </div>
    };

    if (!props) props = {};

    if (!props.headerClassName) props.headerClassName = defaultProps.headerClassName;
    if (!props.contentClassName) props.contentClassName = defaultProps.contentClassName;
    if (!props.header) props.header = defaultProps.header;
    if (!props.footer) props.footer = defaultProps.footer;

    this.showDialog(content, props);
  }

  showDialogConfirm(content: JSX.Element, props?: any) {
    let defaultProps = {
      headerClassName: 'dialog-confirm-header',
      contentClassName: 'dialog-confirm-content',
      header: "Confirm",
      footer: <>
        <div className={"flex w-full justify-between"}>
          <button className={"btn " + props.yesButtonClass ?? "btn-success"} onClick={() => { this.lastShownDialogRef.current.hide(); props.onYes(); }} >
            <span className="icon"><i className="fas fa-check"></i></span>
            <span className="text">{props.yesText}</span>
          </button>
          <button className={"btn " + props.noButtonClass ?? "btn-cancel"} onClick={() => { this.lastShownDialogRef.current.hide(); props.onNo(); }} >
            <span className="icon"><i className="fas fa-xmark"></i></span>
            <span className="text">{props.noText}</span>
          </button>
        </div>
      </>
    };

    if (!props.headerClassName) props.headerClassName = defaultProps.headerClassName;
    if (!props.contentClassName) props.contentClassName = defaultProps.contentClassName;
    if (!props.header) props.footer = defaultProps.header;
    if (!props.footer) props.footer = defaultProps.footer;

    this.showDialog(content, props);
  }

  registerReactComponent(elementName: string, elementObject: any) {
    this.reactComponents[elementName] = elementObject;
  }

  /**
   * Get specific ADIOS component with destructed params
   */
  renderReactElement(componentName: string, props: Object, children: any) {
    if (!componentName) return null;

    let componentNamePascalCase = kebabToPascal(componentName);

    if (!this.reactComponents[componentNamePascalCase]) {
      console.error('ADIOS: renderReactElement(' + componentNamePascalCase + '). Component does not exist. Use `adios.registerReactComponent()` in your project\'s index.tsx file.');
      return null;
    } else {
      return React.createElement(
        this.reactComponents[componentNamePascalCase],
        props,
        children
      );
    }
  };

  getReactElement(elementId: string): any {
    return this.reactElements[elementId] ?? null;
  }

  /**
  * Validate attribute value
  * E.g. if string contains Callback create frunction from string
  */
  // getValidatedAttributeValue(attributeName: string, attributeValue: any): Function|any {
  //   return attributeName.toLowerCase().includes('callback') ? new Function(attributeValue) : attributeValue;
  // }

  convertDomToReact(domElement) {
    let isAdiosComponent = false;
    let component: string = '';
    let componentProps: Object = {};

    if (domElement.nodeType == 3) { /* Text node: https://developer.mozilla.org/en-US/docs/Web/API/Node/nodeType */
      return <>{domElement.textContent}</>;
    } else {
      if (domElement.tagName.substring(0, 4) != 'APP-') {
        component = domElement.tagName.toLowerCase();
      } else {
        component = domElement.tagName.substring(4).toLowerCase();
        isAdiosComponent = true;
      }

      let attributesDoNotConvert: Array<string> = [];
      for (let i in domElement.attributes) {
        if (domElement.attributes[i].name == 'adios-do-not-convert') {
          attributesDoNotConvert = domElement.attributes[i].value.split(',');
        }
      }

      let i: number = 0
      while (domElement.attributes.length > i) {
        let attributeName: string = domElement.attributes[i].name.replace(/-([a-z])/g, (_: any, letter: string) => letter.toUpperCase());
        let attributeValue: any = domElement.attributes[i].value;

        if (!attributesDoNotConvert.includes(attributeName)) {
          if (attributeName.startsWith('json:')) {
            attributeName = attributeName.replace('json:', '');
            attributeValue = JSON.parse(attributeValue);
          } else if (attributeName.startsWith('string:')) {
            attributeName = attributeName.replace('string:', '');
            attributeValue = attributeValue;
          } else if (attributeName.startsWith('int:')) {
            attributeName = attributeName.replace('int:', '');
            attributeValue = parseInt(attributeValue);
          } else if (attributeName.startsWith('bool:')) {
            attributeName = attributeName.replace('bool:', '');
            attributeValue = attributeValue == 'true';
          } else if (attributeName.startsWith('function:')) {
            attributeName = attributeName.replace('function:', '');
            attributeValue = new Function(attributeValue);
          } else if (attributeValue === 'true') {
            attributeValue = true;
          } else if (attributeValue === 'false') {
            attributeValue = false;
          } else if (isValidJson(attributeValue)) {
            attributeValue = JSON.parse(attributeValue);
          }
        }

        componentProps[attributeName] = attributeValue;

        i++;
        // if (this.attributesToSkip.includes(attributeName)) {
        //   i++;
        //   continue;
        // }

        // // Remove attributes from HTML DOM
        // domElement.removeAttribute(domElement.attributes[i].name);
      }

      let children: Array<any> = [];

      domElement.childNodes.forEach((subElement, _index) => {
        children.push(this.convertDomToReact(subElement));
      });

      let reactElement: any = null;

      if (isAdiosComponent) {
        if (componentProps['uid'] == undefined) {
          componentProps['uid'] = '_' + uuid.v4().replace('-', '_');
        }

        reactElement = this.renderReactElement(
          component,
          componentProps,
          children
        );

        domElement.setAttribute('adios-react-rendered', 'true');
      } else {
        reactElement = React.createElement(
          component,
          componentProps,
          children
        );
      }

      return reactElement;
    }

  }

  /**
  * Render React component (create HTML tag root and render)
  */
  renderReactElements(rootElement?) {
    if (!rootElement) rootElement = document;

    rootElement.querySelectorAll('*').forEach((element, _index) => {

      if (element.tagName.substring(0, 4) != 'APP-') return;
      if (element.attributes['adios-react-rendered']) return;

      //@ts-ignore
      $(rootElement).addClass('react-elements-rendering');

      let elementRoot = createRoot(element);
      this.reactElementsWaitingForRender++;
      const reactElement = this.convertDomToReact(element)
      elementRoot.render(reactElement);


      // https://stackoverflow.com/questions/75388021/migrate-reactdom-render-with-async-callback-to-createroot
      // https://blog.saeloun.com/2021/07/15/react-18-adds-new-root-api/
      requestIdleCallback(() => {
        this.reactElementsWaitingForRender--;

        // console.log($(element), $(element).html());
        // $(element).find('*').each((el) => {
        //   console.log($(el));//, $(this).html());
        //   // $(this).parent().before($(this));
        // });

        if (this.reactElementsWaitingForRender <= 0) {
          //@ts-ignore
          $(rootElement)
            .removeClass('react-elements-rendering')
            .addClass('react-elements-rendered')
          ;
        }
      });
    });

  }
}

// export const adios = new ADIOS();
