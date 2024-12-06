  var ADIOS_windows = {};
  var desktop_main_box_history = [];
  var desktop_keyboard_shortcuts = [];

  var adios_menu_hidden = 0;

  function window_popup(controller, params, options) {
    if (typeof options == 'undefined') options = {};

    if (options.type == 'POST') {
      let paramsObj = _ajax_params(params);
      let formHtml = '';

      formHtml = '<form action="' + globalThis.app.config.accountUrl + '/' + _controller_url(controller, {}, true) + '" method=POST target="adios_popup">';
      for (var i in paramsObj) {
       formHtml += '<input type="hidden" name="' + i + '" value="' + paramsObj[i] + '" />';
      }
      formHtml += '</form>';

      $(formHtml).appendTo('body').submit();
    } else {
      window.open(globalThis.app.config.accountUrl + '/' + _controller_url(controller, params));
    }
  }

  function window_render(controller, params, onclose, options) {
    if (typeof params == 'undefined') params = {};
    if (typeof options == 'undefined') options = {};

    params.__IS_WINDOW__ = '1';

    $('.adios.main-content .windows').addClass('update-in-progress');

    setTimeout(function() {
      _ajax_read(controller, params, function(html) {

        // if (params.windowParams && params.windowParams.uid) {
        //   $('#' + params.windowParams.uid).remove();
        // }

        $('.adios.main-content .windows').show();
        $('.adios.main-content .windows').removeClass('update-in-progress');

        $('.adios.main-content .windows .windows-content').append(html);

        if (typeof onclose == 'function') onclose();
        // window_post_render(html, controller, params, options);

      });

    }, 0);
  }

  function window_refresh(windowId) {
    let win = $('#' + windowId);

    if (win.length > 0) {
      win
        .attr('id', win.attr('id') + '_TO_BE_REMOVED')
      ;
      ADIOS.renderWindow(
        ADIOS_windows[windowId]['controller'],
        ADIOS_windows[windowId]['params'],
        ADIOS_windows[windowId]['onclick'],
      );

      setTimeout(function() {
        win.remove();
      }, 500);
    }
  }

  function window_close(windowId, oncloseParams) {
    if (!ADIOS_windows[windowId]) {
      // okno bolo otvarane cez URL
      window.location.href = globalThis.app.config.accountUrl;
    } else {

      if ($('.adios.main-content .adios.ui.Window').length == 1) {
        window.history.back();
      }

      $('#' + windowId).find('a.--onclose-href').trigger('click');
      $('#' + windowId).remove();

      if ($('.adios.main-content .adios.ui.Window').length == 0) {
        $('.adios.main-content .windows').hide();
      }

      if (typeof ADIOS_windows[windowId]['onclose'] == 'function') {
        ADIOS_windows[windowId]['onclose'](oncloseParams);
      }

    }

  }

