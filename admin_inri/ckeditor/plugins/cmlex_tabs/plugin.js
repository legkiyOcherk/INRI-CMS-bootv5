CKEDITOR.plugins.add('cmlex_tabs',
 {init:function(a){
     var d={
         canUndo:false,exec:function(f){

         var e = f.document.createElement('div');
             e.setAttribute('class','cmlex_tabs_box');
             e.appendHtml('<div class="cmlex_tabs_head"><p>Название таба</p></div><div class="cmlex_tabs_text"><p>Текст скрытой страницы таба</p></div>');
             f.insertElement(e);
             }};

             var name = 'cmlex_tabs';
             var text = 'Вставка дополнительно таба (только для товара)';
             a.addCommand(name, d);
             a.ui.addButton(name, {label:text, command:name, icon:this.path+'icon.png', toolbar: 'insert'});}});