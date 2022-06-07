CKEDITOR.plugins.add('cmlex-box', {

  requires: 'widget',
  icons: 'cmlex-box',
  init: function(editor)
    {
     var name = 'cmlex-box';
     var text = 'Скрытый блок, который открывается во всплывающем окне';

    editor.widgets.add( name,
        {
      allowedContent:  'div(!cmlex-insert-box); div(!cmlex-insert-box-href); h2(!cmlex-insert-box-body)',
      requiredContent: 'div(cmlex-insert-box)',

      editables: {
        title: {
          selector: '.cmlex-insert-box-href',
          allowedContent: 'br strong em'
        },
        content: {
          selector: '.cmlex-insert-box-body',
        }
      },

      template:
        '<div class="cmlex-insert-box"><div class="cmlex-insert-box-href"><p>Кликабельный текст - заглавие</p></div><div class="cmlex-insert-box-hide" style="display:none;"><div class="cmlex-insert-box-body"><p>Содержимое скрытого блока</p></div></div></div>',

      button: text,

      upcast: function(element)
            {
        return element.name == 'div' && element.hasClass('cmlex-insert-box');
      }
    } );

        editor.ui.addButton(name, {label:text, command:name, icon:this.path + 'icons/' + name + '.png', toolbar: 'insert'});
  }
} );
