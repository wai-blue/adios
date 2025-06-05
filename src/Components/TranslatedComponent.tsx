import React, { Component } from 'react';

export default class TranslatedComponent<P, S> extends Component {
  translationContext: string = '';

  translate(orig: string, context?: string): string {
    return globalThis.app.translate(orig, context ?? this.translationContext);
  }

}
