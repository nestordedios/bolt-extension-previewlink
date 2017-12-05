(function() {

  'use strict';

  // Add tooltip to button
  var tooltip = $('[data-toggle="tooltip"]').tooltip();

  /*
    Copy text to clipboard
  */

  // click events
  document.getElementById("previewlink-copy-button").addEventListener('click', copy);
  document.getElementById("preview-link-button").addEventListener('click', openPreviewPage);

  // event handler
  function copy(e) {

    var input = $('#previewlink');

    // is input selectable?
    if (input && input.select) {

      // select text
      input.select();

      try {
        // copy text to clipboard
        document.execCommand('copy');
      }
      catch (err) {
        alert('please press Ctrl/Cmd+C to copy');
      }

    }

  }

  function openPreviewPage(e){
    window.open(this.attr('data-url', '_blank'));
  }

})();
