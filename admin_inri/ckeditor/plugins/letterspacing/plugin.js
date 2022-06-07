CKEDITOR.plugins.add( 'letterspacing', {
  requires: ['richcombo'],
  init: function( editor ) {
    var config = editor.config,
      lang = editor.lang.format;
    var trackings = [];

    config.allowedContent = 'span'; //There may be a better way to do this.

    for (var i = -5; i < 5; i++) {
      trackings.push(String(i) + 'px');
    }

    editor.ui.addRichCombo('letterspacing', {
      label: 'Межбуквенный',
      title: 'Межбуквенный интервал',
      voiceLabel: 'Межбуквенный интервал',
      className: 'cke_format',
      multiSelect: false,

      panel: {
      css : [ config.contentsCss, CKEDITOR.getUrl( CKEDITOR.skin.getPath('editor') + 'editor.css' ) ]
      },

      init: function() {
      this.startGroup('letterspacing');
      for (var this_letting in trackings) {
        this.add(trackings[this_letting], trackings[this_letting], trackings[this_letting]);
      }
      },

      onClick: function(value) {
      editor.focus();
      editor.fire('saveSnapshot');
      var ep = editor.elementPath();
      var style = new CKEDITOR.style({styles: {'letter-spacing': value}});
      editor[style.checkActive(ep) ? 'removeStyle' : 'applyStyle' ](style);

      editor.fire('saveSnapshot');
      }
    });
  }
});
