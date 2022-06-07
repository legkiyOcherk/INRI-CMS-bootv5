<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once('lib/class.Carusel.php');
require_once('lib/class.Image.php');

class Reviews extends Carusel{
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
          	<td style="width: 55px;">#</td>
      		  <td style="width: 50px;">Скрыть</td>
            <td style="width: 60px;">Картинка</td>
            <td style="width: 110px;">Дата</td>
      		  <td>Название</td>
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
              <a href="'.IA_URL.'$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.sqlDateToRusDate($date).'</a>
            </td>
            
            <td style="text-align: left;">
              <a href="'.IA_URL.'$this->carusel_name.'.php?edits='.$id.'" title="редактировать"><b>'.$title.'</b></a>';
    if($longtxt1){
      $output .= '
              <br>'.$longtxt1.'';  
    }
    $output .= '
            </td>';
            
    $output .= '
        	  <td style="" class="img-act">
              <a  href="..'.IA_URL.'$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm"
                  title = "Редактировать">
                <i class="fas fa-pencil-alt"></i>
              </a>
              
              <span >
              <span class="btn btn-danger btn-sm" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                <i class="far fa-trash-alt"></i>
              </span>
            </td>
  			  </tr>
  			  </tr>';
    
    return $output;
  }  
  
  function show_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a class="btn btn-info pull-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }  
      
      $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']));
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt3", "longtxt4"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      if( in_array($key, array("color"))) $type = 'color';
      if( in_array($key, array("date"))) $type = 'date';
      if( in_array($key, array("datetime"))) $type = 'datetime';
      
      // Отступы SEO
      if($key == 'seo_h1'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('SEO');
        $is_open_panel_div = true;   
      }
      
      if($key == 'img_alt'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Атрибуты основого изображения');
        $is_open_panel_div = true;   
      }
      
      if($item){
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'" >'
          );
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
        }
        
      }else{
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<input type="'.$type.'" name="'.$key.'"  value="">'
          );
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50></textarea>'
          );
        }
      }
      
    }
    
    if($is_open_panel_div){
      $is_open_panel_div = false; 
      $output .= $this->getCardPanelFooter();
    }
    
    $output .= $this->getFormPicture($id, $item);
      
    $output .= '</div>';
    
    return $output;
    
  }
  
    function show_table(){
    $output = "";
   
    $output .= $this->getFormStyleAndScript(); 
    
    $header = '<h1>'.$this->header.'</h1>';
    (!is_null($this->admin)) ?  : $output .=  $header;
    
    #$this->bread[ucfirst_utf8($this->header)] = $this->carusel_name.'.php'; 
    $this->title  = ucfirst_utf8($this->header);
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `".$this->prefix.$this->carusel_name."`
    ";
    
    $q = $this->pdo->query($s);
    $r = $q->fetch();
    $count_items = $r['count'];

    $s_filter = $s_sorting = $s_limit = $strPager = $groupOperationsCont = '';
    $s_order = " ORDER BY `date` DESC ";
    
    if(!$count_items) $output .= "<p>Раздел пуст</p>";
    if($this->is_filter &&  $count_items) $output .= $this->getFilterTable($s_filter);
    if( $count_items) $groupOperationsCont = $this->getGroupOperations();
    if($this->is_pager && $count_items) $strPager = $this->getPager( $count_items, $s_limit);
   
    $output .= $strPager;
    
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      $s_filter
      $s_sorting
      $s_order
      $s_limit
    ";
    #echo $s;
    
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortSlide"
        class="table-responsive"
      >
        <input type="hidden" name="slideid" value="1">
    ';
    if($q = $this->pdo->query($s))
      if($q->rowCount()){
        
        
    #if($items){
      
      $output .= '
  	    <table id="sortabler" class="table sortab table-condensed table-striped ">
          '.$this->show_table_header_rows();
      
      while($item = $q->fetch()){
        
        $output .= $this->show_table_rows($item);

        
      }
      
      $output .= '
        </table>
      ';
      
      
    }
    $output .= $groupOperationsCont;
    $output .= '
    <br>
  	<center><a class="btn btn-success " href="?adds" id="submit">Добавить</a></center>
    </form>';

    
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
}

$date_arr = array(
    'title' => 'Имя',
    'phone' => 'Телефон',
    'email' => 'E-mail',
    'date' => 'Дата',
    #'bani_id' => 'Регион',
    #'address' => 'Адрес',
    'longtxt1' => 'Сообщение',
    'answer' => 'Ответ',
    'ip' => 'IP',
  );
$pager = array(
        'perPage' => 10,
        'page' => 1,
        'url' => '',
        'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
      );
$carisel = new Reviews('reviews', $date_arr, true, true, $pager);

$arrfilterfield = array('title', 'longtxt1', 'longtxt2');

$carisel->setHeader('Отзывы');
$carisel->setIsUrl(false);
$carisel->setIsImages(false);
$carisel->setIsPager(true);
$carisel->setIsLog(true);
$carisel->setIsFilter(true);
$carisel->setFilterField($arrfilterfield); 
$carisel->setImg_ideal_width(300);  
$carisel->setImg_ideal_height(180); 
  
#$carisel->setDate_arr($date_arr);
if($output = $carisel->getContent($admin)){
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
