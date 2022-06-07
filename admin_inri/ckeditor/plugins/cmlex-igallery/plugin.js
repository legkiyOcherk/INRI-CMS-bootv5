CKEDITOR.plugins.add('cmlex-igallery', {

	requires: 'widget',
	icons: 'cmlex-igallery',
	init: function(editor)
    {
     var name = 'cmlex-igallery';
     var text = 'Место для вывода фотогалереи';

		editor.widgets.add( name,
        {
			allowedContent:  'div(!' + name + ')',
			requiredContent: 'div(' + name + ')',

			template:
				'<div class="' + name + '">&nbsp;</div>',

			button: text,

			upcast: function(element)
            {
				return element.name == 'div' && element.hasClass(name);
			}
		} );

        editor.ui.addButton(name, {label:text, command:name, icon:this.path + 'icons/' + name + '.png', toolbar: 'insert'});
	}
} );


