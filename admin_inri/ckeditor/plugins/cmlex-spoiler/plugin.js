CKEDITOR.plugins.add('cmlex-spoiler', {

	requires: 'widget',
	icons: 'cmlex-spoiler',
	init: function(editor)
    {
     var name = 'cmlex-spoiler';
     var text = 'Вставка разворачиваемого пользователем блока (спойлер)';

		editor.widgets.add( name,
        {
			allowedContent:  'div(!cmlex-insert-spoiler)',
			requiredContent: 'div(cmlex-insert-spoiler)',

			editables:
            {
				content:
                {
					selector: '.cmlex-insert-spoiler',
				}
			},

			template:
				'<div class="cmlex-insert-spoiler"><p>Содержимое скрытого блока</p></div>',

			button: text,

			upcast: function(element)
            {
				return element.name == 'div' && element.hasClass('cmlex-insert-spoiler');
			}
		} );

        editor.ui.addButton(name, {label:text, command:name, icon:this.path + 'icons/' + name + '.png', toolbar: 'insert'});
	}
} );