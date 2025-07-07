const ADIOS = {

  onAppLoaded: function(callback) {
    document.addEventListener('readystatechange', function() {
      if (document.readyState === 'complete') {
        callback();
      }
    });
  },

  update(url, params, selector, onDone) {
    _ajax_supdate(url, params, selector, { async: true, append: false, success: onDone });
  },

  renderDesktop: function(url, params) {
    if (typeof params == 'undefined') params = {};
    if (typeof options == 'undefined') options = {};

    $('.adios.main-content').css('opacity', 0.5);

    if (options.type == 'POST') {
      let paramsObj = _ajax_params(params);
      let formHtml = '';

      formHtml = '<form action="' + globalThis.app.config.rootUrl + '/' + _controller_url(url, {}, true) + '" method=POST>';
      for (var i in paramsObj) {
       formHtml += '<input type="hidden" name="' + i + '" value="' + paramsObj[i] + '" />';
      }
      formHtml += '</form>';

      $(formHtml).appendTo('body').submit();
    } else {
      window.location.href = globalThis.app.config.rootUrl + '/' + _controller_url(url, params, true);
    }
  },

  renderWindow: function(url, params, options) {
    window_render(url, params, options.onclose, options);
  },


  modal: function(controllerUrl, params = {}, modalParams = null) {
    $('#adios-modal-title-global').text("");

    if (modalParams != null) {
      $('#adios-modal-title-global').text(modalParams.title);
    }

    _ajax_update(
      controllerUrl,
      params,
      'adios-modal-body-global',
      {
        success: () => {
          $('#adios-modal-global').modal();
        }
      }
    );
  },

  modalToggle(uid) {
    $('#adios-modal-' + uid).modal('toggle');
  },

}
