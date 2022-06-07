CKEDITOR.plugins.add('cmlex-bootstrap', {

  requires: 'widget',
  icons: 'cmlex-bootstrap',
  init: function(editor)
    {
     var name = 'cmlex-bootstrap';
     var text = 'Bootstrap объект';

    editor.widgets.add( name,
        {
      allowedContent:  'div(!' + name + ')',
      requiredContent: 'div(' + name + ')',

			editables:
            {
				content:
                {
					selector: '.cmlex-bootstrap',
				}
			},

      template:
        '<div class="' + name + '"></div>',

      button: text,

      upcast: function(element)
            {
        return element.name == 'div' && element.hasClass(name);
      }
    } );
  }
} );


