<?php
require_once __DIR__."/class.BaseCarusel.php";

class Carusel extends BaseCarusel{
  
  var $img_ideal_width = 960;
  var $img_ideal_height = 500;

  
  var $date_arr = array(
    'title' => 'Название',
    'img_alt' => 'Alt изображение',
    'img_title' => 'Title изображение',
    'link' => 'Ссылка',
    'txt1' => 'Текст на синем фоне',
    'txt2' => 'Текст на белом фоне',
    'tongtxt1' => 'Описание бла бла бла',
  );
  var $pdo;
  
  var $url_item = null;      // Генерация url
  var $images_items = null;  // Модуль картинок
  var $files_items = null;   // Модуль файлов
  var $log = null;           // Вести лог
  var $is_pager = false;     // Отображать пэйджер
  var $is_filter = null;     // Отображать фильтр
  
  var $carusel_name;
  var $sqlTable;
  var $validateError_arr = array();
  #var $items_per_page = array( 50, 100, 500, 1000);
  var $pager = array(
        'perPage' => 50,
        'page' => 1,
        'url' => '',
        'items_per_page' => array( 50, 100, 500, 1000, 5000)
      );
      
  var $filter_field = array('title');
  
  
  // конструктор
  function __construct ($carusel_name = null, $date_arr = null, $genSqlTable = false, $genImgDir = false, $pager = null) {
     
    //Для пересоздания раскоментить
    #$_SESSION[$carusel_name]['is_table'] = 0;
    #$_SESSION[$carusel_name]['img_dir'] = 0; 
    //END Для пересоздания раскоментить
    
    $this->pdo = db_open();
    $this->carusel_name = "carusel_01";
    
    if($carusel_name){
      $this->carusel_name = $carusel_name;
    }else{
      $this->carusel_name = "carusel_01";
    }
    
    $this->sqlTable = $this->prefix.$this->carusel_name;  
    
    if($date_arr) $this->date_arr = $date_arr;
  
        
    //Если нет таблицы в базе данных, то создаем ее
    if($genSqlTable){
      
      $create_table = 0;
      if(!isset($_SESSION[$carusel_name]['is_table'])){
        $create_table = 1;
      }elseif($_SESSION[$carusel_name]['is_table'] != 1){
        $create_table = 1;
      }
      
      if($create_table){
          $s = 'SHOW TABLES LIKE "'.$this->sqlTable.'"';  
          $q = $this->pdo->query($s);
          if(!$q->rowCount()){
            // create_sql_table - Создавать таблицу если ее нет
            if($this->create_sql_table()) $_SESSION[$carusel_name]['is_table'] = 1;
            
          }else{
            $_SESSION[$carusel_name]['is_table'] = 1;
          }
      }
    }
    
    //Если нет папок для картинок создаем их
    if($genImgDir){
      
      $create_img_dir = 0;
      if(!isset($_SESSION[$carusel_name]['img_dir'])){
        $create_img_dir = 1;
      }elseif($_SESSION[$carusel_name]['img_dir'] != 1){
        $create_img_dir = 1;
      }
      
      if($create_img_dir){
        // create_img_dir - Создавать дерево директорий если его нет
        $this->create_img_dir();
        $_SESSION[$carusel_name]['img_dir'] = 1;
      }
    }
    
    //Если нужен пэйджер
    if($pager){
      if(is_array($pager)){
        $this->pager = $pager;
      }
      $this->is_pager = true;
      $this->setPagerParamers();
    }
    
    
  }
  
  // Инициалицация
  function create_sql_table(){
    
    #$arr_varchar_255_default_null = array( ); // varchar(255) DEFAULT NULL
    
    $arr_varchar_255_not_null = array(         // varchar(255) NOT NULL
      'article' );
    
    $arr_varchar_10_default_null = array(      // varchar(10) DEFAULT NULL
      'date', 'datetime' );
    
    $arr_text = array(                         // text
      'longtxt1', 'longtxt2', 'longtxt3', 'orm_search' );
    
    $arr_tinyint_1_default_null = array(       // tinyint(1) DEFAULT NULL      
      'fl1', 'fl2', 'fl3', 'fl_mine_menu', 'fl_show_mine' );
    
    $arr_int_11_default_null = array(          // int(11) DEFAULT NULL
      'price', 'userPhone');
    
    $arr_ignore = array(                       // ignore field
      'title', 'ignore', 'hide', 'ord' );
    
    //genprok
    $arr_text[] = 'site_item_descr';
    $arr_text[] = 'site_item_fulltext';
    $arr_text[] = 'site_callback';
    
    $arr_text[] = 'rec_descr';
    $arr_text[] = 'rec_fulltext';
    
    $arr_tinyint_1_default_null[] = 'is_published';
    
    
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->sqlTable.'` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      ';
    $sql .= '`img` varchar(255) NOT NULL,
      ';
    foreach($this->date_arr as $key=>$val){
      if(in_array($key, $arr_varchar_255_not_null)){
        $sql .= '`'.$key.'` varchar(255) NOT NULL,
      ';  
      }elseif(in_array($key, $arr_varchar_10_default_null)){
        $sql .= '`'.$key.'` varchar(10) DEFAULT NULL,
      ';
      }elseif(in_array($key, $arr_text)){
        $sql .= '`'.$key.'` text,
      ';
      }elseif(in_array($key, $arr_tinyint_1_default_null)){
        $sql .= '`'.$key.'` tinyint(1) DEFAULT NULL,
      ';
      }elseif(in_array($key, $arr_int_11_default_null)){
        $sql .= '`'.$key.'`  int(11) DEFAULT NULL,
      ';
      }elseif(in_array($key, $arr_ignore)){
        continue;
      }else{
        $sql .= '`'.$key.'`  varchar(255) DEFAULT NULL,
      ';
      }
      
    }
      
    $sql .= '
      `hide` tinyint(1) NOT NULL DEFAULT "0",
      `ord` int(11) NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ';
    
    #echo "<pre>sql = $sql<br></pre>"; die();
    
    if($q = $this->pdo->query($sql)){
      echo "База данных ".$this->sqlTable." Успешно созданна";
      return true;
    }else{
      echo "Ошибка при создании базы данных ".$this->sqlTable." : ".$q;
      echo "<pre>".$sql."</pre>";
      echo $q->errorInfo();
      exit;
      return false;
    }
  }
  
  // End Инициалицация
  
  // Валидация введенных данных

  // End Валидация введенных данных
  
  // All Method
  
  function setIsUrl($is_url) {
    require_once('lib/class.Url.php');
    if($is_url) $this->url_item = new Url('url');
  }
  
  function setIsImages($is_images) {
    require_once('lib/class.Images.php');
    if($is_images) $this->images_items = new Images('all_images');
  }
  
  function setIsFiles($is_files) {
    require_once('lib/class.Files.php');
    if($is_files) $this->files_items = new Files('all_files');
  }
  
  function setIsLog($is_log) {
    require_once('lib/class.Log.php');
    if($is_log) $this->log = new Log('all_log');
  }
  
  // END All Method
  
  // AJAX function
 
  // END AJAX function
  
  // BACKEND function

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
            <td class = "zoomImg_box" style="">';
            
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
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>';
            
    $output .= '
        	  <td style="" class="action_btn_box">
              '.$this->show_table_row_action_btn($id).'
            </td>
  			  </tr>';
    
    return $output;
  }
  
  function show_table_row_action_btn($id){
    $output = '';
    
    $output .= '
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" 
                  class = "btn btn-info btn-sm"
                  title = "Редактировать">
                <i class="fas fa-pencil-alt"></i>
              </a>
              
              <span class="btn btn-danger btn-sm" 
                    title="удалить" 
                    onclick="delete_item('.$id.', \'Удалить элеемент?\', \'tr_'.$id.'\')">
                <i class="far fa-trash-alt"></i>
              </span>
    ';
    
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
    $q = $this->pdo->query($s); $r = $q->fetch(); $count_items = $r['count'];
    
    $where = $s_sorting = $s_limit = $strPager = $groupOperationsCont = '';
    $s_order = " ORDER BY `ord` ASC ";
      
    if(!$count_items) $output .= "<p>Раздел пуст</p>";
    if($this->is_filter &&  $count_items) $output .= $this->getFilterTable($where);
    
    $s = "
      SELECT COUNT( * ) AS count
      FROM `".$this->prefix.$this->carusel_name."`
      $where
    "; #pri($s);
    $q = $this->pdo->query($s); $r = $q->fetch(); $count_items = $r['count'];
    
    if( $count_items) $groupOperationsCont = $this->getGroupOperations();
    if($this->is_pager && $count_items) $strPager = $this->getPager( $count_items, $s_limit);
   
    $output .= $strPager;
    
    $s = "
      SELECT *
      FROM `".$this->prefix.$this->carusel_name."`
      $where
      $s_sorting
      $s_order
      $s_limit
    "; #pri($s);
    
    $output .= '
      <form 
        method="post" 
        action="'.$this->carusel_name.'.php" 
        id="sortSlide"
        class="table-responsive"
      >
        <input type="hidden" name="slideid" value="1">
    ';
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){      
        $output .= '
    	    <table id="sortabler" class="table sortab table-sm table-striped ">
            <thead>'.$this->show_table_header_rows().'</thead>
            <tbody>';
        while($item = $q->fetch()){
          $output .= $this->show_table_rows($item);
        }
        $output .= '
            </tbody>
          </table>';
      }
    }
    
    $output .= $groupOperationsCont;
    $output .= '
    <br>';
  	$output .=  $this->get_add_btn_show_table();
    $output .= '
    </form>';

    
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
  function get_add_btn_show_table(){
    $output = '';
    $output .= '
    <center><a class="btn btn-success " href="?adds" id="submit">Добавить</a></center>';
    
    return $output;
  }
  
  function show_form($item = null, $output = '', $id = null){
    
    $output .= '<div class = "c_form_box">';
    $is_open_panel_div = false;
    
    //Генерация Url
    if($this->url_item && $id && $item['title']){
      
      $url = $this->url_item->getUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      
      if($url){
        $tmp = '<a class="btn btn-sm btn-info float-right" href="/'.$url.'" target = "_blank" >Посмотреть на сайте</a>';
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
      if( in_array($key, array("title", "link", "seo_h1" ,"seo_title", "seo_keywords", "img_alt", "img_title" ))) $type = 'text';
      
      // Отступы SEO
      if($key == 'seo_h1'){
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('SEO');
        $is_open_panel_div = true;   
      }
      
      if($key == 'img_alt') {
        if($is_open_panel_div) $output .= $this->getCardPanelFooter();
        $output .= $this->getCardPanelHeader('Атрибуты основого изображения');
        $is_open_panel_div = true;         
      }
      
      if( in_array( $key, $this->checkbox_array) ){
        $output .= $this->show_iCheck('col_'.$key, $item, $key, $val);
        continue;  
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
            '<input '.$class_input.' type="'.$type.'" name="'.$key.'"  value="">'
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
      $output .= '
        </div>
      </div>
      ';
    }
    
    $output .= $this->getFormPicture($id, $item);
      
    $output .= '</div>';
    
    return $output;
    
  }
  
  function create_slide(){
    $output = "
    <style>
    .validate_rerror{color: red;font-size: 12px;}
    </style>        
    ";
    $id = '';
    if($this->validationValue($id)){
      
      $sql_names = ''; $sql_vals = ''; 
      
      $this->getCreateSlide_SqlNames_SqlVals($sql_names, $sql_vals);
      
      if(
        $this->pdo->query(
          $s = "
            INSERT INTO `".$this->prefix.$this->carusel_name."` ($sql_names) 
            VALUES                                              ($sql_vals)
          "
        )
      ){
        $id = $this->pdo->lastInsertId();
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Создание", "create", $this->prefix.$this->carusel_name, $id, ''/*, addslashes($s)*/);
      }else{
        echo "s = $s";
        $carusel_error = "Произошла ошибка при СОЗДАНИИ записи в бд функсия create_slide()";
        echo $carusel_error;
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord($carusel_error, "error", $this->prefix.$this->carusel_name, 0, '', addslashes($s) );
          
        exit;
      }
      
      
      if ($name = $this->load_picture($id)){
  		  $this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Загрузка изображения", "load_picture", $this->prefix.$this->carusel_name, $id, $name );
  	  }
      

      $_GET["edits"] = $id;
      $output .= $this->edit_slide($id);
      
    }else{
      $output .= $this->add_slide($_POST);
    }
    
    

  	/*if (!$_POST["save_view"]){
      #header("Location: ?edits=$id");
      $_GET["edits"] = $id;
      $output .= $this->edit_slide($id);
    }else header("Location: /");
    */
    
    return $output;
  }
  
  function edit_slide($id, &$item = null){
    $output = '';
    
    $output .= parent::edit_slide($id, $item);
    
    if($item){
    
      //Модуль картинок
      if($this->images_items && $id ){
        $output .= $this->images_items->showImageForm($this->prefix.$this->carusel_name, $id);
      }
      
      //Модуль файлов
      if($this->files_items && $id ){
        $output .= $this->files_items->showFilesForm($this->prefix.$this->carusel_name, $id);
      }
    }
    
    return $output;
  }

  function update_slide($id){
    $output = "";
    
    // Если форма прошла валидацию
    if($this->validationValue($id)){

      if ($name = $this->load_picture($id)){
  			$this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET `img` = '$name' WHERE `id` = '$id'");
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord("Загрузка изображения", "load_picture", $this->prefix.$this->carusel_name, $id, $name );
  		}

      $sql_vals = $this->getUpdateSlide_SqlVals();
      
      if($this->log)  // Ведение лога
        $backUpItem = serialize ( db::row("*", $this->prefix.$this->carusel_name, "id = ".$id) );
       
      if($this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET $sql_vals WHERE `id` = '$id'")){
        
        if($this->log){ // Ведение лога
          $newBackUpItem = serialize ( db::row("*", $this->prefix.$this->carusel_name, "id = ".$id) );
          if($backUpItem != $newBackUpItem){
            $res_log = $this->log->addLogRecord("Редактирование", "update", $this->prefix.$this->carusel_name, $id, $backUpItem/*, addslashes($s)*/);
          }else{
            $res_log = $this->log->addLogRecord("Просмотр/изменения", "view", $this->prefix.$this->carusel_name, $id/*, $backUpItem/*, addslashes($s)*/);
          }
        }
        
      }else{
        $carusel_error = "Произошла ошибка при ОБНОВЛЕНИИ записи в бд функсия update_slide()";
        echo $carusel_error;
        
        if($this->log) // Ведение лога
          $res_log = $this->log->addLogRecord($carusel_error, "error", $this->prefix.$this->carusel_name, $id, $backUpItem, addslashes($s) );
        
        exit;
      }
		
      $output .= $this->edit_slide($id);
      
    }else{
      $output .= $this->edit_slide($id, $_POST);
    }
    
    return $output;
  }

  function delete_slide($id, $view = 'show_table'){
    $output = "";
    if (isset($id) && $id){
      $this->delete_picture($id);
      
      // Удаление Url, если подключен
      if($this->url_item && $id){
        $this->url_item->deleteUrlForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      // Удаление Дополнительных картинок, если подключены
      if($this->images_items && $id){
        $this->images_items->deleteImageForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      // Удаление Дополнитеьных файлов, если подключен
      if($this->files_items && $id){
        $this->files_items->deleteImageForModuleAndModuleId($this->prefix.$this->carusel_name, $id);
      }
      
      $s = "DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'";
      if($this->log){ // Ведение лога
        $backUpItem = serialize ( db::row("*", $this->prefix.$this->carusel_name, "id = ".$id) );
        $res_log = $this->log->addLogRecord("Удаление", "delete", $this->prefix.$this->carusel_name, $id, $backUpItem, addslashes($s));
      }
      $this->pdo->query($s);
      
    }
    switch($view){
      case 'show_table':
        header('Location: '.IA_URL.$this->carusel_name.'.php');
        #$output .= $this->show_table();
        break;
        
      case 'ajax':
        $output .= "ok";
        break;
    }
    
    return $output;
  }
  
  // END BACKEND function 

}



?>