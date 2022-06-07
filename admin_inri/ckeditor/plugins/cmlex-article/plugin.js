CKEDITOR.plugins.add('cmlex-article', {

  requires: 'widget',
  icons: 'cmlex-article',
  init: function(editor)
    {
     var name = 'cmlex-article';
     var text = 'Плашка (фотография + описание + ссылка)';

    editor.widgets.add( name,
        {
      allowedContent:  'article(!cmlex-article); div(!cmlex-article-img); h2(!cmlex-article-title)',
      requiredContent: 'article(cmlex-article)',

      editables: {
        title: {
          selector: '.cmlex-article-title',
          allowedContent: 'br strong em a[href]'
        },
        content: {
          selector: '.cmlex-article-img'
        }
      },

      template:
        '<article class="cmlex-article"><div class="cmlex-article-img"><img src="/skin/nophoto.png" style="width:100%"></div><div class="cmlex-article-title"><a href="#">Ссылка с кратким описанием</a></div></article>',

      button: text,

      upcast: function(element)
            {
        return element.name == 'article' && element.hasClass('cmlex-article');
      }
    } );

        editor.ui.addButton(name, {label:text, command:name, icon:this.path + 'icons/' + name + '.png', toolbar: 'insert'});
  }
} );
