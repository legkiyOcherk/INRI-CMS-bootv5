/*
 Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.addTemplates("default",
{
imagesPath:CKEDITOR.getUrl(CKEDITOR.plugins.getPath("templates") + "templates/images/"),

templates:
  [
     {
      title:"Адаптивный блок 100%",
      image:"col-1.png",
      description:"На мобильном устройстве данные колонки будут выстроены одна под одной",
      html:'<div class="cmlex-bootstrap cmlex-row"><div class="cmlex-bootstrap-col cmlex-col-100"><p>Информация</p></div></div>'
     },

     {
      title:"Адаптивный блок на 2 колонки",
      image:"col-2.png",
      description:"На мобильном устройстве данные колонки будут выстроены одна под одной",
      html:'<div class="cmlex-bootstrap cmlex-row"><div class="cmlex-bootstrap-col cmlex-col-50"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-50"><p>Информация</p></div></div>'
     },

     {
      title:"Адаптивный блок на 3 колонки",
      image:"col-3.png",
      description:"На мобильном устройстве данные колонки будут выстроены одна под одной",
      html:'<div class="cmlex-bootstrap cmlex-row"><div class="cmlex-bootstrap-col cmlex-col-33"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-33"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-33"><p>Информация</p></div></div>'
     },

     {
      title:"Адаптивный блок на 4 колонки",
      image:"col-4.png",
      description:"На мобильном устройстве данные колонки будут выстроены одна под одной",
      html:'<div class="cmlex-bootstrap cmlex-row"><div class="cmlex-bootstrap-col cmlex-col-25"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-25"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-25"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-25"><p>Информация</p></div></div>'
     },

     {
      title:"Адаптивный блок на 2 колонки 30% + 70%",
      image:"col-2-r.png",
      description:"На мобильном устройстве данные колонки будут выстроены одна под одной",
      html:'<div class="cmlex-bootstrap cmlex-row"><div class="cmlex-bootstrap-col cmlex-col-33"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-67"><p>Информация</p></div></div>'
     },

     {
      title:"Адаптивный блок на 2 колонки 70% + 30%",
      image:"col-2-l.png",
      description:"На мобильном устройстве данные колонки будут выстроены одна под одной",
      html:'<div class="cmlex-bootstrap cmlex-row"><div class="cmlex-bootstrap-col cmlex-col-67"><p>Информация</p></div><div class="cmlex-bootstrap-col cmlex-col-33"><p>Информация</p></div></div>'
     }
  ]
});