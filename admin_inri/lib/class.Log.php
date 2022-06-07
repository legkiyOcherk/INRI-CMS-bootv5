<?php
require_once __DIR__."/class.BaseCarusel.php"; 
require_once __DIR__."/formvalidator.php";// Валидатор

class Log extends BaseCarusel{
  // Настройка модуля (меняется при установке модуля)
  var $prefix = DB_PFX;
  // End Настройка модуля
  
  var $header = 'ЧПУ (Человеко Понятный URL)';
  var $pdo;
  var $date_arr = array(
    
    'title'     => 'Действие',
    'type'      => 'Тип',
    'user_id'   => 'Администратор',
    'ip'        => 'IP',
    'int_ip'    => 'Int IP',
    'date'      => 'Дата',
    'dump_data' => 'Дамп записи',
    'query'     => 'Запрос',
    'module'    => 'Название модуля',
    'module_id' => 'Id модуля',
    #'hide'      => 'Скрыть'
  );
  
  var $carusel_name;
  var $sqlTable;
  var $validateError_arr = array();
  
  var $is_pager = true; // Отображать пэйджер
  var $is_filter = null; // Отображать фильтр
  var $pager = array(
        'perPage' => 10,
        'page' => 1,
        'url' => '',
        'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
      );
  
  var $accounts = array();
  
  
  // конструктор
  function __construct ($carusel_name = null, $genSqlTable = false, $pager = null ) {
    
     
    //Для пересоздания раскоментить
    /*$genSqlTable = true;
    $_SESSION[$carusel_name]['is_table'] = 0;*/
    //END Для пересоздания раскоментить
    $this->pdo = db_open();
    $this->carusel_name = "log_01";
    
    if($carusel_name){
      $this->carusel_name = $carusel_name;
    }else{
      $this->carusel_name = "log_01";
    }
    
    $this->sqlTable = $this->prefix.$this->carusel_name;  
    
    $s = "
      SELECT `".DB_PFX."accounts`.*
      FROM `".DB_PFX."accounts`  
      WHERE 1
    ";#pri($s);
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        while($r = $q->fetch()){
          $this->accounts[$r['id']] = $r;
        }
      }
    }
    
    
    
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
    
    //Если нужен пэйджер
    if($pager){
      if(is_array($pager)) $this->pager = $pager;
      $this->setPagerParamers();
    }
    
  }
  
  // Инициалицация
  function create_sql_table(){
    
    $arr_varchar_255_not_null = array(         // varchar(255) NOT NULL
      'article' );
    
    $arr_varchar_10_default_null = array(      // varchar(10) DEFAULT NULL
      /*'date',*/ 'datetime' );
    
    $arr_text = array(                         // text
      'longtxt1', 'longtxt2', 'longtxt3', 'orm_search' );
    
    $arr_tinyint_1_default_null = array(       // tinyint(1) DEFAULT NULL      
      'fl1', 'fl2', 'fl3'  );
    
    $arr_int_11_default_null = array(          // int(11) DEFAULT NULL
      'price', 'userPhone');
    
    $arr_ignore = array(                       // ignore field
      'title', 'type', 'user_id', 'ip', 'int_ip', 'date', 'dump_data', 'query', 'ignore', 'module', 'module_id', 'file');
      
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->sqlTable.'` (
      `id`        int(11) NOT NULL AUTO_INCREMENT,
      `title`     varchar(255) NOT NULL,
      `type`      varchar(255) NOT NULL,
      `user_id`   varchar(255) NOT NULL,
      `ip`        varchar(15) DEFAULT NULL,
      `int_ip`    int(10) DEFAULT NULL,
      `date`      timestamp NOT NULL DEFAULT "0000-00-00 00:00:00" ON UPDATE CURRENT_TIMESTAMP,
      `dump_data` text,
      `query`     text,
      `module`    varchar(255) NOT NULL,
      `module_id` int(11) NOT NULL,
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
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
      		  <th style="width: 55px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
            <th style="width: 150px;">Дата</th>
      		  <th style="width: 250px;">Действие</th>
            <th >Материал</th>
            <th >Пользователь</th>
            <th >IP</th>
            <th >Название модуля</th>
            <th >Id модуля</th>
      		  <th style="width: 80px">Действие</th>
          </tr>';
    
    return $output;
  }
  
  function show_table_rows($item){
    
    extract($item);
    
    $output .= '
          <tr class="r1" id="tr_'.$id.'" style="cursor: move;">			 
            <td>
              <input type="checkbox" class="group_checkbox" name="group_item[]" value="'.$id.'"> '.$id.'
              <input type="hidden" value="'.$id.'" name="itSort[]">
          </td>
            
            <td class="img-act"><div title="Скрыть" onclick="star_check('.$id.', \'hide\')" class="star_check '.$this->getStarValStyle($hide).'" id="hide_'.$id.'"></div></td>  
            <td>'.$date.'</td>
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>
            <td>';
    
    if($type && ( $type != 'delete') && $module && $module_id ){
          
      if(substr_count($module, "_cat") && substr_count($module, $this->prefix) ){
        $ed = "editc"; 
        $md = str_replace("_cat", '', $module);
        $md = str_replace($this->prefix, '', $md);   
      }else{
        $ed = "edits";
        $md = str_replace($this->prefix, '', $module);  
      }
      
      $tt = db::value("title", $module, "id = ".$module_id);
      if($tt === false){
        $output .= 'Не существует';
      }else{
        
        $output .= '<a href="'.IA_URL.$md.'.php?'.$ed.'='.$module_id.'" title="Посмотреть в админке">'.$tt.'</a>';  
      }
      
    }elseif($type == 'delete'){
      $output .= 'Не существует';
    }else{
      $output .= 'Не существует';
    }
    
    $user_login = $this->accounts[$user_id]['login'];
    $user_name = $this->accounts[$user_id]['fullname'];
    #pri($this->accounts);die();
    
    $output .= '
            </td>
            <td><b>'.$user_login.'</b> '.$user_name.' id('.$user_id.')</td>
            <td>'.$ip.'</td>
            <td>'.$module.'</td>
            <td>'.$module_id.'</td>
  	';
        
    $output .= '
        	  <td style="" class="img-act">
              <a  href="..'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" 
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
    $s_order = " ORDER BY `id` DESC ";
    
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
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        $output .= '
    	    <table id="sortabler" class="table sortab table-sm table-striped ">
            <thead>'.$this->show_table_header_rows().'</thead>
            <tbody>';
        while($item = $q->fetch()){
          try {
            $output .= $this->show_table_rows($item);
          } catch (Exception $e) {
            echo $e->getMessage();
          }
        }
        $output .= '
            </tbody>
          </table>';
      }
    }
    $output .= $groupOperationsCont;
    $output .= '
    <br>
  	<center><a class="btn btn-success" href="?adds" id="submit">Добавить</a></center>
    </form>';

    
    if($this->is_pager) $output .= $strPager;
    
    return $output;
    
  }
  
  function show_form_row($title = '', $cont = ''){
    $output = '
      <div class="form-group row">
        <label class="col-12 col-sm-4 col-md-3 col-lg-2 c_title control-label">'.$title.'</label>
        <div class = "col-12 col-sm-8 col-md-9 col-lg-10 c_cont">'.$cont.'</div>
      </div>
    ';
    
    return $output;
  }
  
  function show_form($item = null, $output = ''){
    
    $dump_data = '';
    
    $output .= '<div class = "c_form_box">';
    
    foreach($this->date_arr as $key=>$val){
      $type = '';
      $class_input = '  class="form-control" '; $is_color = false;
      if( in_array($key, array("tongtxt1", "tongtxt1", "tongtxt1"))) $class_input = ' class="ckeditor" '; 
      if( in_array($key, array( "dump_data" ))){
        $class_input = ' class="form-control" style = "min-height: 200px;" '; 
        if($item['dump_data']){
          $dump_data = unserialize($item[$key]); #pri(unserialize($item[$key]));
        }
      }
      if( in_array($key, array( "query" ))) $class_input = ' class="form-control" style = "min-height: 150px;" '; 
      if( in_array($key, array("title", "type", "user_id", "ip", "int_ip", "date", "module", "module_id"))) $type = 'text';
      
      if( in_array( $key, $this->checkbox_array) ){
        $output .= $this->show_iCheck('col_'.$key, $item, $key, $val);
        continue;  
      }
      // Если есть занчение
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
    if($dump_data){
        $output .= '
        <p><b>Массив дампа записи</b><p>
        <pre>'.print_r($dump_data, TRUE).'</pre>';  
    }
    $output .= '</div>';
    
    return $output;
    
  }

  function create_slide(){
    $output = "";
    
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
      
      }else{
        echo "Произошла ошибка при СОЗДАНИИ записи в бд функсия create_slide() s = `$s`";
        exit;
      }
    
		  #$id = mysql_insert_id();
      $id = $this->pdo->lastInsertId();

      $_GET["edits"] = $id;
      $output .= $this->edit_slide($id);
      
    }else{
      
      $output .= $this->add_slide($_POST);
      
    }
    


    
    return $output;
  }
  
  function update_slide($id){
    $output = "";
    
		#$id = intval($_GET["updates"]);
    
		
    // Если форма прошла валидацию
    if($this->validationValue($id)){
      
      $sql_vals = $this->getUpdateSlide_SqlVals();
       
      if($this->pdo->query("UPDATE `".$this->prefix.$this->carusel_name."` SET $sql_vals WHERE `id` = '$id'")){
        
      }else{
        echo "Произошла ошибка при ОБНОВЛЕНИИ записи в бд функсия update_slide()";
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
   	  #if (isset($_GET["deletes"])){
  		#$id = intval($_GET["deletes"]);
      #$this->delete_picture($id);
      
      $s = "DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'";
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
  
  // Log function
  
  function addLogRecord($title, $type, $module, $module_id = 0, $dump_data = '', $query = '' ){
    
    /*switch($type){
      
      case 'create': $title = 'Создание'; break;
      case 'update': $title = 'Редактирование'; break;
      case 'delete': $title = 'Удаление'; break;
        
      default: $title = 'Лог';
    }*/
    
    if (isset($_SESSION["WA_USER"])){
      $user_id = $_SESSION["WA_USER"]["id"];
    }
    $ip = $_SERVER['REMOTE_ADDR'];
    $int_ip = ip2long($ip);
    
    $date = date("Y-m-d H:i:s");
    
    $insert_data = array(
      'title'     => $title,
      'type'      => $type,
      'user_id'   => $user_id,
      'ip'        => $ip,
      'int_ip'    => $int_ip,
      'date'      => $date,
      'dump_data' => addslashes($dump_data),
      'query'     => $query,
      'module'    => $module,
      'module_id' => $module_id
    );
    
    $res = db::insert($this->prefix.$this->carusel_name, $insert_data, 0);
    
    return $res;
    
  }
  
  function getLogRacord(){
    
  }
  
  // END Log function
  
  // END BACKEND function

  
}



?>