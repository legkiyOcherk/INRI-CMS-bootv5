<?php
require_once(__DIR__.'/lib/class.Admin.php');
$admin = new Admin();

if(  ( IS_AJAX_BACKEND == 1 ) ){
  require_once( __DIR__.'/lib/class.AjaxCarusel.php');
  class BlockClass extends AjaxCarusel {}
}else{
  require_once( __DIR__.'/lib/class.Carusel.php');  
  class BlockClass extends Carusel {}
} 
require_once( __DIR__.'/lib/class.Image.php' );


class MineCarusel extends BlockClass{
  
  function getAjaxCompleteScript(){
    $output = '';
    
    $output .= '
    <script>
      $(document).ajaxComplete(function() {
        CKEDITOR.replace( "longtxt1" );
      });
    </script>';
    
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
            <td style="max-width: 60px;">';
            
    if($img){
      $output .= '
            <div class="zoomImg" ><img style="width:50px;" src="../images/'.$this->carusel_name.'/slide/'.$img.'"></div>        ';
    }elseif($color){
      $output .= '
            <div class="zoomImg" style = "background-color: '.$color.'">';
    }
    $output .= '
            </td>
        	  
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>';
    if($link){
                $output .= '
                    <br><a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">Ссылка: '.trim(strip_tags($link)).'</a>';
              }
    $output .= '
            </td>
            
        	  <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).'
            </td>
  			  </tr>';
    
    return $output;
  }
   
}

$date_arr = array(
    'title'     => 'Название',
    'link'      => 'Ссылка',
    'txt1'      => 'Текст',
    'longtxt1'  => 'Описание',
    'hide'      => 'Скрыть',
    'img_alt'   => 'Alt изображение',
    'img_title' => 'Title изображение',
  );

$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);

$carisel = new MineCarusel('carusel', $date_arr, true, true, $pager  ); 


$carisel->setHeader('Слайдер на главной');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsPager(false);
$carisel->setIsLog(true);
$carisel->setImg_ideal_width(1920);  
$carisel->setImg_ideal_height(666); 
$carisel->checkbox_array = array('hide');                # Галочка в форме

if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
