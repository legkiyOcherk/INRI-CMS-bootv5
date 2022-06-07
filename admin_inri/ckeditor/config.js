/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config )
{
config.language = 'ru';
//config.leaflet_maps_google_api_key = 'AIzaSyA9ySM6msnGm0qQB1L1cLTMBdKEUKPySmQ';

/* cmlex_box,cmlex_tabs,cmlex_timer,lineutils,widget,leaflet,cmlex-article,cmlex-button,cmlex-igallery,cmlex-spoiler,cmlex-box,cmlex-bootstrap */
config.extraPlugins = 'lineutils,widget,widgetselection,quicktable,youtube,codemirror,wordcount,glyphicons';
// ckeditor/plugins/glyphiconspt
config.contentsCss = '/css/ckeditor.css';
config.allowedContent = true;
// end ckeditor/plugins/glyphiconspt

config.forcePasteAsPlainText = true;
config.autoParagraph = false;

/*config.filebrowserBrowseUrl = '/wordpad/plugins/AjexFileManager/elfinder.php';*/

// config.filebrowserUploadUrl = '../../ckeditor_upload_img.php';
   config.filebrowserBrowseUrl = 'kcfinder/browse.php?opener=ckeditor&type=files';
   config.filebrowserImageBrowseUrl = 'kcfinder/browse.php?opener=ckeditor&type=images';
   config.filebrowserFlashBrowseUrl = 'kcfinder/browse.php?opener=ckeditor&type=flash';
   config.filebrowserUploadUrl = 'kcfinder/upload.php?opener=ckeditor&type=files';
   config.filebrowserImageUploadUrl = 'kcfinder/upload.php?opener=ckeditor&type=images';
   config.filebrowserFlashUploadUrl = 'kcfinder/upload.php?opener=ckeditor&type=flash';


config.height = 350;

config.toolbar = 'FullToolbar';

config.extraAllowedContent = 'script span article a[*](*){*}';  // atrb class style
// config.indentClasses = ['cmlex-article'];

config.font_names =
    'Arial/Arial, Helvetica, sans-serif;' +
    'Times New Roman/Times New Roman, Times, serif;' +
    'Georgia/Georgia, Times New Roman, Times, serif;' +
    'Tahoma/Tahoma, Geneva, sans-serif;' +
    'Comic/Comic Sans MS, cursive;' +
    'Courier/Courier New, Courier, monospace;' +
    'Verdana/Verdana, Geneva, sans-serif';

/* 'Preview', 'Templates', 'Paste', 'PasteText', 'Print', 'SpellChecker', 'Scayt', 'Styles', 'NewPage', 'PageBreak', 'Smiley', 'About','cmlex_box','cmlex_tabs','leaflet','cmlex_timer' */

config.toolbar_FullToolbar =
[
    ['Source','-','Maximize','-','ShowBlocks'],['SelectAll','-','Cut','Copy','PasteFromWord'],
    ['Undo','Redo','-','Find','Replace'], ['Link','Unlink','Anchor'],
    ['Table','Image','Youtube','Flash','CreateDiv','Iframe','SpecialChar','Glyphicons'],
    /*['Templates','cmlex-button','cmlex-igallery','cmlex-spoiler','cmlex-box'],*/
    '/',
    ['RemoveFormat','Format','Font','FontSize'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['TextColor','BGColor'],
    ['HorizontalRule','-','NumberedList','BulletedList','-','Outdent','Indent','Blockquote']
];

config.toolbar_MiniToolbar =
[
    ['Source','-','Maximize'],['Cut','Copy','PasteFromWord', 'Undo','Redo'],
    ['Link','Unlink','Anchor', 'Table','Image','SpecialChar'],
    ['Bold','Italic','Underline','Strike'],['TextColor','BGColor'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']
];

};

       /* config.indentClasses = ["ul-grey", "ul-red", "text-red", "ul-content-red", "circle", "style-none", "decimal", "paragraph-portfolio-top", "ul-portfolio-top", "url-portfolio-top", "text-grey"];
        config.protectedSource.push(/<(style)[^>]*>.*<\/style>/ig);
        config.protectedSource.push(/<(script)[^>]*>.*<\/script>/ig);// разрешить теги <script>
        config.protectedSource.push(/<(i)[^>]*>.*<\/i>/ig);// разрешить теги <i>
        config.protectedSource.push(/<\?[\s\S]*?\?>/g);// разрешить php-код
        config.protectedSource.push(/<!--dev-->[\s\S]*<!--\/dev-->/g);
        config.allowedContent = true; /* all tags */