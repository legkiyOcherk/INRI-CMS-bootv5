<?php
require_once __DIR__."/class.BaseCarusel.php"; 
require_once __DIR__."/formvalidator.php";// Валидатор

class MyValidator extends CustomValidator
{
  var $id_row;
  var $table;
  function __construct($id = 0, $table = DB_PFX.'url'){
    $this->id_row = $id;
    $this->table = $table;
  }
  
  
	function DoValidate(&$formars,&$error_hash)
	{
    $newUrl = $formars['url'];
    
    $s = "
    SELECT *
    FROM `".$this->table."`
    WHERE `url` = '$newUrl'
    ";
    if( $this->id_row){
      $s .= "
      AND id <> $this->id_row
      ";
    }
    
    #echo "s = $s";
    $this->pdo = db_open();
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        $error_hash['url']= "URLs = `".$newUrl."` не уникален введите другой";
        return false;
      }
    }else{
      echo "Ошибка при обращении к таублице `".$this->table."` при проверке url = `".$newUrl."` на уникальность class MyValidator " ;
      exit;
    }
    
		return true;
	}
}

class Url extends BaseCarusel{
  
  var $header = 'ЧПУ (Человеко Понятный URL)';
  
  var $date_arr = array(
    'title'     => 'Title странички ',
    'url'       => 'Чпу',
    'module'    => 'Название модуля (таблицы бд к которой привязан URL)',
    'module_id' => 'Название модуля id модуля',
    'hide'      => 'Скрыть'
  );
  var $pdo;
  
  var $carusel_name;
  var $sqlTable;
  var $validateError_arr = array();
  var $yandex_key = 'trnsl.1.1.20160208T075713Z.08bdf3a29fd3445a.82c1febf60fe1a3e8de0c7e220354e82c7b0592a';
  
  var $is_pager = true; // Отображать пэйджер
  var $is_filter = null; // Отображать фильтр
  var $pager = array(
        'perPage' => 10,
        'page' => 1,
        'url' => '',
        'items_per_page' => array( 10, 50, 100, 500, 1000, 5000)
      );
  
  
  
  // конструктор
  function __construct ($carusel_name = null, $genSqlTable = false, $pager = null ) {
    
     
    //Для пересоздания раскоментить
    /*$genSqlTable = true;
    $_SESSION[$carusel_name]['is_table'] = 0;*/
    //END Для пересоздания раскоментить
    $this->pdo = db_open();
    $this->carusel_name = "url_01";
    
    if($carusel_name){
      $this->carusel_name = $carusel_name;
    }else{
      $this->carusel_name = "url_01";
    }
    
    $this->sqlTable = $this->prefix.$this->carusel_name;  
  
        
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
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$this->sqlTable.'` (
      `title` varchar(255) NOT NULL,
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `url` varchar(255) NOT NULL,
      `module` varchar(255) NOT NULL,
      `module_id` int(11) NOT NULL,
    ';
    /*
      `txt1` varchar(255) DEFAULT NULL,
      `txt2` varchar(255) DEFAULT NULL,
      `txt3` varchar(255) DEFAULT NULL,
      `tongtxt1` text,
      `longtxt2` text,
      `longtxt3` text,
      `fl1` tinyint(1) DEFAULT NULL,
      `fl2` tinyint(1) DEFAULT NULL,
      `fl3` tinyint(1) DEFAULT NULL,
      */
    $sql .= '
      `hide` tinyint(1) NOT NULL DEFAULT "0",
      `ord` int(11) NOT NULL DEFAULT "0",
      PRIMARY KEY (`id`),
      UNIQUE (`url`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ';
    $q = $this->pdo->query($sql);
    if($q){
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
  
  // All Method
  
  function setValidateError_arr($array) {
    $this->validateError_arr = $array;
  }
   
  // END All Method
  
  
  // AJAX function

  // END AJAX function
  
  
  // BACKEND function
  
  function show_table_header_rows(){
    $output = '
          <tr class="th nodrop nodrag">
      		  <th style="width: 55px;">#</th>
      		  <th style="width: 50px;">Скрыть</th>
      		  <th >Название</th>
            <th >ЧПУ</th>
            <th >Модуль</th>
            <th >Модуль ID</th>
            <th>Ссылка</th>
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
            <td style="text-align: left;">
              <a href="'.IA_URL.$this->carusel_name.'.php?edits='.$id.'" title="редактировать">'.$title.'</a>
            </td>
            <td style="text-align: left;">
              <a href="/'.$url.'" title="На сайте">'.$url.'</a>
            </td>
            <td style="text-align: left;">'.$module.'</td>
            <td style="text-align: left;">'.$module_id.'</td>
            <td style="text-align: left;">';
    if( $source_link = $this->getAdminSourceLink($module, $module_id) )
      $output .= '
              <a href="'.$source_link.'" title="Источник">Материал</a>';  
    $output .= '
            </td>
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
    $s_order = " ORDER BY `ord` ASC ";
    
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
          $output .= $this->show_table_rows($item);
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
  
  function show_form($item = null, $output = ''){
    
    /*echo "<pre>";
    print_r($this->validateError_arr);
    print_r($_POST);
    echo "</pre>";*/
    
    $output .= '<div class = "c_form_box">';
    
    foreach($this->date_arr as $key=>$val){
      $type = '';
      $class_input = '  class="form-control" '; $is_color = false;
      if( in_array($key, array("tongtxt1", "tongtxt1", "tongtxt1"))) $class_input = ' class="ckeditor" ';
      if( in_array($key, array("title", "url", "module", "module_id"))) $type = 'text';
      
      if( in_array( $key, $this->checkbox_array) ){
        $output .= $this->show_iCheck('col_'.$key, $item, $key, $val);
        continue;  
      }
      # Если есть занчение
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
      /*
      if($item){
        
        #$output .= ' '.$val.' :<BR/>';
        #$output .= $this->getErrorForKey($key);
        #$output .= '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea><BR/><BR/>';
        
        $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50>'.htmlspecialchars($item[$key]).'</textarea>'
          );
      }else{
        
          #$output .= ' '.$val.' :<BR/><TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50></textarea><BR/><BR/>';
          
        $output .= $this->show_form_row( 
            $val.$this->getErrorForKey($key), 
            '<TEXTAREA '.$class_input.' name="'.$key.'" rows=2 cols=50></textarea>'
          );
      }*/
      
    }
    
    $output .= '</div>';
    
    return $output;
    
  }
  
  function getCreateSlide_SqlNames_SqlVals(&$sql_names, &$sql_vals){
    $i=0;
      
    foreach($this->date_arr as $key=>$val){
      // Генерация Url
      if($key == "url"){
        if(!$_POST['url']){
          $_POST['url'] = $this->gen_unique_url($_POST['title'], $id);
        }
      }
        
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_names .= $prefix.' `'.$key.'`';
      $sql_vals .= $prefix.' \''.addslashes($_POST[$key]).'\'';
      $i++;
    };
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
  
  // Валидация введенных данных
  function getErrorForKey($key){
    $output = '';
    if(!empty($this->validateError_arr)){
      if(isset($this->validateError_arr[$key]))
        if($this->validateError_arr[$key]){
          $output .= '<label class="validate_rerror">'.$this->validateError_arr[$key].'</label>';
        }
    }
    return $output;
  }
  
  function validationValue($id){
    $is_validation = true;
    
    $validator = new FormValidator();
    $validator->addValidation("title","req","Пожалуйста, заполните ".$this->date_arr['title']);
    //$validator->addValidation("url", "req", "Пожалуйста, заполните ".$this->date_arr['url']);
    $validator->addValidation("url", "regexp=/^[A-Za-z0-9\-\_]{1,100}$/im", "Поле: ".$this->date_arr['url']." может содержать только Цифры 0-9 Буквы a-z и символы `-`, `_`. Дина не более 100 символов");
    $validator->addValidation("module", "req", "Пожалуйста, заполните ".$this->date_arr['module']);
        
    $validator->addValidation("module_id", "req", "Пожалуйста, заполните ".$this->date_arr['module_id']);
    $validator->addValidation("module_id", "num", "Поле: ".$this->date_arr['module_id']." должно быть целочисленным");
    $custom_validator = new MyValidator($id, $this->prefix.$this->carusel_name);
    $validator->AddCustomValidator($custom_validator);

    if($validator->ValidateForm())
    {
        #echo "<h2>Validation Success!</h2>";
    }
    else
    {
        #echo "<B>Validation Errors:</B>";

        $error_hash = $validator->GetErrors();
        $this->validateError_arr = $error_hash;
        $is_validation = false;
        /*foreach($error_hash as $inpname => $inp_err)
        {
            echo "<p>$inpname : $inp_err</p>\n";
        }*/       
    }
    
    return $is_validation;
  }

  function validationValueUrl(){
    $is_validation = true;
    
    $validator = new FormValidator();
    $validator->addValidation("url", "regexp=/^[A-Za-z0-9\-\_]{1,100}$/im", "Поле: ".$this->date_arr['url']." может содержать только Цифры 0-9 Буквы a-z и символы `-`, `_`. Дина не более 100 символов");
    
    if($validator->ValidateForm())
    {
        /*echo "<h2>Validation Success!</h2>";*/
    }
    else
    {
        #echo "<B>Validation Errors:</B>";

        $error_hash = $validator->GetErrors();
        $this->validateError_arr = $error_hash;
        $is_validation = false;
        /*foreach($error_hash as $inpname => $inp_err)
        {
            echo "<p>$inpname : $inp_err</p>\n";
        }*/       
    }
    
    return $is_validation;
  }
  // End Валидация введенных данных
  
  function getUpdateSlide_SqlVals(){
    $sql_vals = ''; $i=0;
    
    foreach($this->date_arr as $key=>$val){
      // Генерация Url
      if($key == "url"){
        if(!$_POST['url']){
          $_POST['url'] = $this->gen_unique_url($_POST['title'], $id);
        }
      }
      
      ($i) ? $prefix = ', ' : $prefix = '';
      if( in_array( $key, $this->checkbox_array ) ){
        ( isset($_POST[$key]) && $_POST[$key] ) ? $_POST[$key] = 1 : $_POST[$key] = 0;
      }
      $sql_vals .= $prefix.'  `'.$key.'` = \''.addslashes($_POST[$key]).'\'';
      $i++;
    }
    return $sql_vals;
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
  
  function deleteUrlForModuleAndModuleId($module, $module_id){
    $s = "
    SELECT * 
    FROM `".$this->prefix.$this->carusel_name."`
    WHERE `module` = '$module'
    AND `module_id` = '$module_id' 
    ";
    #echo "s = $s";
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        while($r = $q->fetch()){
          $id = $r['id'];
          #$this->delete_picture($id);
  		    $this->pdo->query("DELETE FROM `".$this->prefix.$this->carusel_name."` WHERE `id` = '$id'");
        }
      }
    }
  }
  
  function getUrlForModuleAndModuleId($module, $module_id){
    #echo " <br> <br> module = $module<br> module_id = $module_id<br> ";
    $s = "
    SELECT * 
    FROM `".$this->prefix.$this->carusel_name."`
    WHERE `module` = '$module'
    AND `module_id` = '$module_id' 
    LIMIT 1
    ";
    
    #echo "s = $s";
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        $r = $q->fetch();
        return $r['url'];
      }
    }
    
    return null;
  }
  
  function show_form_field(&$url, $module, $module_id, $title){
    $output = '';
    #echo "url = $url<br> module = $module<br> module_id = $module_id<br> title = $title<br>";
    if($this->validationValueUrl()){
      $url = $this->set_url($url, $module, $module_id, $title);
    }else{
      $output = "    
      <style>
      img {max-width: 992px;}
      .validate_rerror{
        color: red;
        font-size: 12px;
      }

      </style>
      ";
      $output .= $this->getErrorForKey('url');
    }
    
    #echo "url2 = $url";
    
    $output .= ' <input type = "text" class="form-control" name="url" value = '.$url.' />';
    //$output .= ' <TEXTAREA class="span12" name="url" rows=2 cols=50>'.$url.'</textarea> ';
    
    return $output;
  }
  
  function set_url(&$url, $module, $module_id, $title ){
    if(!$title) return 'class Url function set_url парааметр title пустой ';
    if(!$module) return 'class Url function set_url парааметр module пустой ';
    if(!$module_id) return 'class Url function set_url парааметр module_id пустой ';
    //Существует ли запись с таким module и module_id
    $s = "
    SELECT * 
    FROM `".$this->prefix.$this->carusel_name."`
    WHERE `module` = '$module'
    AND `module_id` = '$module_id'
    LIMIT 1
    ";
    #echo "s = $s<br>";
    $q = $this->pdo->query($s);
    
    if($q->rowCount()){
      $r = $q->fetch();
      $id = $r['id'];
      if($url == $r['url']) return $url;
      if(!$url){
        $url = $this->gen_unique_url($title, $r['id']);
      }else{
        if ($this->is_url($url)) {
          $i = 0;
          do {
            if (!$id) {
              $id = rand(1, 100000);
            }
            $url = $url . '-' . $id;
            $i++;  
            
          }
          while (($this->is_url($url)) && ($i<10));
        }
      }
      
      $s_url = "UPDATE `".$this->prefix.$this->carusel_name."` SET  `url` =  '$url', `title` =  '$title'  WHERE `module` = '$module' AND `module_id` = '$module_id'";
      
      if($res = $this->pdo->query($s_url)){
        return $url;
      }else{
        return $res;
      }
      
    }else{
      
      if(!$url){
        $url = $this->gen_unique_url($title);
      }else{
        
        if ($this->is_url($url)) {
          $i = 0;
          do {
            $id = rand(1, 100000);
            
            $url = $url . '-' . $id;
            $i++;  
            
          }
          while (($this->is_url($url)) && ($i<10));
        }
      }
      $s_url = "
      INSERT INTO  `".$this->prefix.$this->carusel_name."` 
        ( `id`, `url`,`module`, `module_id`, `title` )
      VALUES 
        ( NULL ,  '$url',  '$module',  '$module_id',  '$title' )
      ";
      
      if($res = $this->pdo->query($s_url)){
        return $url;
      }else{
        return $res;
      } 
    }
    
  }
  
  // Генерация уникального Url
    
  function gen_unique_url($title, $id = null, $eng_trans = 0){
    if($eng_trans){
      $slug_url = $this->title2url($title);
      if (!$slug_url) $slug_url = $this->transliterate($title);
    }else{
      $slug_url = $this->transliterate($title);
      if (!$slug_url) $slug_url = $this->title2url($title);
    }
    
    if ($this->is_url($slug_url)) {
      $slug_url2 = $slug_url;
    
      $i = 0;
      do {
        if (!$id) {
          $id = rand(1, 100000);
        }
        $slug_url2 = $slug_url2 . '-' . $id;
        $i++;  
        #echo "$this->is_url ".$slug_url2." = ".($this->is_url($slug_url2))."<br>";
      }
      while (($this->is_url($slug_url2)) && ($i<10));
      #echo "i = $i<br>";
      if($i == 10){
        die("class.Url функция gen_unique_url - произошла ошибка при генерации уникального адреса");
      }
      $slug_url = $slug_url2;
    }
    
    return $slug_url;
  } 
  
  function is_url($url){
    
    $s = "
    SELECT * 
    FROM `".$this->prefix.$this->carusel_name."`
    WHERE `url` = '$url'
    ";
    
    #echo "s = $s<br>";
    $q = $this->pdo->query($s);
    
    if($q->rowCount()){
      //echo "Есть Урл";
      return 1; 
    }else{
      //echo "Нет Урл";
      return 0;
    }
    
  }
  
  function title2url($title2url){
    
    $title2url = str_replace("'", "", $title2url);
    $title2url = str_replace("\"", "", $title2url);
    $title2url = str_replace(" ", "+", $title2url);
    $title2url = str_replace("«", "", $title2url);
    $title2url = str_replace("»", "", $title2url);

    $translate_url="https://translate.yandex.net/api/v1.5/tr.json/translate?key=".$this->yandex_key."&text=$title2url&lang=ru-en&format=plain"; 
     //$result=$this->goUrl($translate_url);
    $json = file_get_contents($translate_url,0,null,null);
    $json_output = json_decode($json);
    /*if (!$result)
        return false;*/
    //echo "resul = ".$result."<br>";
    //$result = preg_replace("#\[\[\[\"(.*?)\".*#ism", "\$1", $result);
    //$answer_arr =  json_decode ('{"code":200,"lang":"ru-en","text":["ring"]}');
    
    if(isset($json_output->text[0])){
      
      if($json_output->text[0]){
        $result = $json_output->text[0];
        $result = strtolower($result);
        $result = trim(preg_replace('#[^a-z0-9]+#u', ' ', $result));
        $t2u = explode(" ", $result);
        $t2u_no = 0;
        foreach ($t2u as $word) {
            if ($t2u_no != 0) {
                if (preg_match('#[a-z]#u', $word))
                    $result.="_" . $word;
                else
                    $result.="-" . $word;
            }
            else {
                $result = $word;
            }
            if ($t2u_no == 3)
                break;
            if (strlen($word) > 2)
                $t2u_no++;
        }
        //echo "Перевод = ".$result."<br>";
        return $result;
      }else{
        return null;
      }
    }else{
      return null;
    }
    return null;
  }
  
  function goUrl($url,$post=array(),$cookie='',$header=0,$follow=0){
    $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.1) Gecko/2008070208';   
    $ch = curl_init();
     if($cookie) curl_setopt($ch, CURLOPT_COOKIE, $cookie); 
     curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
     //curl_setopt($ch, CURLOPT_PROXY, "112.25.12.37:80");
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if($header)    curl_setopt($ch, CURLOPT_HEADER, true);
    if($follow)    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);     
     if(!empty($post)){
         curl_setopt($ch, CURLOPT_POST, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
     }
     curl_setopt($ch, CURLOPT_URL, $url);
    $answer=curl_exec($ch);
    curl_close($ch);
    echo "<pre>";
    echo $answer;
    echo "</pre>";
    return $answer;
  }
  
  function transliterate($st) {
    
    $st = preg_replace('/[^a-zа-яё0-9\s\.\+\/\!\&]+/iu', '', $st);
    
    $replace=array(
  		"'"=>"",
  		"`"=>"",
  		"а"=>"a","А"=>"a",
  		"б"=>"b","Б"=>"b",
  		"в"=>"v","В"=>"v",
  		"г"=>"g","Г"=>"g",
  		"д"=>"d","Д"=>"d",
  		"е"=>"e","Е"=>"e",
      'ё'=>"yo", 'Ё'=>"yo",
  		"ж"=>"zh","Ж"=>"zh",
  		"з"=>"z","З"=>"z",
  		"и"=>"i","И"=>"i",
  		"й"=>"y","Й"=>"y",
  		"к"=>"k","К"=>"k",
  		"л"=>"l","Л"=>"l",
  		"м"=>"m","М"=>"m",
  		"н"=>"n","Н"=>"n",
  		"о"=>"o","О"=>"o",
  		"п"=>"p","П"=>"p",
  		"р"=>"r","Р"=>"r",
  		"с"=>"s","С"=>"s",
  		"т"=>"t","Т"=>"t",
  		"у"=>"u","У"=>"u",
  		"ф"=>"f","Ф"=>"f",
  		"х"=>"h","Х"=>"h",
  		"ц"=>"c","Ц"=>"c",
  		"ч"=>"ch","Ч"=>"ch",
  		"ш"=>"sh","Ш"=>"sh",
  		"щ"=>"sch","Щ"=>"sch",
  		"ъ"=>"","Ъ"=>"",
  		"ы"=>"y","Ы"=>"y",
  		"ь"=>"","Ь"=>"",
  		"э"=>"e","Э"=>"e",
  		"ю"=>"yu","Ю"=>"yu",
  		"я"=>"ya","Я"=>"ya",
  		"і"=>"i","І"=>"i",
    	/*' '=>'_',	
      '+'=>'_', 
      '/'=>'_', 
      '!'=>'_', 
      '&'=>'_',*/
      
      '%'=>'',
      '«'=>"",
  		'»'=>"",
      '.'=>'', 
      ','=>'', 
      '('=>'', 
      ')'=>'', 
  	);
    if($result = strtr($st, $replace)){
    
      $result = strtolower($result);
      $result = trim(preg_replace('#[^a-z0-9]+#u', ' ', $result));
      $t2u = explode(" ", $result);
      $t2u_no = 0;
      foreach ($t2u as $word) {
        if ($t2u_no != 0) {
            if (preg_match('#[a-z]#u', $word))
                $result.="-" . $word;
            else
                $result.="-" . $word;
        }
        else {
            $result = $word;
        }
        if ($t2u_no == 7)
            break;
        if (strlen($word) > 1)
            $t2u_no++;
      }
      //echo "Перевод = ".$result."<br>";
      return $result;
    }else{
      //return $str=iconv("UTF-8","UTF-8//IGNORE",strtr($st, $replace));
      return null;    
    }
  	
  }
  
  // END Генерация уникального Url
  
  
  // END BACKEND function

   
 	// FRONTEND function
   
  function route($url = '', &$module = null, &$module_id = null){
    $s = "
    SELECT *
    FROM `".$this->prefix.$this->carusel_name."`
    WHERE url = '$url'
    LIMIT 1
    ";
    #echo "s = $s";
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        $r = $q->fetch();
        $module = $r['module'];
        $module_id = $r['module_id'];
      }
    }
  }
  
  static function getStaticUrlForModuleAndModuleId($urlTable, $module, $module_id){
    global $PDO;
    #echo " <br> <br> module = $module<br> module_id = $module_id<br> ";
    $s = "
    SELECT * 
    FROM `$urlTable`
    WHERE `module` = '$module'
    AND `module_id` = '$module_id' 
    LIMIT 1
    "; #pri($s);
    
    if($q = $PDO->query($s)){
      if($q->rowCount()){
        $r = $q->fetch();
        return $r['url'];
      }
    }
    
    return null;
  }
  
  // END FRONTEND function
  
  function getContent(&$admin = null){
    $carisel = $this;
    if(!is_null($admin)){
      if(isset($admin->is_admin_navigation) && ($admin->is_admin_navigation) ){
        $this->admin = &$admin;
      }
    }
    $output = '';
    
    if(isset($_GET['ajx'])){
      if (isset($_SESSION["WA_USER"])){
        
        if(isset($_GET['act'])){
          if($_GET['act'] == 'star_check'){
            $carisel->star_check();
          }elseif($_GET['act'] == 'sort_item'){
            $carisel->sort_item();
          }elseif($_GET['act'] == 'ajx_pager'){
            echo $carisel->ajx_pager();
          }elseif($_GET['act'] == 'delete_item'){
            echo $carisel->delete_slide(intval($_POST['del_id']), 'ajax');
          }
        }

      }
      
    }else{
      #$output='<div style="padding:20px 0;">';
        
      if (isset($_SESSION["WA_USER"])){
        
        
        if(isset($_GET['adds'])){
          $output .= $carisel->add_slide();
        }elseif(isset($_GET['creates'])){
          $output .= $carisel->create_slide();  
        }elseif(isset($_GET["edits"])){
          $output .= $carisel->edit_slide(intval($_GET["edits"]));  
        }elseif(isset($_GET["updates"])){
          if( isset($_POST['title'] )){
            $output .= $carisel->update_slide(intval($_GET["updates"]));    
          }else{
            $output .= $carisel->edit_slide(intval($_GET["updates"]));  
          }
        }elseif(isset($_GET["delete_picture"])&&isset($_GET['id'])){
          $output .= $carisel->delete_picture(intval($_GET['id']));  
        }elseif(isset($_GET["deletes"])){
          $output .= $carisel->delete_slide(intval($_GET['deletes']));  
        }else{
          $output .= $carisel->show_table();
        }
     
      }else{
        $output .=  '<div style ="text-align: center;"><h3> Кончилость время ссесии <a href = "'.IA_URL.'">Повторите авторизацию</a></h3>';
        
      }
      #$output .= "</div>";
      
      if(!is_null($admin)){
        $admin->setForName('header', $this->getForName('header'));
        $admin->setForName('bread', $this->getForName('bread'));
        $admin->setForName('title', $this->getForName('title'));
        $admin->setForName('cont_footer', $this->getForName('cont_footer'));
      }
    }
    
    return $output;
  }
}



?>