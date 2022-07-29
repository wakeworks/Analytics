import jQuery from 'jquery';
import { loadComponent } from 'lib/Injector';
import React from 'react';
import ReactDOM from 'react-dom';

jQuery.entwine('ss', ($) => {
  // We're matching to the field based on class. We added the last class in the field
  $('.js-injector-boot .form__field-holder .analytics-pages-field').entwine({
    onmatch() {
      // We're using the injector to create an instance of the react component we can use
      const Component = loadComponent('AnalyticsPagesField');
      // We've added the schema state to the div in the template above which we'll use as props
      const schemaState = this.data('state');

      // We render the component onto the targeted div
      ReactDOM.render(<Component {...schemaState} />, this[0]);
    },

    // When we change the loaded page we'll remove the component
    onunmatch() {
      ReactDOM.unmountComponentAtNode(this[0]);
    },
  });
});

