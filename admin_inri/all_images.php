<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Images.php');
require_once('lib/class.Image.php');

class AllImages extends Images{
  
}
$date_arr = array(
    'title'           => 'Название',
    'longtxt1'        => 'Краткий текст',
    'longtxt2'        => 'Полный текст',
    'hide'            => 'Скрыть',
    'module'          => 'Название модуля (таблицы бд к которой привязано изображение)',
    'module_id'       => 'Название модуля id модуля',
    'seo_h1'          => 'SEO h1',
    'seo_title'       => 'SEO Title',
    'seo_description' => 'SEO Description',
    'img_alt'         => 'Alt изображение',
    'img_title'       => 'Title изображение'
  );

$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'module', 'module_id');

$all_images = new AllImages('all_images', $date_arr, false, false, $pager);

$all_images->setHeader('Изображения');
//ЧПУ
#$all_images->setIsUrl(false);
$all_images->setIsPager(true);
$all_images->setIsFilter(true);
$all_images->setFilterField($arrfilterfield);

$all_images->setImg_ideal_width(1920);  
$all_images->setImg_ideal_height(1080); 
  
#$all_images->setDate_arr($date_arr);

$all_images->checkbox_array = array('hide');                # Галочка в форме

if($output = $all_images->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}