<?php
require_once( __DIR__.'/lib/class.Admin.php' );
$admin = new Admin();
if(  ( IS_AJAX_BACKEND == 1 ) ){
  require_once( __DIR__.'/lib/class.AjaxCarusel.php');
  class BlockClass extends AjaxCarusel {}
}else{
  require_once( __DIR__.'/lib/class.Carusel.php');  
  class BlockClass extends Carusel {}
} 
require_once( __DIR__.'/lib/class.Image.php');

class Article extends BlockClass{  

  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<td style="width: 55px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td style="width: 50px;">Фиксировать<br> на всем сайте</td>
      		  <td>Название</td>
            <td>Стандартный блок</td>
      		  <td style="width: 80px">Действие</td>
          </tr>';
    
    return $output;
  }
  
  function show_table_rows($item){
    $output = '';
    extract($item);
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
            <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td> 
            
            <td class="img-act"><div title="Фиксировать" onclick="star_check('.$id.', \'fl_is_fixed\')" class="star_check '.$this->getStarValStyle($fl_is_fixed).'" id="fl_is_fixed_'.$id.'"></div></td>  
            
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>
            <td style="text-align: left;">
              '.$link.'
            </td>';
            
    $output .= '
        	  <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).'
            </td>
  			  </tr>';
    
    return $output;
  }
  
  
  function show_table(){
    $output = "";
   
    $output .= parent::show_table(); 
    
    $output .= '<br>';
    $output .= '
    <pre>
    <p>Перечень стандартных блоков</p>
    block_mine_header    => Стандартная шапка
    block_mine_top_menu  => <a href="'.IA_URL.'smpl_article.php">Меню сайта</a>
    block_mine_slider    => <a href="'.IA_URL.'carusel.php">Слайдер</a>
    block_inner_content  => Контент на внутренних страницах
    block_mine_footer    => Стандартный подвал
    </pre>
    ';
    
    
    return $output;
    
  }
  
}

$date_arr = array(
    'title'       => 'Название',
    'link'        => 'Стандартный блок',
    'longtxt2'    => 'Контент',
    'fl_is_fixed' => 'Фиксировать',
    'hide'        => 'Скрыть',
    
  );
$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$arrfilterfield = array('title', 'link', 'longtxt2');

$carisel = new Article('mine_block', $date_arr, false, false, $pager);

$carisel->setHeader('Главная страница');
$carisel->setIsUrl(true);
$carisel->setIsImages(false);
$carisel->setIsFiles(false);
$carisel->setIsPager(false);
$carisel->setIsFilter(false);
$carisel->setFilterField($arrfilterfield); 
$carisel->setIsLog(true);

$carisel->setImg_ideal_width(750);  
$carisel->setImg_ideal_height(410);
#$carisel->setDate_arr($date_arr);
$carisel->checkbox_array = array('fl_is_fixed', 'hide');                # Галочка в форме

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
} 