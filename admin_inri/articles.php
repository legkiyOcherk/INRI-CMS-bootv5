<?php
require_once( __DIR__.'/lib/class.Admin.php' );
$admin = new Admin();

if(  ( IS_AJAX_BACKEND == 1 ) ){
  require_once( __DIR__.'/lib/class.AjaxCatCarusel.php' );
  class BlockClass extends AjaxCatCarusel {} 
}else{
  require_once( __DIR__.'/lib/class.CatCarusel.php' ); 
  class BlockClass extends CatCarusel {} 
}
require_once( __DIR__.'/lib/class.Image.php' );
require_once( __DIR__.'/../vendors/phpmorphy/phpmorphy_init.php' ); // Морфология

function get_phpmorphy($descr_str) {
    global $morphy;
    
    $descr_str = strip_tags($descr_str);
    
    $descrs = str_word_count($descr_str, 1, "АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя0123456789");
    $orm_search = '';
    foreach($descrs as $descr){
      
      $des = mb_strtoupper($descr, 'UTF-8');
      //echo "des = $des<br>";
      $collection = $morphy->findWord($des);
      if(false === $collection) { 
        #echo $des, " NOT FOUND\n<br>";
        $orm_search .= $des." ";
        continue;
      } else {
        
      }
    
      foreach($collection as $paradigm) {
        #echo "lemma: ", $paradigm[0]->getWord(), "\n<br>";
        $orm_search .= $paradigm[0]->getWord()." ";
        break;
      }
    }
    
    $orm_search = trim($orm_search);
    
    return $orm_search;
}

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

class Article extends BlockClass{
  
  function show_cat_table_rows($item, $i = 0){
    $output = '';
              extract($item);
              $output .= '
                <tr class="r'.($i % 2).'" id="trc_'.$id.'" style="cursor: move;">			 
                  <td style="width: 20px;">'.$id.'<input type="hidden" value="'.$id.'" name="itCatSort[]"></td>
                  
                  <td style="width: 30px;" class="img-act"><div title="Скрыть" onclick="star_cat_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>
              	  
                  <td style="width: 50px;">
              ';
              if($img){
                $output .= '
                  <div class="zoomImg"><img style="width:50px" src="../images/'.$this->carusel_name.'/cat/slide/'.$img.'"></div>  
                ';
              }else if( isset($color) && $color ){
                $output .= '
                  <div class="zoomImg" style = "background-color: '.$color.'">
                ';
              }
              $output .= '
                  </td>
              	  
                  <td style="text-align: left;">
                    <a  href="..'.IA_URL.$this->carusel_name.'.php?editc='.$id.'" 
                        class = "btn btn-info btn-sm"
                        title = "Редактировать"
                        style = "color: #fff;">
                        <i class="fas fa-pencil-alt"></i>
                    </a> &nbsp;
                    <a href="'.IA_URL.$this->carusel_name.'.php?c_id='.$id.'" title="редактировать"><b>'.$title.'</b></a>';
              if($link){
                $output .= '
                    <br>Ссылка: '.trim(strip_tags($link)).'</span>';
              }
              if($longtxt1){
                $output .= '
                    <br><span>'.trim(strip_tags($longtxt1)).'</span>';
              }
              $output .= '
                  </td>';

              $output .= '
                  <td style="" class="action_btn_box">
                    '.$this->show_cat_table_row_action_btn($id).'
                  </td>
        			  </tr>
              ';
    
    return $output;
  }
  
  function show_table_header_rows(){
    $output = '
          <tr class="tth nodrop nodrag">
          	<th style="width: 55px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
            <th style="width: 60px;">Картинка</th>
      		  <th>Название</th>
      		  <th style="width: 80px">Действие</th>
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
    }elseif( isset($color) && $color ){
      $output .= '
            <div class="zoomImg" style = "background-color: '.$color.'">';
    }
    $output .= '
            </td>
        	  
            <td style="text-align: left;">
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'"  
                  class = "btn btn-info btn-sm"
                  title = "Редактировать"
                  style = "color: #fff;">
                  <i class="fas fa-pencil-alt"></i>
              </a> &nbsp;
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a><br />
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$longtxt1.'</a>
              
            </td>
            
            <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).'
            </td>
  			  </tr>';
    
    return $output;
  }
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    
    $_POST['orm_search_name'] = $_POST['orm_search'] = '';
    if($_POST['title'])    $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    if($_POST['longtxt2']) $_POST['orm_search']      = get_phpmorphy($_POST['longtxt2']);
    
    $i=0;
    foreach($this->date_arr as $key=>$val){
      ($i) ? $prefix = ', ' : $prefix = ''; $i++;
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      
    };
  }
  
  function getUpdateSlide_SqlVals(){
    $sql_vals = ''; 
    
    $_POST['orm_search_name'] = $_POST['orm_search'] = '';
    if($_POST['title'])    $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    if($_POST['longtxt2']) $_POST['orm_search']      = get_phpmorphy($_POST['longtxt2']);
    
    $i=0;
    foreach($this->date_arr as $key=>$val){

      ($i) ? $prefix = ', ' : $prefix = ''; $i++;
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      
    }
    return $sql_vals;
  }
  
  function getCreateCatSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    
    $_POST['orm_search_name'] = $_POST['orm_search'] = '';
    if($_POST['title'])    $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    if($_POST['longtxt2']) $_POST['orm_search']      = get_phpmorphy($_POST['longtxt2']);
    
    $i=0;
      
    foreach($this->date_cat_arr as $key=>$val){
      if( in_array( $key, $this->checkbox_cat_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
  }
  
  function getUpdateCatSlide_SqlVals(){
    $sql_vals = ''; 
    
    $_POST['orm_search_name'] = $_POST['orm_search'] = '';
    if($_POST['title'])    $_POST['orm_search_name'] = get_phpmorphy($_POST['title']);
    if($_POST['longtxt2']) $_POST['orm_search']      = get_phpmorphy($_POST['longtxt2']);
    
    $i=0;
    
    foreach($this->date_cat_arr as $key=>$val){
      if( in_array( $key, $this->checkbox_cat_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      ($i) ? $prefix = ', ' : $prefix = '';
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
  }
  
  function show_cat_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->cat_carusel_name, $id);
      
      if($url){
        $tmp = '<a class="btn btn-info pull-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      } 
        
       $output .= $this->show_form_row('ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->cat_carusel_name, $id, $item['title']));  
      
    }
    
    
       
    foreach($this->date_cat_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; $is_color = false;
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      //if( in_array($key, array("color"))) $is_color = true;
      
      $type = '';
      $create_val = '';
      if( in_array($key, array("color"))) {$type = 'color'; $create_val = '#FFFFFF';}
      if( in_array($key, array("date"))) { $type = 'date'; $class_input = ' class="form-control" style = "max-width: 180px;" '; }
      if( in_array($key, array("title", "link", "seo_h1", "seo_title", "seo_keywords", "img_alt", "img_title" ))) $type = 'text';
      
      if($key == 'is_enlarge_photos'){
        ($item && $item[$key]) ? $coldate = 'checked' : $coldate = ''; #pri($coldate);
        
        $output .= $this->show_form_row( 
          $val.$this->getErrorForKey($key), 
            '<input type="checkbox" class="col_checkbox" name="'.$key.'" '.$coldate.'>'
          );
          
        $output .= '
            <script type="text/javascript">
              $(document).ready(function(){
                $(".col_checkbox").iCheck({
                  checkboxClass: "icheckbox_flat-red",
                  radioClass: "iradio_flat-red"
                });
              });
            </script>';
        
        continue;  
      }
      
      if($key == 'parent_id'){
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if($item) {
          $_SESSION[$this->carusel_name]['c_id'] = htmlspecialchars($item[$key]);
          $this->bread = array();
          $this->show_bread_crumbs($item_cat_id);
          $this->admin->setForName('bread', $this->getForName('bread')); 
        }
        if(!$item_cat_id) $item_cat_id = 0;
        
        $tmp  = '<select name="parent_id" class="form-control">';
        $tmp .= $this->get_category_option($item_cat_id);
        $tmp .= '</select>';
        
        #$output .= '<input type = "hidden" name = "parent_id" value = "'.$item_cat_id.'">';
        $output .= $this->show_form_row( $val, $tmp); 
        continue;
      }
      
      // Вспомогательные поля для храниения поискового индекса
      if(($key == 'orm_search_name') || ($key == 'orm_search')) continue;
      
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
            $val.$this->getCatErrorForKey($key), 
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'">'
          );
          
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
         
        }
        
      }else{
        if($type){
          $output .= $this->show_form_row( 
            $val.$this->getCatErrorForKey($key), 
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.$create_val.'">'
          );
          
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.$create_val.'</textarea>'
          );
          
        }
      }
      
    }
    
    if($is_open_panel_div){
      $is_open_panel_div = false; 
      $output .= $this->getCardPanelFooter();
    }
    
    $output .= ' </div> ';
    
    $output .= ' Изображение  (Иделальный размер '.$this->img_cat_ideal_width.' x '.$this->img_cat_ideal_height.'):';
    $output .= '<BR/><INPUT type="file" name="picture" id = "fr_picture" value="" class="w100"><BR/>';
    
    return $output;
    
  }
  
  function show_form($item = null, $output = '', $id = null){
    $title = '';
    $output .= '<div class = "c_form_box">';
    
    /*$output .= '
      <div class="panel panel-default"> 
        <div class="panel-heading"> 
          <h3 class="panel-title">Основное</h3>
        </div> 
        <div class="panel-body"> 
    ';*/
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a href="/'.$url.'" class="btn btn-info pull-right" style="color:#fff"><i class="icon-eye-open icon-white"></i> Посмотреть на сайте</a>';
        $output .= $this->show_form_row(null, $tmp);
      }
      
      $output .= $this->show_form_row( 'ЧПУ', $this->url_item->show_form_field($_POST['url'], $this->prefix.$this->carusel_name, $id, $item['title']) );
    }
    
    foreach($this->date_arr as $key=>$val){
      
      $class_input = ' class="form-control" '; 
      if( in_array($key, array("longtxt1", "longtxt2", "longtxt2"))) $class_input = ' class="ckeditor" '; 
      
      $type = '';
      $create_val = '';
      if( in_array($key, array("color"))) {$type = 'color'; $create_val = '#FFFFFF';} 
      if( in_array($key, array("date"))) { $type = 'date'; $class_input = ' class="form-control" style = "max-width: 180px;" '; }
      if( in_array($key, array("title", "link", "seo_h1", "seo_title", "seo_keywords", "img_alt", "img_title" ))) $type = 'text';
      
      if($key == 'cat_id'){
        $tmp  = '<select name="cat_id" class="form-control">';
        ($item) ? $item_cat_id = htmlspecialchars($item[$key]) : $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
        if($item) {
          $_SESSION[$this->carusel_name]['c_id'] = htmlspecialchars($item[$key]);
          $this->bread = array();
          $this->show_bread_crumbs($item_cat_id);
          if($_SESSION[$this->carusel_name]['c_id']){
            $c_title = db::value('title', '`'.$this->cat_carusel_name.'`', "id = ".$_SESSION[$this->carusel_name]['c_id'] );
            $title .='<a href="?c_id='.$_SESSION[$this->carusel_name]['c_id'].'"> '.$c_title.' </a> → ';
          }
          $title .=' редактирование записи';
          
          $this->title = $title;
        }
        if(!$item_cat_id) $item_cat_id = 0;
        $tmp .= $this->get_category_option($item_cat_id);
        $tmp .= '</select>';
        
        $output .= $this->show_form_row( $val, $tmp);
        continue;  
      }
      
      if($key == 'is_enlarge_photos'){
        ($item && $item[$key]) ? $coldate = 'checked' : $coldate = ''; #pri($coldate);
        
        $output .= $this->show_form_row( 
          $val.$this->getErrorForKey($key), 
            '<input type="checkbox" class="col_checkbox" name="'.$key.'" '.$coldate.'>'
          );
          
        $output .= '
            <script type="text/javascript">
              $(document).ready(function(){
                $(".col_checkbox").iCheck({
                  checkboxClass: "icheckbox_flat-red",
                  radioClass: "iradio_flat-red"
                });
              });
            </script>';
        
        continue;  
      }
      
      // Вспомогательные поля для храниения поискового индекса
      if(($key == 'orm_search_name') || ($key == 'orm_search')) continue;
      
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
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.htmlspecialchars($item[$key]).'" >'
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
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="'.$create_val.'">'
          );
    
        }else{
          $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.$create_val.'</textarea>'
          );
    
        }
      }
      
    }
    
    if($is_open_panel_div){
      $is_open_panel_div = false; 
      $output .= $this->getCardPanelFooter();
    }
    $output .= $this->getFormPicture($id, $item);
      
    $output .= ' </div> ';
    
    return $output;
    
  }
  
  function full_tree(){
    
    if( isset($_SESSION[$this->carusel_name]['c_id']) && $_SESSION[$this->carusel_name]['c_id'] ){
      $item_cat_id = $_SESSION[$this->carusel_name]['c_id'];
      $this->bread = array();
      $this->show_bread_crumbs($item_cat_id);
      $this->admin->setForName('bread', $this->getForName('bread')); 
    }
    $this->title = 'Полный каталог'; 
    $this->header = '<h1><a href = "'.IA_URL.$this->carusel_name.'.php?c_id=root">'.$this->header.'</a></h1>';
    $output .=  '
    <table class="table table-condensed">
      <tr class="r0"><td><a href="?view_tree">Дерево всех категорий</a></td></tr>
      <tr class="r1"><td><a href="?full_tree">Полный каталог</a></td></tr>
    </table>';
    $output .= '
		  <script>
		  $(function(){
			  $("#article").keyup(function(){
			    var q=$(this).val();
			    $.post("'.$this->carusel_name.'.php?ajx&act=search", {que:q}).done(function( data ) 
				  {
				  	$("#exists").html(data);
				  });
		    });
		  });
		  </script>

      <div class="container">
        <div class="row">
          <div class="col-sm-12 col-md-7 col-lg-8">
            <div class="row">
              <div class="col-xs-12">';
	  $output .= $this->show_entrie_catalog();
    $output .= '
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-5 col-lg-4">
            <div class="row">
              
                <div class="box box-primary box-solid">
                  <div class="box-header with-border">
                    <h3 class="box-title">Поиск</h3>

                    <div class="box-tools pull-right">
                      <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>                    <!-- /.box-tools -->
                  </div>                      <!-- /.box-header -->
                  <div class="box-body">
                    <div class="form-group">
                      <input type="text" class="text form-control" name="article" id="article" placeholder="Запрос..."> 
                    </div>
                  </div>                      <!-- /.box-body -->
                </div>
              
                
            </div>
            
            <div class="row">
              <div class="" id="exists"></div>
            </div>
          </div>
        </div>
      </div>
      
      <script>
      $(function(){
        $(".posit").popover({
	        html:true,
	        trigger:"hover",
	        placement:"top"
        })
      });
      </script>
      
      <style>
      .listingb li
      {
	      padding: 5px 0 0 0;
      }
      .listingb{
        padding:5px 0;
        margin: 0 0 0 15px;
      }
      .listingb a{
        color: #265c88;
      }
      .container{
        width: 100%;
      }
      .listingb .label{
        color: #000000;
      }
      
      </style>
    ';
    
    return $output;
  }
  
}


$date_arr = array(
    'title'             => 'Название',
    'cat_id'            => 'Категория',
    #'substring'         => 'Подстрочник',
    'date'              => 'Дата',
    'longtxt1'          => 'Краткий текст',
    'longtxt2'          => 'Полный текст (для отдельной страницы)<br><small style = "color:#28a745">Для вставки слайдера: %slider%</small>',
    #'is_enlarge_photos' => 'Увеличивать фото<br>в описании',
    'seo_h1'            => 'SEO H1',
    'seo_title'         => 'SEO Title',
    'seo_description'   => 'SEO Description',
    'seo_keywords'      => 'SEO Keywords',
    'img_alt'           => 'Alt изображение',
    'img_title'         => 'Title изображение',
    'orm_search_name'   => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'        => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса
  );

$date_cat_arr = array(
    'title'             => 'Название',
    'parent_id'         => 'Категория',
    'link'              => 'Ссылка',
    #'substring'         => 'Подстрочник',
    'longtxt1'          => 'Краткий текст',
    'longtxt2'          => 'Полный текст (для отдельной страницы)',#<br><small style = "color:#28a745">Для вставки слайдера: %slider%</small>',
    #'is_enlarge_photos' => 'Увеличивать фото<br>в описании',
    'seo_h1'            => 'SEO H1',
    'seo_title'         => 'SEO Title',
    'seo_description'   => 'SEO Description',
    'seo_keywords'      => 'SEO Keywords',
    'img_alt'           => 'Alt изображение',
    'img_title'         => 'Title изображение',
    'orm_search_name'   => 'поле для поискового индекса orm_search_name', // Вспомогательное поле для храниения поискового индекса
    'orm_search'        => 'поле для поискового индекса orm_search', // Вспомогательное поле для храниения поискового индекса
  );
$pager = array(
  'perPage' => 50,
  'page' => 1,
  'url' => '',
  'items_per_page' => array( 50, 100, 500, 1000, 5000)
);
$arrfilterfield = array('title', 'longtxt1', 'longtxt2');
  
$carisel = new Article('articles', $date_arr, $date_cat_arr, false, false, $pager);

$carisel->setHeader('КАТАЛОГ СТАТЕЙ');
$carisel->setIsUrl(true);
$carisel->setIsImages(true);
$carisel->setIsFiles(true);
$carisel->setIsLog(true);
$carisel->setIsPager(true);
$carisel->setIsFilter(true);
$carisel->setFilterField($arrfilterfield); 
$carisel->setImg_ideal_width(650);  
$carisel->setImg_ideal_height(650);
#$carisel->setDate_arr($date_arr);


if($output = $carisel->getContent($admin)){
  #pri($carisel->admin);
  $admin->setContent($output);
  echo $admin->showAdmin('content');
}
