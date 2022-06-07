CKEDITOR.plugins.add('cmlex-button2',
 {init:function(a)
  {
     var d =
        {
         canUndo:false,exec:function(f)
         {

         var Time = new Date(); id = Time.getTime();

         var e = f.document.createElement('a');
             e.setAttribute('class','cmlex-insert-button');
             e.setAttribute('href','#');
             e.setAttribute('data-form','order2');
             e.appendHtml('Текст кнопки');
             f.insertElement(e);
         }};

             var name = 'cmlex-button2';
             var text = 'Ссылка ввиде кнопки';
             a.addCommand(name, d);
             a.ui.addButton(name, {label:text, command:name, icon:this.path + 'icon.png', toolbar: 'insert'});
  }});