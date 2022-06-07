<?php
require_once('../define.php');
require_once(WA_PATH.'config.inc.php');
require_once(WA_PATH.'lib/mysql.lib.php');
require_once(WA_PATH.'lib/class.db.php');

class Setup{
  
  var $doctype           = '<!doctype html>';
  var $cms               = CMS_NAME;
  var $db_pfx            = DB_PFX;
  var $lang              = 'ru';
  var $charset           = '<meta http-equiv="Content-Type" content="text/html" charset="utf-8">';
  var $title;
  var $description;
  var $keywords;
  var $nav_items_arr     = array(); # ['link'] => title
  var $script_name       = 'setup.php'; 
  var $pdo;
  private $content; 
  
  function __construct (){ # конструктор
    if($this->is_database_access()){
      $this->pdo = db_open();  
    }
    
    
    $this->title = 'Установка '.$this->cms.' CMS';
    $this->set_content('<div class="container"><h1>Установка '.$this->cms.' CMS</h1></div>'); 
    
    #$this->nav_items_arr['/'.ADM_DIR.'/'.$this->script_name] = 'Главная';
    #$this->nav_items_arr['/'] = '<span class = "btn btn-sm btn-outline-primary">Сайт</span>';
    #$this->nav_items_arr['/'.ADM_DIR.'/index.php'] = '<span class = "btn btn-sm btn-outline-primary">Админка</span>';
  }
 
  function get_setup_header(){
    $output = '    
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="'.SITE_NAME.'">
    <link rel="shortcut icon" type="image/x-icon" href="'.ADMIN_FAVICON.'">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    ';
    return $output;
  }
  
  function get_setup_footer(){
    $output = '
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    ';
    
    return $output;
  }
  
  function get_setup_style(){
    $output = '
    <style>
    pre {
      display: block;
      padding: 9.5px;
      margin: 0 0 10px;
      font-size: 13px;
      line-height: 1.42857143;
      color: #333;
      word-break: break-all;
      word-wrap: break-word;
      background-color: #f5f5f5;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .container{
      padding-bottom: 15px;
    }
    </style>
    ';
    
    return $output;
  }
  
  function set_content($content){
    $this->content = $content;
  }
  
  function get_content(){
    return $this->content;
  }
  
  function add_content($text){
    $this->content .= $text;
  }
  
  function get_nav_bar(){
    $output = '';
    $output .= '
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="/'.ADM_DIR.'/'.$this->script_name.'">'.CMS_NAME.'</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mr-auto">'; 
    foreach($this->nav_items_arr as $k => $v){
      $output .= '
            <li class="nav-item">
              <a class="nav-link" href="'.$k.'">'.$v.'</a>
            </li>';
    }
            
    $output .= '
          </ul>
        </div>
      </nav>
    </div>';
    
    return $output;
  }
  
  function wrap_block($text){
    $output = '';
    
    $output .= '
    <div class="container">
      '.$text.'
    </div>';
    
    return $output;
  }
  
  function show(){
    $output = '';
    $output .= $this->doctype.'
<html lang="'.$this->lang.'">
  <head>
    '.$this->charset;
    $output .= $this->get_setup_header();
    $output .= '
    <title>'.$this->title.'</title>
  </head>
  <body>';
    $output .= $this->get_nav_bar();
    $output .= $this->get_content();
    $output .= $this->get_setup_footer();
    $output .= $this->get_setup_style();
    $output .= '
  </body>
</html>';
    
    return $output;
  }
  
  function is_database_access(){ # Проверка корректности подключения
    $charset = 'utf8';
    $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $CFG = & $_SESSION["NEX_CFG"];
    $dsn = 'mysql:host='.$CFG["db_hostname"].';dbname='.$CFG["db_basename"].';charset='.$charset;
    #pri($dsn);
    try { 
      $PDO = new PDO($dsn, $CFG["db_username"], $CFG["db_password"], $opt); 
      return true;
    } 
    catch (PDOException $e) { 
     #die('Подключение не удалось: ' . $e->getMessage()); 
     return false;
    }
    
  }
  
  function test_database_access(){
    $output = '';
    if( $this->is_database_access() ){
      $output .= '
      <div class="alert alert-success" role="alert">
        Доступ к базе данных установлен.
      </div>';
    }else{
      $output .= '
      <div class="alert alert-danger" role="alert">
        Доступ к базе данных отсутствует!
      </div>';
    }
    return $output;
  }
  
  function set_database_access(&$db_arr){
    $output = '';
    
    if( isset($_POST) && $_POST ){
      $db_arr['db_hostname'] = $_POST['db_hostname'];
      $db_arr['db_username'] = $_POST['db_username'];
      $db_arr['db_password'] = $_POST['db_password'];
      $db_arr['db_basename'] = $_POST['db_basename'];
      
      $new_config_data = '#db_config
	"db_hostname" => "'.$db_arr['db_hostname'].'",
	"db_username" => "'.$db_arr['db_username'].'",
	"db_password" => "'.$db_arr['db_password'].'",
	"db_basename" => "'.$db_arr['db_basename'].'"
#end_db_config';

      $config_file = file_get_contents('config.inc.php');
      $config_file = preg_replace('|\#db_config(.*?)\#end_db_config|isU', $new_config_data, $config_file);
      $res = file_put_contents('config.inc.php', $config_file);
      $_SESSION["NEX_CFG"] =& $db_arr;
      #pri($config_file);
    }
    #pri($database_acs_arr);
    
    return $output; 
  }
  
  function setup_database_access(){
    $output = '';
    
    $db_arr = array(
      "db_hostname" => "",
    	"db_username" => "",
    	"db_password" => "",
    	"db_basename" => "" 
    );
    
    $output .= '
    <h1>Установка доступа к базе данных</h1>';
    $output .= $this->set_database_access($db_arr);
    #$output .= $this->test_database_access();
    if( $this->is_database_access() ){
      $output .= '
      <div class="alert alert-success" role="alert">
        Доступ к базе данных установлен.
      </div>';
    }else{
      $output .= '
      <div class="alert alert-danger" role="alert">
        Доступ к базе данных отсутствует!
      </div>';
    
      $output .= '
      <form method = "post">
        <div class="card">
          <div class="card-header">
            Доступ к базе данных
          </div>
          <div class="card-body">
            <h5 class="card-title">Введите данные</h5>
            
            <div class="form-group row">
              <label for="db_hostname" class="col-sm-3 col-form-label">Hostname</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_hostname" name = "db_hostname" value="'.$db_arr['db_hostname'].'">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="db_username" class="col-sm-3 col-form-label">Username</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_username" name = "db_username" value="'.$db_arr['db_username'].'">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="db_password" class="col-sm-3 col-form-label">Password</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_password" name = "db_password" value="'.$db_arr['db_password'].'">
              </div>
            </div>
            
            <div class="form-group row">
              <label for="db_basename" class="col-sm-3 col-form-label">Basename</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="db_basename" name = "db_basename" value="'.$db_arr['db_basename'].'">
              </div>
            </div>
            <div class="form-group row">
              <label for="submit" class="col-sm-3 col-form-label"></label>
              <div class="col-sm-9">
                <button class="btn btn-primary" type="submit">Применить</button>
              </div>
            </div>
            
            
            
          </div>
        </div>
      
      </form>';
    }
    
    return $output; 
  }
  
  function is_database($table_name){
    
    $CFG = & $_SESSION["NEX_CFG"];
    $database = $CFG["db_basename"];
    
    $s = "SHOW TABLES FROM $database LIKE '$table_name' ";
    
    if($q = $this->pdo->query($s)){
      if($q->rowCount()){
        return true;
      }  
    }
    
    return false;
  }
  
  function delete_table(){
    $output = $module_name = '';
    $output .= '
    <h3>Удаление модуля </h3>';
    if( isset($_GET['table_name']) && isset($_GET['table_name']) ){
      $table_name = addslashes( $_GET['table_name'] );
      
      if( !$this->is_database( $table_name ) ){
        $output .= '
        <div class="alert alert-danger" role="alert">
          Модуль '.$table_name.' Отсутствует!
        </div>';
      }else{
        $sql = "DROP TABLE `$table_name`";
        if($q = $this->pdo->query($sql)){
          $output .= '
          <div class="alert alert-success" role="alert">
            Таблица '.$table_name.' УДАЛЕНА!
          </div>';
        }else{
          $error .= '
          <div class="alert alert-danger" role="alert">
            Не удалось УДАЛИТЬ таблицу '.$table_name.'!
          </div>';
        }
      }
    }else{
      $output .= '
        <div class="alert alert-danger" role="alert">
          Отсутствует название модуля!
        </div>';
    }
    
    return $output;
  }
  
  function delete_all_table(){
    
    $output = '';
    
    $output .= '
    <h3>Удаление Базы Данных </h3>';
    
    $s_tn = "
    SELECT table_name AS table_name 
    FROM   information_schema.tables
    WHERE  table_schema = DATABASE()
    ";
    
    if($q_tn = $this->pdo->query($s_tn)){
      if($q_tn->rowCount()){
        while($r_tn = $q_tn->fetch()){
          $table_name = $r_tn['table_name'];
          
          $sql = "DROP TABLE `$table_name`";
          if($q = $this->pdo->query($sql)){
            $output .= '
            <div class="alert alert-success" role="alert">
              Таблица '.$table_name.' УДАЛЕНА!
            </div>';
          }else{
            $error .= '
            <div class="alert alert-danger" role="alert">
              Не удалось УДАЛИТЬ таблицу '.$table_name.'!
            </div>';
          }
          
        }
      }else{
        $output .= '
          <div class="alert alert-success" role="alert">
            Базы Данных уже удалена.
          </div>';
      }
    }
        
    return $output;
  }
  
  function delete_all_images(){
    $output = $rez = '';
    $output .= '
    <h3>Удаление Файлов (Изображений) </h3>';
    $rez = shell_exec('cd ../images; ls; rm -r *');
    
    if($rez){
      $output .= '
      <div class="alert alert-success" role="alert">
        <b>Удаление:</b> <pre>'.$rez.'</pre>
      </div>';  
    }else{
      $output .= '
      <div class="alert alert-success" role="alert">
        Файлы уже удалены
      </div>';  
    }
    
    
    return $output;
  }
  
  function sql_insert_database_table( $table_name, &$sql_insert){
    $output = '';
    
    if($sql_insert) {
      $this->pdo->query("TRUNCATE TABLE  `$table_name`");
      if($q_insert = $this->pdo->query($sql_insert)){
        $output .= '
      <div class="alert alert-success" role="alert">
        Таблица '.$table_name.' Заполнена значениями по умолчанию!
      </div>';  
      }else{
        $output .= '
        <div class="alert alert-danger" role="alert">
          Не удалось заполнить таблицу '.$table_name.' значениями по умолчанию!!
        </div>';
      }
      
    }
    
    return $output;
  }
  
  function sql_def_insert_database_table( $module_name, $table_name, &$sql_insert, $script_name = null){
    $output = '';
    if($ins_text = $this->sql_insert_database_table( $table_name, $sql_insert  )){
      $output .= '
      <h4>Наполнение модуля `'.$module_name.'`</h4>';
      $output .= $ins_text;
      if($script_name){
        $output .= '
        <div class="alert alert-primary" role="alert">
          Управление модулем `<b><a href = "'.IA_URL.$script_name.'" target = "_blank">'.$module_name.'</a></b>`.
        </div>';
      }
    }
    return $output;
  }
    
  function setup_database_table($module_name, $table_name, &$sql, &$sql_insert = null, $script_name = null  ){
   
    $output = $error = '';
    
    
    $output .= '
    <h4>Установка модуля `'.$module_name.'`</h4>';
    if( !$this->is_database( $table_name ) ){
      $output .= '
      <div class="alert alert-danger" role="alert">
        Таблица '.$table_name.' отсутствует!
      </div>';
      
      if($q = $this->pdo->query($sql)){
        $output .= '
        <div class="alert alert-success" role="alert">
          Таблица '.$table_name.' создана!
          &nbsp; <a class="btn btn-outline-danger btn-sm float-right" href = "/'.ADM_DIR.'/'.$this->script_name.'?step=delete_database_module&table_name='.$table_name.'">Удалить</a>
        </div>';
        $output .= $this->sql_insert_database_table($table_name, $sql_insert);
      }else{
        $error .= '
        <div class="alert alert-danger" role="alert">
          Не удалось установить таблицу '.$table_name.'!
        </div>';
      }
      
      
    }else{
      $output .= '
      <div class="alert alert-success" role="alert">
        Таблица '.$table_name.' существует! 
        &nbsp; <a class="btn btn-outline-danger btn-sm float-right" href = "/'.ADM_DIR.'/'.$this->script_name.'?step=delete_database_module&table_name='.$table_name.'">Удалить</a>
      </div>';
    }
    
    if($error){
      $output .= $error;
    }else{
      if($script_name){
        $output .= '
        <div class="alert alert-primary" role="alert">
          Управление модулем `<b><a href = "'.IA_URL.$script_name.'" target = "_blank">'.$module_name.'</a></b>`.
        </div>';
      }
    }
    
    return $output; 
  }
  
  function create_img_dir( $name ){
    if (!is_dir("../images")){
      mkdir("../images");
      chmod ("../images", 0777);
    }
    
    if (!is_dir("../images/".$name )){
      mkdir ( "../images/".$name );
      chmod ( "../images/".$name, 0755 );
    }
    
    if (!is_dir("../images/".$name."/orig")){
      mkdir("../images/".$name."/orig");
      chmod ("../images/".$name."/orig", 0755);
    }
    
    if (!is_dir("../images/".$name."/slide")){
      mkdir("../images/".$name."/slide");
      chmod ("../images/".$name."/slide", 0755);
    }
    
    if (!is_dir("../images/".$name."/temp")){
      mkdir("../images/".$name."/temp");
      chmod ("../images/".$name."/temp", 0755);
    }
    
    if (!is_dir("../images/".$name."/variations")){
      mkdir("../images/".$name."/variations");
      chmod ("../images/".$name."/variations", 0755);
    }
  }
  
  function create_file_dir( $name ){
    
    $this->create_img_dir( $name ); 
    
    if (!is_dir("../images/".$name."/files")){
      mkdir("../images/".$name."/files");
      chmod ("../images/".$name."/files", 0755);
    }
  }
  
  function copy_file( $file_name, $source, $target ){ 
    $output = '';
    try { 
      if($current_file = file_get_contents( $source ) ){
        file_put_contents($target, $current_file );
      }
    } 
    catch (PDOException $e) { 
      $output .= '
        <div class="alert alert-danger" role="alert">
          Ошибка копирования файла `'.$file_name.'` Источник `'.$source.'`!
        </div>';
      #pri( 'Ошибка копирования файла `'.$file_name.'` Источник `'.$source.'`' );
    }
     
    /*$ch = curl_init( $source );
    $fp = fopen( $target, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);*/
    
    return $output;
  }
  
  function copy_img_module( $name, $source_site ){
    $output = '';
    $table = DB_PFX.$name;
    $items = db::select('*', $table, null, null, null, null, 0 );
    
    foreach($items as $item){
      if( isset($item['img']) && $item['img'] ){
        
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/orig/'.$item['img'],
                                      '../images/'.$name.'/orig/'.$item['img']  ); 
                         
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/slide/'.$item['img'],
                                      '../images/'.$name.'/slide/'.$item['img']  );
      }
    }
    
    return $output;
  }
  
  function copy_img_module_cat( $name, $source_site ){
    $output    = '';
    $table_cat = DB_PFX.$name.'_cat';
    $items     = db::select('*', $table_cat, null, null, null, null, 0 );
    
    foreach($items as $item){
      if( isset($item['img']) && $item['img'] ){
        
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/cat/orig/'.$item['img'],
                                      '../images/'.$name.'/cat/orig/'.$item['img']  ); 
                         
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/cat/slide/'.$item['img'],
                                      '../images/'.$name.'/cat/slide/'.$item['img']  );
      }
    }
    
    return $output;
  }
  
  function copy_file_module( $name, $source_site ){
    $output = '';
    $table = DB_PFX.$name;
    $items = db::select('*', $table, null, null, null, null, 0 );
    
    foreach($items as $item){
      
      if( isset($item['img']) && $item['img'] ){
        
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/orig/'.$item['img'],
                                      '../images/'.$name.'/orig/'.$item['img']  ); 
                         
        $output .= $this->copy_file(  $item['img'], 
                                      $source_site.'/images/'.$name.'/slide/'.$item['img'],
                                      '../images/'.$name.'/slide/'.$item['img']  );
      }
      
      if( isset($item['file']) && $item['file'] ){
        
        $output .= $this->copy_file(  $item['file'], 
                                      $source_site.'/images/'.$name.'/files/'.$item['file'],
                                      '../images/'.$name.'/files/'.$item['file']  ); 
        
      }
    }
    
    return $output;
  }
  
  function create_cat_img_dir( $name ){
    
    $this->create_img_dir( $name );
    
    if (!is_dir("../images/".$name."/cat")){
      mkdir("../images/".$name."/cat");
      chmod ("../images/".$name."/cat", 0755);
    }
    
    if (!is_dir("../images/".$name."/cat/orig")){
      mkdir("../images/".$name."/cat/orig");
      chmod ("../images/".$name."/cat/orig", 0755);
    }
    
    if (!is_dir("../images/".$name."/cat/slide")){
      mkdir("../images/".$name."/cat/slide");
      chmod ("../images/".$name."/cat/slide", 0755);
    }
    
    if (!is_dir("../images/".$name."/cat/temp")){
      mkdir("../images/".$name."/cat/temp");
      chmod ("../images/".$name."/cat/temp", 0755);
    }
        if (!is_dir("../images/".$name."/cat/variations")){
      mkdir("../images/".$name."/cat/variations");
      chmod ("../images/".$name."/cat/variations", 0755);
    }
  }
  
  #--------------------- setup_database_module_required --------------------- 
  
  function setup_module_config( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(256) NOT NULL,
        `title` varchar(256) NOT NULL,
        `val` text NOT NULL,
        `type` tinyint(1) NOT NULL DEFAULT '0',
        `comment` varchar(256) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `name`, `title`, `val`, `type`, `comment`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'email_order', 'E-mail', '1@in-ri.ru', 0, 'отпарка писем', 0, 0),
      (2, 'phone', 'Телефон', '<a href="tel:+79058010809">+7 (905) 801-08-09‬</a>', 0, '', 0, 0),
      (4, 'adress', 'Адрес', '620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404', 0, '', 0, 0),
      (5, 'email', 'E-mail', '1@in-ri.ru', 0, '', 0, 0),
      (8, 'soc_net', 'Социальные сети', '<a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-vk" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-odnoklassniki" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-youtube-play" aria-hidden="true"></i></a>\r\n<a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>', 1, '', 0, 0),
      (3, 'whatsap_phone', 'Телефон whatsap', '+79058010809', 0, 'Пишется только цифрами без пробелов', 0, 0),
      (6, 'working_hour', 'Время работы', 'Время работы: ежедневно с 10-00 до 18-00', 0, '', 0, 0) 
HTML;
    return $this->setup_database_table( $title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_seo( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = ''; 
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `view` int(1) DEFAULT NULL,
        `ord` int(11) NOT NULL DEFAULT '0',
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `value` text NOT NULL,
        `comment` varchar(256) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_design( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `view` int(1) DEFAULT NULL,
        `ord` int(11) NOT NULL DEFAULT '0',
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `value` text NOT NULL,
        `comment` varchar(256) DEFAULT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `type`, `view`, `ord`, `hide`, `value`, `comment`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Стили', 'user_style', 2, 130, 0, '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" >\r\n\r\n<style>\r\n  .test{\r\n	padding: 20px;\r\n  }\r\n  .mine_slider {\r\n    /*max-width: 1920px;*/\r\n  }\r\n</style>\r\n\r\n', 'Пользовательские css стили'),
(2, 'Скрипты, счетчики', 'user_script', 2, 120, 0, '<script type="text/javascript">\r\n\r\n</script>\r\n', 'Яндекс метрика, liveinternet.ru и пользовательские js скрипты'),
(3, 'Файлы', 'user_file', 3, 140, 0, '', 'Пользовательские файлы. ( путь до папки /images_ckeditor/files <a href = "/images_ckeditor/files/test.jpg" target = "_blank">тест</a> )'),
(4, 'meta теги', 'user_meta', 2, 90, 0, '', 'Подтверждение прав: вембмастер, метрика и др.'),
(5, 'robots.txt', 'user_robots', 2, 100, 0, 'User-agent: * \r\nHost:\r\nSitemap: /sitemap.xml\r\n', 'Пользовательские css стили'),
(6, 'Логотип', 'user_logo', 0, 150, 0, '/images_ckeditor/files/logo.png', 'Логотип. ( путь до папки /images_ckeditor/files <a href = "/images_ckeditor/files/test.jpg" target = "_blank">тест</a> )'),
(7, 'Слоган', 'user_site_slogan', 0, 160, 0, 'Слоган', ''),
(8, 'Фавикон', 'user_favicon', 0, 170, 0, '/images_ckeditor/files/favicon.ico', '');
HTML;
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_url( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `url` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `url` (`url`),
        KEY `module` (`module`),
        KEY `module_id` (`module_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_accounts( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(20) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) DEFAULT NULL,
        `img` varchar(255) DEFAULT NULL,
        `login` varchar(255) NOT NULL DEFAULT '',
        `key` varchar(32) NOT NULL DEFAULT '',
        `hash` varchar(32) NOT NULL DEFAULT '',
        `fullname` varchar(255) NOT NULL DEFAULT '',
        `is_admin` tinyint(1) NOT NULL DEFAULT '0',
        `is_programmer` tinyint(1) NOT NULL DEFAULT '0',
        `untouchible` tinyint(1) NOT NULL DEFAULT '0',
        `iscontent` tinyint(4) NOT NULL DEFAULT '0',
        `ismanag` tinyint(4) NOT NULL DEFAULT '0',
        `iscatalog` tinyint(4) NOT NULL DEFAULT '0',
        `isjournalist` tinyint(1) NOT NULL DEFAULT '0',
        `email` varchar(255) DEFAULT NULL,
        `phone` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `login`, `key`, `hash`, `fullname`, `is_admin`, `is_programmer`, `untouchible`, `iscontent`, `ismanag`, `iscatalog`, `isjournalist`, `email`, `phone`, `longtxt1`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'Администратор', '1552249940.jpeg', 'd', '1d319e1b83c1b7ec90328bcca7e6e200', '7d86b76f7b71aae04d6cd2d59090b4e9', 'Илья Ощепков', 1, 1, 0, 1, 1, 1, 1, 'ilya.oshepkov@gmail.com', '9058010809', '', 0, 0),
(2, 'Администратор', NULL, 'admin_inri', 'a321e4bf3dd0cadcb50b0f7053fb80c5', '0b25aeb39224a071ea2a4857b2f41e4a', 'Админ', 1, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0);
HTML;
    
    $output .= $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_mine_block( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    $sql = "
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `img` varchar(255) NOT NULL,
      `link` varchar(255) DEFAULT NULL,
      `longtxt2` text,
      `fl_is_fixed` tinyint(1) NOT NULL DEFAULT '0',
      `hide` tinyint(1) NOT NULL DEFAULT '0',
      `ord` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_carusel( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `link` varchar(255) DEFAULT NULL,
        `txt1` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_reservations( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `userIp` varchar(255) DEFAULT NULL,
        `userName` varchar(255) DEFAULT NULL,
        `userPhone` varchar(255) DEFAULT NULL,
        `userMail` varchar(255) DEFAULT NULL,
        `userStatus` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_admin_logs( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `ip` varchar(15) NOT NULL,
      `user_id` int(11) NOT NULL,
      `date_time` datetime NOT NULL,
      `action` int(11) NOT NULL,
      `item_id` int(11) NOT NULL,
      `changes` text NOT NULL,
      `script` varchar(16) NOT NULL,
      PRIMARY KEY (`id`),
      KEY `ip` (`ip`,`user_id`,`date_time`,`action`,`script`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = '';
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_all_log( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `type` varchar(255) NOT NULL,
        `user_id` varchar(255) NOT NULL,
        `ip` varchar(15) DEFAULT NULL,
        `int_ip` int(10) DEFAULT NULL,
        `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        `dump_data` text,
        `query` text,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = '';
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_all_images( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `img` varchar(255) NOT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        `module_ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    
    $this->create_img_dir( $name );
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_all_files( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    $sql_insert = '';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `module` varchar(255) NOT NULL,
        `module_id` int(11) NOT NULL,
        `img` varchar(255) NOT NULL,
        `file` varchar(255) NOT NULL,
        `longtxt1` text,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        `module_ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $output = '';
    $output .= $this->create_file_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  
  #--------------------- setup_database_module_cutaway --------------------- 
  function insert_def_module_url_cutaway( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `url`, `module`, `module_id`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
        (1, 'Главная', 'glavnaya', 'inri_smpl_article', 1, 0, 0),
        (2, 'О компании', 'kompanii', 'inri_smpl_article', 2, 0, 0),
        (3, 'Фотогалерея', 'fotogalereya', 'inri_smpl_article', 3, 0, 0),
        (4, 'Документы', 'dokumenty', 'inri_smpl_article', 4, 0, 0),
        (5, 'Дилеры', 'dilery', 'inri_smpl_article', 5, 0, 0),
        (6, 'Контакты', 'kontakty', 'inri_smpl_article', 6, 0, 0),
        (8, 'Блок 1', 'blok-1', 'inri_mine_block', 2, 0, 0),
        (9, 'Блок 2', 'blok-2', 'inri_mine_block', 3, 0, 0),
        (10,'Блок 3', 'blok-3', 'inri_mine_block', 4, 0, 0),
        (11,'Слайдер на главной',  'slayder-na-glavnoy', 'inri_mine_block', 5, 0, 0),
        (12,'Услуги', 'uslugi', 'inri_smpl_article', 7, 0, 0),
        (13,'Шапка сайта', 'shapka-sayta', 'inri_mine_block', 6, 0, 0),
        (14,'Главное меню', 'glavnoe-menyu', 'inri_mine_block', 7, 0, 0),
        (15,'Контент на внутренних страницах',  'kontent-na-vnutrennih-stranicah', 'inri_mine_block', 8, 0, 0),
        (16,'robots.txt',  'robots_txt', 'robots_txt', 0, 0, 0)
        
HTML;
        
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_mine_block_cutaway( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt2`, `fl_is_fixed`, `hide`, `ord`) VALUES ";  
    $sql_insert .=<<<HTML
(2, 'Блок 3', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h1>Блок</h1>\r\n\r\n<h2 style="text-align: center;">Контакты</h2>\r\n</div>\r\n</div>\r\n<!-- сontacts_inretactive -->\r\n\r\n<div class="сontacts_inretactive_box">\r\n<div class="сontacts_inretactive">\r\n<div class="ya_map"><script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script></div>\r\n\r\n<div class="сontacts_descr">\r\n<p><b>Наш адрес:</b><br />\r\n620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Телефоны:</b><br />\r\n<a href="tel:+79058010809">+7 (905) 801-08-09</a></p>\r\n\r\n<p><b>Электропочта:</b><br />\r\n<a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n</div>\r\n<!-- End сontacts_inretactive --></div>\r\n</div>\r\n<style type="text/css">/* --- сontacts --- */\r\n.сontacts_box{\r\n  background: url("img/mc_bg.png") no-repeat center center;\r\n  background-size: cover;\r\n  min-height: 490px;\r\n  padding-bottom: 0px;\r\n}\r\n.сontacts{\r\n  padding-bottom: 20px;\r\n}\r\n.сontacts_descr{\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  margin: 0 auto;\r\n  margin-top: 10px;\r\n  padding: 15px 25px;\r\n  border: 10px solid #17a2b8;\r\n  text-align: center;\r\n}\r\n.сontacts_descr b{\r\n  font: 400 25px/30px AvantGardeGothicBdITC-Reg, Arial, sans-serif;\r\n  color: #000000;\r\n}\r\n.сontacts_descr a{\r\n  color: #000000;\r\n}\r\n/* --- End сontacts --- */\r\n  \r\n  /* --- сontacts_inretactive_box --- */\r\n.сontacts_inretactive_box{\r\n  min-height: 500px;\r\n  padding-bottom: 0px;\r\n  \r\n}\r\n.сontacts_inretactive{\r\n  padding-bottom: 20px;\r\n  position: relative;\r\n}\r\n.ya_map{\r\n  position: absolute;\r\n  top: 0;\r\n  height: 500px;\r\n  width: 100%;\r\n}\r\n.сontacts_inretactive .mtitle_box{\r\n  /*position: relative;\r\n  z-index: 1;\r\n  text-align: right;\r\n  margin-right: 15px;*/\r\n}\r\n\r\n.сontacts_inretactive .сontacts_descr{\r\n  margin: 55px 15px 15px 25px;\r\n  position: relative;\r\n  z-index: 1;\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  float: right;\r\n}\r\n@media (max-width: 768px){\r\n  .сontacts_inretactive .сontacts_descr{\r\n    margin: 0 auto 0 auto;\r\n    float: none;\r\n  }\r\n  .сontacts_inretactive{\r\n    padding-top: 525px;\r\n  }\r\n}\r\n/* --- End сontacts_inretactive --- */\r\n</style>\r\n', 0, 0, 6),
(3, 'Блок 1', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок</h2>\r\n</div>\r\n</div>\r\n\r\n<div class="block_box bl_bg" style="">\r\n<div class="block_b" style="background: rgba(0, 0, 0, 0.3);\r\n    padding-top: 150px;\r\n    padding-bottom: 150px;">\r\n<div class="block">\r\n<h2 style="color: #fff;">ПРОЕКТИРОВАНИЕ И ДИЗАЙН</h2>\r\n\r\n<p>Внешний вид сайта - лицо компании. Важно, чтобы он был не только красивым, но и удобным. Интуитивно понятный интерфейс пользователя плюс отзывчивый дизайн увеличивают конверсию и продажи.</p>\r\n\r\n<h2 style="color: #fff;">РАЗРАБОТКА</h2>\r\n\r\n<p>На этапе разработки учитывается адаптивность сайта. Он должен корректно отображаться в любом браузере и на всех видах устройств. Удобная система управления сайтом позволит вам вести проект самостоятельно.</p>\r\n\r\n<h2 style="color: #fff;">ПРОДВИЖЕНИЕ И ПОДДЕРЖКА</h2>\r\n\r\n<p>Как не потеряться в медиа пространстве? Мы готовы сотрудничать с вами, отзываясь на любые просьбы и следим за тем, чтобы ваш сайт был в топе поисковиков.</p>\r\n\r\n<p><b>ВЕБ-студия <a href="//in-ri.ru" style="color: #dc3545;">in-ri.ru</a> занимается разработкой сайтов, поддержкой, созданием интернет-магазинов, интернет-рекламой и&nbsp;поисковой оптимизацией.</b></p>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">.bl_bg{\r\n    background-image: url(/images_ckeditor/files/test.jpg);\r\n    background-attachment: fixed;\r\n    background-size: cover;\r\n    background-position: center center;\r\n    text-shadow: 1px 1px 16px rgba(0, 0, 0, 0.8);\r\n    color: #ffffff;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n    margin-bottom: 0px;\r\n    /*-webkit-transform: translate3d(0,0,0);*/\r\n    -webkit-backface-visibility: hidden;\r\n  }\r\n  .admin_edit_box .bl_bg{\r\n    /*position: relative;\r\n    z-index: 1600;*/\r\n  }\r\n</style>\r\n', 0, 0, 5),
(4, 'Блок 2', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок&nbsp;</h2>\r\n\r\n<div class="scheme">\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-globe fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Встречи 1 раз в неделю</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-tasks fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">График работы</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-external-link-alt fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Новые техники</div>\r\n</div>\r\n</div>\r\n\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-clock fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Поддержка</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-percent fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">100%</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-sun fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Качественный результат</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">/* --- scheme --- */\r\n.scheme{\r\n	margin-top: 25px;\r\n  margin-bottom: 25px;\r\n}\r\n.scheme .c_img_box{\r\n  text-align:center;\r\n  /*color: #28a745;*/\r\n  padding-bottom: 25px;\r\n  padding-top: 15px;\r\n}\r\n.scheme .c_title{\r\n	text-align:center;\r\n  padding-bottom: 35px;\r\n  font-size: 24px;\r\n  font-weight: bold;\r\n}\r\n.steps{\r\n  \r\n}\r\n.steps .rim{\r\n  display: inline-block;\r\n  width: 25px;\r\n  text-align:center;\r\n}\r\n.steps .left{\r\n  /*text-align:right;*/\r\n}\r\n.steps .right{\r\n  border-left: 2px solid #37373e59;\r\n  border-right: 2px solid #37373e59;\r\n  border-top: 2px solid #37373e59;\r\n  padding-top: 20px;\r\n  padding-bottom: 20px;\r\n  /*min-height: 75px;*/\r\n}\r\n.steps .row:last-child {\r\n  \r\n  /*border-bottom: 3px solid #37373e;*/\r\n}\r\n/* --- END scheme --- */\r\n</style>\r\n', 1, 0, 4),
(5, 'Слайдер', '', 'block_mine_slider', '', 0, 0, 2),
(6, 'Шапка сайта', '', 'block_mine_header', '', 1, 0, 0),
(7, 'Меню сайта', '', 'block_mine_top_menu', '', 1, 0, 1),
(8, 'Контент на внутренних страницах', '', 'block_inner_content', '', 1, 0, 3),
(9, 'Подвал сайта', '', 'block_mine_footer', '', 1, 0, 7);
HTML;
    
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_carusel_cutaway( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `txt1`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Слайд №1', '1532522017.jpg', '', '', '', '', '', 0, 0),
(2, 'Слайд №2', '1532521995.jpg', '', 'Текст', '<p>Описание описание описание описание</p>\r\n', 'Alt изображение', 'Title изображение', 0, 1),
(3, 'Слайд №3', '1532522040.jpg', '', '', '', '', '', 0, 2),
(4, 'Слайд №4', '1531731541.jpg', '', '', '', '', '', 0, 3),
(5, 'Слайд №5', '1531731565.jpg', '', '', '', '', '', 0, 4);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function insert_def_module_seo_cutaway( $title, $name ){
    
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `type`, `view`, `ord`, `hide`, `value`, `comment`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'Title главой', 'mine_title', NULL, 10, 0, 'Сайт визитка', NULL),
      (2, 'Description Главной', 'mine_description', 1, 20, 0, 'Сайт визитка', NULL),
      (3, 'Keywords главной', 'mine_keywords', NULL, 21, 0, '', NULL),
      (4, 'Title текстовой статьи', 'lib_text_title', NULL, 110, 0, '*h1* ', NULL),
      (5, 'Title текстового раздела', 'lib_cat_title', NULL, 100, 1, '*h1*', NULL)
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    
    return $output;
  }
  
  function insert_def_module_all_images_cutaway( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(70, 'yamaha scr950.jpg', 'inri_smpl_article', 3, '1532522961_10.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(69, 'QOVZzEETzGY.jpg', 'inri_smpl_article', 3, '1532522961_9.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(68, 'HONDACB900FHornet-5190_3.jpg', 'inri_smpl_article', 3, '1532522961_8.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(63, '14658847586171.jpg', 'inri_smpl_article', 3, '1532522961_3.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(64, '14658847586242.jpg', 'inri_smpl_article', 3, '1532522961_4.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(65, '14658847586293.jpg', 'inri_smpl_article', 3, '1532522961_5.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(66, '14764684961551.jpg', 'inri_smpl_article', 3, '1532522961_6.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(67, '14785962379880.jpg', 'inri_smpl_article', 3, '1532522961_7.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(62, '14658847586130.jpg', 'inri_smpl_article', 3, '1532522961_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(61, '14657292234510.jpg', 'inri_smpl_article', 3, '1532522961_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(60, '4.jpg', 'inri_smpl_article', 3, '1532522961_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(71, 'mMAPhsLGD6w.jpg', 'inri_smpl_article', 3, '1532523022_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(72, 'QPyh7YBBraM.jpg', 'inri_smpl_article', 3, '1532523023_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(73, 'rNqjPvV3a2Q.jpg', 'inri_smpl_article', 3, '1532523024_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0);
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function insert_def_module_all_files_cutaway( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `file`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, '1972.pdf', 'inri_smpl_article', 4, '', '1530877350_0.pdf', NULL, NULL, NULL, 0, 0, 0),
(2, '1973.pdf', 'inri_smpl_article', 4, '', '1530877350_1.pdf', NULL, NULL, NULL, 0, 0, 0),
(3, '1974.pdf', 'inri_smpl_article', 4, '', '1530877350_2.pdf', NULL, NULL, NULL, 0, 0, 0),
(4, '1975.pdf', 'inri_smpl_article', 4, '', '1530877350_3.pdf', NULL, NULL, NULL, 0, 0, 0),
(5, '1976.pdf', 'inri_smpl_article', 4, '', '1530877350_4.pdf', NULL, NULL, NULL, 0, 0, 0);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    $output .= $this->copy_file_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_smpl_article_cutaway( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `link` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `fl_mine_menu` tinyint(1) DEFAULT NULL,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `date`, `link`, `longtxt1`, `longtxt2`, `fl_mine_menu`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Главная', '', '', '/', '', '', 1, '', '', '', '', '', '', 'ГЛАВНЫЙ', '', 0, 0),
(2, 'О компании', '', '', '', '', '<h3>Пример текста &laquo;О компании&raquo;, как его написать &ndash; &laquo;пикап&raquo; потенциального клиента</h3>\r\n\r\n<p>Вы на рынке с 2002 года? Мне всё равно. &laquo;АвтоВАЗ&raquo; на рынке дольше вас&hellip; У вас хорошая динамика развития и молодой, дружный коллектив? Отлично, то есть, опыта у сотрудников маловато&hellip; Закупили дорогое немецкое оборудование (в кредит?), когда рубль балансировал на ободке унитаза? Значит, теперь мне за это расплачиваться? Прощайте!</p>\r\n\r\n<p>Как написать текст на страницу &laquo;О компании&raquo; и выбросить из словосочетания &laquo;потенциальный клиент&raquo; первое слово? Сделать это в среде &laquo;неправильных&raquo; клиентов. Ведь они, как девушка, которую постоянно атакуют пикаперы: интерес проявляет, но в машину не садится.</p>\r\n\r\n<p>Проблема в том, что у вас все как у всех: низкие цены, надежное оборудование и специалисты &ndash; профессионалы своего дела, у которых клиентоориенитрованность на нуле.</p>\r\n\r\n<p>Если не знаете, как написать текст о компании для сайта, и нужны примеры, то эта работа для <a href="//in-ri.ru" target="_blank">нас</a>.</p>\r\n', 1, '', '', '', '', '', '', 'О КОМПАНИЯ', 'ПРИМЕР ТЕКСТ LAQUO О КОМПАНИЯ RAQUO КАК ЕГО НАПИСАТЬ NDASH LAQUO ПИКАП RAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ ВЫ НА РЫНОК С 2002 ГОД Я ВЕСЬ РАВНО LAQUO АВТОВАЗ RAQUO НА РЫНОК ДОЛГИЙ ВЫ HELLIP У ВЫ ХОРОШИЙ ДИНАМИК РАЗВИТИЕ И МОЛОДАЯ ДРУЖНЫЙ КОЛЛЕКТИВ ОТЛИЧНО ТО ЕСТЬ ОПЫТ У СОТРУДНИК МАЛОВАТЫЙ HELLIP ЗАКУПИТЬ ДОРОГОЙ НЕМЕЦКИЙ ОБОРУДОВАНИЕ В КРЕДИТ КОГДА РУБЛЬ БАЛАНСИРОВАТЬ НА ОБОДОК УНИТАЗ ЗНАЧИТ ТЕПЕРЬ Я ЗА ЭТО РАСПЛАЧИВАТЬСЯ ПРОЩАТЬ КАК НАПИСАТЬ ТЕКСТ НА СТРАНИЦА LAQUO О КОМПАНИЯ RAQUO И ВЫБРОСИТЬ ИЗ СЛОВОСОЧЕТАНИЕ LAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ RAQUO ПЕРВЫЙ СЛОВО СДЕЛАТЬ ЭТО В СРЕДА LAQUO НЕПРАВИЛЬНЫЙ RAQUO КЛИЕНТ ВЕДЬ ОНИ КАК ДЕВУШКА КОТОРЫЙ ПОСТОЯННО АТАКОВАТЬ ПИКАПЕР ИНТЕРЕС ПРОЯВЛЯТЬ НО В МАШИН НЕ САДИТЬСЯ ПРОБЛЕМА В ТОМ ЧТО У ВЫ ВСЕ КАК У ВЕСЬ НИЗКИЙ ЦЕНА НАДЕЖНЫЙ ОБОРУДОВАНИЕ И СПЕЦИАЛИСТ NDASH ПРОФЕССИОНАЛ СВОЕ ДЕТЬ У КОТОРЫЙ КЛИЕНТООРИЕНИТРОВАННОСТЬ НА НУЛЬ ЕСЛИ НЕ ЗНАТЬ КАК НАПИСАТЬ ТЕКСТ О КОМПАНИЯ ДЛЯ САЙТ И НУЖНЫЙ ПРИМЕР ТО ЭТОТ РАБОТА ДЛЯ МЫ', 0, 3),
(3, 'Фотогалерея', '', '', '', '', '', 1, '', '', '', '', '', '', 'ФОТОГАЛЕРЕЯ', '', 0, 1),
(4, 'Документы', '', '', '', '', '', 1, '', '', '', '', '', '', 'ДОКУМЕНТ', '', 0, 2),
(5, 'Дилеры', '', '', '', '', '', 0, '', '', '', '', '', '', 'ДИЛЕР', '', 0, 5),
(6, 'Контакты', '', '', '', '', '<p><b>Директор: Ощепков Илья Александрович</b></p>\r\n\r\n<p><b>E-mail</b>: <a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n\r\n<p><b>Телефоны в Екатеринбурге:</b></p>\r\n\r\n<p><b>Сот. <a href = "tel:+79058010809">+7 (905) 801-08-09</a></b></p>\r\n\r\n<p><b>Адрес:</b> 620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Время работы:</b> пн-пт 10:00-19:00, сб-вс 11:00-18:00<br />\r\n&nbsp;</p>\r\n<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script>\r\n\r\n<p>&nbsp;</p>\r\n\r\n', 1, '', '', '', '', '', '', 'КОНТАКТ', 'ДИРЕКТОР ОЩЕПОК ИЛЬЯ АЛЕКСАНДР E-MAIL 1 IN-RI RU ТЕЛЕФОН В ЕКАТЕРИНБУРГ СОТЫ 7 905 801-08-09 АДРЕС 620100 Г ЕКАТЕРИНБУРГ УТЬ СИБИРСКИЙ ТРАКТ 12 2 ОФИС 404 ВРЕМЯ РАБОТА ПН-ПТ 10 00-19 00 СБ-ВС 11 00-18 00 NBSP NBSP', 0, 4),
(7, 'Услуги', '', '', '', '', '<ul>\r\n	<li>Услуга 1</li>\r\n	<li>Услуга 2</li>\r\n	<li>Услуга 3</li>\r\n</ul>\r\n', 0, '', '', '', '', '', '', 'УСЛУГА', 'УСЛУГА 1 УСЛУГА 2 УСЛУГА 3', 0, 6);
HTML;
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  
  #--------------------- setup_database_module_corporate ---------------------
  function insert_def_module_carusel_corporate( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `txt1`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Слайд №1', '1531731471.jpg', '', '', '', '', '', 0, 0),
(2, 'Слайд №2', '1531731498.jpg', '', 'Текст', '<p>Описание описание описание описание</p>\r\n', 'Alt изображение', 'Title изображение', 0, 1),
(3, 'Слайд №3', '1531731518.jpg', '', '', '', '', '', 0, 2),
(4, 'Слайд №4', '1531731541.jpg', '', '', '', '', '', 0, 3),
(5, 'Слайд №5', '1531731565.jpg', '', '', '', '', '', 0, 4);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  function insert_def_module_seo_corporate( $title, $name ){
    
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `type`, `view`, `ord`, `hide`, `value`, `comment`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'Title главой', 'mine_title', NULL, 10, 0, 'Корпоративный сайт', NULL),
      (2, 'Description Главной', 'mine_description', 1, 20, 0, 'Корпоративный сайт', NULL),
      (3, 'Keywords главной', 'mine_keywords', NULL, 21, 0, '', NULL),
      (4, 'Title текстовой статьи', 'lib_text_title', NULL, 110, 0, '*h1* ', NULL),
      (5, 'Title текстового раздела', 'lib_cat_title', NULL, 100, 0, '*h1*', NULL),
      (6, 'Title новости', 'news_title', NULL, 90, 0, '*h1*', NULL)
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    
    return $output;
  }
  
  function insert_def_module_url_corporate( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `url`, `module`, `module_id`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (8, 'Блок 1', 'blok', 'inri_mine_block', 2, 0, 6),
      (9, 'Блок 2', 'blok-74548', 'inri_mine_block', 3, 0, 7),
      (10, 'Блок 3', 'blok-86291', 'inri_mine_block', 4, 0, 8),
      (11, 'Слайдер на главной', 'slayder-na-glavnoy', 'inri_mine_block', 5, 0, 9),
      (13, 'Шапка сайта', 'shapka-sayta', 'inri_mine_block', 6, 0, 11),
      (14, 'Главное меню', 'glavnoe-menyu', 'inri_mine_block', 7, 0, 12),
      (15, 'Контент на внутренних страницах', 'kontent-na-vnutrennih-stranicah', 'inri_mine_block', 8, 0, 13),
      (16, 'robots.txt', 'robots_txt', 'robots_txt', 0, 0, 14),
      (17, 'Главное меню', 'glavnoe-menyu-65489', 'inri_articles_cat', 1, 0, 15),
      (18, 'Главная', 'glavnaya', 'inri_articles_cat', 2, 0, 16),
      (19, 'О компании', 'kompanii', 'inri_articles_cat', 3, 0, 17),
      (20, 'Контакты', 'kontakty', 'inri_articles_cat', 4, 0, 18),
      (21, 'Статьи', 'stati', 'inri_articles_cat', 5, 0, 19),
      (22, 'Основатель диджитал-студии Finch — о веб-дизайне в России и США', 'osnovatel-didzhitalstudii-finch-o', 'inri_articles', 1, 0, 20),
      (23, 'Киану Ривз: «Россия ассоциируется у меня с моральной силой»', 'kianu-rivz-rossiya-associiruetsya', 'inri_articles', 2, 0, 21),
      (24, 'От бритвы до автомобиля: почему мир переходит к сервисам по подписке', 'britvy-do-avtomobilya-pochemu-mir', 'inri_articles', 3, 0, 22),
      (25, 'Контент', 'kontent', 'inri_articles_cat', 6, 0, 23),
      (26, 'Фотогалерея', 'fotogalereya', 'inri_articles_cat', 7, 0, 24),
      (27, 'Документы', 'dokumenty', 'inri_articles_cat', 8, 0, 25),
      (28, 'Новости', 'news', 'inri_news', 0, 0, 26), 
      (30, 'Новости', 'novosti', 'inri_articles_cat', 9, 0, 28),
      (31, 'Петросян отреагировал на сообщения о разделе имущества со Степаненко', 'petrosyan-otreagiroval-na-soobscheniya-o', 'inri_news', 1, 0, 29),
      (32, 'Ученые назвали причину исчезновения кораблей в Бермудском треугольнике', 'uchenye-nazvali-prichinu-ischeznoveniya', 'inri_news', 2, 0, 30),
      (33, 'Марафон «Европа-Азия» соберёт более шести тысяч участников', 'marafon-evropaaziya-soberyot-bolee', 'inri_news', 3, 0, 31),
      (NULL, 'Политика организации по обработке персональных данных', 'politikoy-organizacii-po-obrabotke-personalnyh', 'inri_articles', 4, 0, 0),
      (NULL, 'Согласие посетителя сайта на обработку персональных данных', 'soglasie-posetitelya-sayta', 'inri_articles', 5, 0, 0);
HTML;
        
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_mine_block_corporate( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt2`, `fl_is_fixed`, `hide`, `ord`) VALUES ";  
    $sql_insert .=<<<HTML
(2, 'Блок 3', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h1>Блок</h1>\r\n\r\n<h2 style="text-align: center;">Контакты</h2>\r\n</div>\r\n</div>\r\n<!-- сontacts_inretactive -->\r\n\r\n<div class="сontacts_inretactive_box">\r\n<div class="сontacts_inretactive">\r\n<div class="ya_map"><script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script></div>\r\n\r\n<div class="сontacts_descr">\r\n<p><b>Наш адрес:</b><br />\r\n620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Телефоны:</b><br />\r\n<a href="tel:+79058010809">+7 (905) 801-08-09</a></p>\r\n\r\n<p><b>Электропочта:</b><br />\r\n<a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n</div>\r\n<!-- End сontacts_inretactive --></div>\r\n</div>\r\n<style type="text/css">/* --- сontacts --- */\r\n.сontacts_box{\r\n  background: url("img/mc_bg.png") no-repeat center center;\r\n  background-size: cover;\r\n  min-height: 490px;\r\n  padding-bottom: 0px;\r\n}\r\n.сontacts{\r\n  padding-bottom: 20px;\r\n}\r\n.сontacts_descr{\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  margin: 0 auto;\r\n  margin-top: 10px;\r\n  padding: 15px 25px;\r\n  border: 10px solid #17a2b8;\r\n  text-align: center;\r\n}\r\n.сontacts_descr b{\r\n  font: 400 25px/30px AvantGardeGothicBdITC-Reg, Arial, sans-serif;\r\n  color: #000000;\r\n}\r\n.сontacts_descr a{\r\n  color: #000000;\r\n}\r\n/* --- End сontacts --- */\r\n  \r\n  /* --- сontacts_inretactive_box --- */\r\n.сontacts_inretactive_box{\r\n  min-height: 500px;\r\n  padding-bottom: 0px;\r\n  \r\n}\r\n.сontacts_inretactive{\r\n  padding-bottom: 20px;\r\n  position: relative;\r\n}\r\n.ya_map{\r\n  position: absolute;\r\n  top: 0;\r\n  height: 500px;\r\n  width: 100%;\r\n}\r\n.сontacts_inretactive .mtitle_box{\r\n  /*position: relative;\r\n  z-index: 1;\r\n  text-align: right;\r\n  margin-right: 15px;*/\r\n}\r\n\r\n.сontacts_inretactive .сontacts_descr{\r\n  margin: 55px 15px 15px 25px;\r\n  position: relative;\r\n  z-index: 1;\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  float: right;\r\n}\r\n@media (max-width: 768px){\r\n  .сontacts_inretactive .сontacts_descr{\r\n    margin: 0 auto 0 auto;\r\n    float: none;\r\n  }\r\n  .сontacts_inretactive{\r\n    padding-top: 525px;\r\n  }\r\n}\r\n/* --- End сontacts_inretactive --- */\r\n</style>\r\n', 0, 0, 6),
(3, 'Блок 1', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок</h2>\r\n</div>\r\n</div>\r\n\r\n<div class="block_box bl_bg" style="">\r\n<div class="block_b" style="background: rgba(0, 0, 0, 0.3);\r\n    padding-top: 150px;\r\n    padding-bottom: 150px;">\r\n<div class="block">\r\n<h2 style="color: #fff;">ПРОЕКТИРОВАНИЕ И ДИЗАЙН</h2>\r\n\r\n<p>Внешний вид сайта - лицо компании. Важно, чтобы он был не только красивым, но и удобным. Интуитивно понятный интерфейс пользователя плюс отзывчивый дизайн увеличивают конверсию и продажи.</p>\r\n\r\n<h2 style="color: #fff;">РАЗРАБОТКА</h2>\r\n\r\n<p>На этапе разработки учитывается адаптивность сайта. Он должен корректно отображаться в любом браузере и на всех видах устройств. Удобная система управления сайтом позволит вам вести проект самостоятельно.</p>\r\n\r\n<h2 style="color: #fff;">ПРОДВИЖЕНИЕ И ПОДДЕРЖКА</h2>\r\n\r\n<p>Как не потеряться в медиа пространстве? Мы готовы сотрудничать с вами, отзываясь на любые просьбы и следим за тем, чтобы ваш сайт был в топе поисковиков.</p>\r\n\r\n<p><b>ВЕБ-студия <a href="//in-ri.ru" style="color: #dc3545;">in-ri.ru</a> занимается разработкой сайтов, поддержкой, созданием интернет-магазинов, интернет-рекламой и&nbsp;поисковой оптимизацией.</b></p>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">.bl_bg{\r\n    background-image: url(/images_ckeditor/files/test.jpg);\r\n    background-attachment: fixed;\r\n    background-size: cover;\r\n    background-position: center center;\r\n    text-shadow: 1px 1px 16px rgba(0, 0, 0, 0.8);\r\n    color: #ffffff;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n    margin-bottom: 0px;\r\n    /*-webkit-transform: translate3d(0,0,0);*/\r\n    -webkit-backface-visibility: hidden;\r\n  }\r\n  .admin_edit_box .bl_bg{\r\n    /*position: relative;\r\n    z-index: 1600;*/\r\n  }\r\n</style>\r\n', 0, 0, 5),
(4, 'Блок 2', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок&nbsp;</h2>\r\n\r\n<div class="scheme">\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-globe fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Встречи 1 раз в неделю</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-tasks fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">График работы</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-external-link-alt fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Новые техники</div>\r\n</div>\r\n</div>\r\n\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-clock fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Поддержка</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-percent fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">100%</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-sun fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Качественный результат</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">/* --- scheme --- */\r\n.scheme{\r\n	margin-top: 25px;\r\n  margin-bottom: 25px;\r\n}\r\n.scheme .c_img_box{\r\n  text-align:center;\r\n  /*color: #28a745;*/\r\n  padding-bottom: 25px;\r\n  padding-top: 15px;\r\n}\r\n.scheme .c_title{\r\n	text-align:center;\r\n  padding-bottom: 35px;\r\n  font-size: 24px;\r\n  font-weight: bold;\r\n}\r\n.steps{\r\n  \r\n}\r\n.steps .rim{\r\n  display: inline-block;\r\n  width: 25px;\r\n  text-align:center;\r\n}\r\n.steps .left{\r\n  /*text-align:right;*/\r\n}\r\n.steps .right{\r\n  border-left: 2px solid #37373e59;\r\n  border-right: 2px solid #37373e59;\r\n  border-top: 2px solid #37373e59;\r\n  padding-top: 20px;\r\n  padding-bottom: 20px;\r\n  /*min-height: 75px;*/\r\n}\r\n.steps .row:last-child {\r\n  \r\n  /*border-bottom: 3px solid #37373e;*/\r\n}\r\n/* --- END scheme --- */\r\n</style>\r\n', 1, 0, 4),
(5, 'Слайдер', '', 'block_mine_slider', '', 0, 0, 2),
(6, 'Шапка сайта', '', 'block_mine_header', '', 1, 0, 0),
(7, 'Меню сайта', '', 'block_mine_top_menu', '', 1, 0, 1),
(8, 'Контент на внутренних страницах', '', 'block_inner_content', '', 1, 0, 3),
(9, 'Подвал сайта', '', 'block_mine_footer', '', 1, 0, 7);
HTML;
    
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_all_images_corporate( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(60, '1531733498_0.jpg', 'inri_articles_cat', 7, '1533298966_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 1, 0),
(61, '1531733498_1.jpg', 'inri_articles_cat', 7, '1533298966_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 2, 0),
(62, '1531733498_2.jpg', 'inri_articles_cat', 7, '1533298966_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 3, 0),
(63, '1531733498_3.jpg', 'inri_articles_cat', 7, '1533298966_3.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 4, 0),
(64, '1531733498_4.jpg', 'inri_articles_cat', 7, '1533298966_4.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 5, 0),
(65, '1531733498_5.jpg', 'inri_articles_cat', 7, '1533298966_5.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 6, 0),
(66, '1531733498_6.jpg', 'inri_articles_cat', 7, '1533298966_6.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 7, 0),
(67, '1531733498_7.jpg', 'inri_articles_cat', 7, '1533298966_7.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 8, 0),
(68, '1531733498_8.jpg', 'inri_articles_cat', 7, '1533298966_8.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 9, 0),
(69, '1531733498_9.jpg', 'inri_articles_cat', 7, '1533298966_9.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 10, 0),
(70, '1531733498_10.jpg', 'inri_articles_cat', 7, '1533298966_10.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 11, 0),
(71, '1531733498_11.jpg', 'inri_articles_cat', 7, '1533298966_11.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 12, 0),
(72, '1531733498_12.jpg', 'inri_articles_cat', 7, '1533298966_12.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 13, 0);
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  function insert_def_module_all_files_corporate( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `file`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(6, '1972.pdf', 'inri_articles_cat', 8, '', '1533299057_0.pdf', NULL, NULL, NULL, 0, 0, 0),
(7, '1973.pdf', 'inri_articles_cat', 8, '', '1533299057_1.pdf', NULL, NULL, NULL, 0, 0, 0),
(8, '1974.pdf', 'inri_articles_cat', 8, '', '1533299057_2.pdf', NULL, NULL, NULL, 0, 0, 0),
(9, '1975.pdf', 'inri_articles_cat', 8, '', '1533299057_3.pdf', NULL, NULL, NULL, 0, 0, 0),
(10, '1976.pdf', 'inri_articles_cat', 8, '', '1533299057_4.pdf', NULL, NULL, NULL, 0, 0, 0),
(11, '1977.pdf', 'inri_articles_cat', 8, '', '1533299057_5.pdf', NULL, NULL, NULL, 0, 0, 0),
(12, '1978.pdf', 'inri_articles_cat', 8, '', '1533299057_6.pdf', NULL, NULL, NULL, 0, 0, 0),
(13, '1979.pdf', 'inri_articles_cat', 8, '', '1533299057_7.pdf', NULL, NULL, NULL, 0, 0, 0),
(14, '1980.pdf', 'inri_articles_cat', 8, '', '1533299057_8.pdf', NULL, NULL, NULL, 0, 0, 0),
(15, '1981.pdf', 'inri_articles_cat', 8, '', '1533299057_9.pdf', NULL, NULL, NULL, 0, 0, 0),
(16, '1982.pdf', 'inri_articles_cat', 8, '', '1533299057_10.pdf', NULL, NULL, NULL, 0, 0, 0),
(17, '1983.pdf', 'inri_articles_cat', 8, '', '1533299057_11.pdf', NULL, NULL, NULL, 0, 0, 0),
(18, '1984.pdf', 'inri_articles_cat', 8, '', '1533299057_12.pdf', NULL, NULL, NULL, 0, 0, 0),
(19, '1985.pdf', 'inri_articles_cat', 8, '', '1533299057_13.pdf', NULL, NULL, NULL, 0, 0, 0),
(20, '1986.pdf', 'inri_articles_cat', 8, '', '1533299057_14.pdf', NULL, NULL, NULL, 0, 0, 0),
(21, '1987.pdf', 'inri_articles_cat', 8, '', '1533299057_15.pdf', NULL, NULL, NULL, 0, 0, 0),
(22, '1988.pdf', 'inri_articles_cat', 8, '', '1533299057_16.pdf', NULL, NULL, NULL, 0, 0, 0),
(23, '1989.pdf', 'inri_articles_cat', 8, '', '1533299057_17.pdf', NULL, NULL, NULL, 0, 0, 0),
(24, '1990.pdf', 'inri_articles_cat', 8, '', '1533299057_18.pdf', NULL, NULL, NULL, 0, 0, 0),
(25, '1991.pdf', 'inri_articles_cat', 8, '', '1533299057_19.pdf', NULL, NULL, NULL, 0, 0, 0);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    $output .= $this->copy_file_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  function setup_module_news_corporate( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `fl_show_mine` tinyint(1) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `date`, `longtxt1`, `longtxt2`, `fl_show_mine`, `orm_search_name`, `orm_search`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `hide`, `ord`) VALUES  ";
    $sql_insert .=<<<HTML
(1, 'Петросян отреагировал на сообщения о разделе имущества со Степаненко', '1533303902.jpg', '2018-08-02', 'Отмечалось, что иск подала Степаненко. Петросян назвал эти новости &quot;недоразумением&quot;. Юморист, телеведущий и певец Владимир Винокур позднее заявил, что раздел имущества между Евгением Петросяном и Еленой Степаненко не должен становиться предметом интереса СМИ.', '<p>Юмористы <strong>Евгений Петросян</strong> и <strong>Елена Степаненко</strong> делят совместно нажитое имущество в&nbsp;Хамовническом суде, сообщает агентство городских новостей &laquo;<a href="https://www.mskagency.ru/materials/2805128" target="_blank">Москва</a>&raquo;.</p>\r\n\r\n<p class="image-in-text" itemprop="image" itemscope="" itemtype="https://schema.org/ImageObject"><img data-author="Фото: скриншот" src="/images/news/orig/1533303902.jpg" /><span class="title"></span><br />\r\n<span class="author">Фото: скриншот</span></p>\r\n\r\n<p>С&nbsp;иском в&nbsp;суд обратилась Степаненко, сообщили в&nbsp;пресс-службе суда. Заявление будет рассмотрено 6&nbsp;августа.</p>\r\n\r\n<p>Петросян заявил, что ему об&nbsp;этом ничего неизвестно,&nbsp;&mdash; по&nbsp;его словам, &laquo;это какое-то недоразумение&raquo;.</p>\r\n\r\n<p>Также народный артист отказался комментировать информацию о&nbsp;разводе со&nbsp;Степаненко. &laquo;Давайте без комментариев. Какая вам разница, что у&nbsp;меня происходит? Без комментариев&raquo;,&nbsp;&mdash; сказал Евгений Петросян.</p>\r\n\r\n<p>Официально юмористы о&nbsp;своем разводе не&nbsp;объявляли.</p>\r\n', 0, 'ПЕТРОСЯН ОТРЕАГИРОВАТЬ НА СООБЩЕНИЕ О РАЗДЕЛ ИМУЩЕСТВО СО СТЕПАНЕНКО', 'ЮМОРИСТ ЕВГЕНИЯ ПЕТРОСЯН И ЕЛЕНА СТЕПАНЕНКО ДЕЛИТЬ СОВМЕСТНО НАЖИТОЕ ИМУЩЕСТВО В NBSP ХАМОВНИЧЕСКИЙ СУД СООБЩАТЬ АГЕНТСТВО ГОРОДСКОЙ НОВОСТЬ LAQUO МОСКВА RAQUO ФОТО СКРИНШОТ С NBSP ИСКОМЫЙ В NBSP СУД ОБРАТИТЬСЯ СТЕПАНЕНКО СООБЩИТЬ В NBSP ПРЕСС-СЛУЖБА СУД ЗАЯВЛЕНИЕ БЫТЬ РАССМОТРЕТЬ 6 NBSP АВГУСТ ПЕТРОСЯН ЗАЯВИТЬ ЧТО ОН ОБ NBSP ЭТО НИЧЕГО НЕИЗВЕСТНЫЙ NBSP MDASH ПО NBSP ЕГО СЛОВО LAQUO ЭТО КАКОЙ-ТО НЕДОРАЗУМЕНИЕ RAQUO ТАКЖЕ НАРОДНЫЙ АРТИСТ ОТКАЗАТЬСЯ КОММЕНТИРОВАТЬ ИНФОРМАЦИЯ О NBSP РАЗВОД СО NBSP СТЕПАНЕНКО LAQUO ДАВАТЬ БЕЗ КОММЕНТАРИЙ КАКАТЬ ВЫ РАЗНИЦА ЧТО У NBSP МЕНЬ ПРОИСХОДИТЬ БЕЗ КОММЕНТАРИЙ RAQUO NBSP MDASH СКАЗАТЬ ЕВГЕНИЯ ПЕТРОСЯН ОФИЦИАЛЬНО ЮМОРИСТ О NBSP СВОЕ РАЗВОД НЕ NBSP ОБЪЯВЛЯТЬ', '', '', '', '', '', '', 0, 0),
(2, 'Ученые назвали причину исчезновения кораблей в Бермудском треугольнике', '1533304968.jpg', '2018-08-03', 'Ученые из&nbsp;Университета Саутгемптона проверили одну из&nbsp;наиболее вероятных версий исчезновения кораблей в&nbsp;Бермудском треугольнике&nbsp;&mdash; блуждающие волны. Об этом говорится в&nbsp;документальном фильме &quot;Загадка Бермудского треугольника&quot; на&nbsp;Channel 5, сообщает Daily Star.', '<div itemprop="articleBody">\r\n<p>Ученые из&nbsp;Университета Саутгемптона (Великобритания) проверили одну из&nbsp;наиболее вероятных версий исчезновения кораблей в&nbsp;Бермудском треугольнике &ndash; блуждающие волны. Об этом говорится в&nbsp;документальном фильме &quot;Загадка Бермудского треугольника&quot; на&nbsp;Channel 5, сообщает британская газета Daily Star.</p>\r\n\r\n<p>Район Атлантического океана, ограниченный треугольником, вершинами которого являются Флорида, Бермудские острова и&nbsp;Пуэрто-Рико, известен тем, что там, как&nbsp;считается, чаще, чем в&nbsp;других районах пропадают морские и&nbsp;воздушные суда.&nbsp;</p>\r\n\r\n<div id="">\r\n<div><img alt="Поселение на Бермудских островах" src="//corporate.ready.in-ri.ru/images_ckeditor/images/news/1479120401.jpg" style="float: left; margin: 5px 5px 5px 0px;" title="Поселение на Бермудских островах" /></div>\r\n\r\n<div>Биофизик: тайна Бермудского треугольника, возможно, близка к разгадке</div>\r\n</div>\r\n\r\n<p>Существуют множество версий загадочных исчезновений, однако основные научные: мощные выбросы метана, генерация волнами инфразвука, слишком быстрое течение и&nbsp;блуждающие волны. Последние представляют собой гигантские одиночные волны, внезапно возникающие в&nbsp;океане, высотой 20-30 метров.&nbsp;&nbsp;</p>\r\n\r\n<p><span style="font-size: 1rem;">Исследователи из&nbsp;Саутгемптона решили сосредоточиться именно на&nbsp;этой версии. Они смоделировали &quot;волну-убийцу&quot; на&nbsp;компьютере и&nbsp;направили к&nbsp;ней модель судна. Как и&nbsp;ожидалось, корабль &quot;затонул&quot; за&nbsp;считанные минуты.</span></p>\r\n\r\n<p>По мнению ученых, блуждающие волны могут объяснить исчезновение кораблей, но&nbsp;не самолетов. Поэтому говорить о&nbsp;полном раскрытии секрета загадочного треугольника пока рано.</p>\r\n\r\n<p>Ведущий научный сотрудник Института физики Земли им. О.Ю. Шмидта РАН Александр Жигалин в&nbsp;эфире радио Sputnik прокомментировал выводы британских ученых.&nbsp;</p>\r\n\r\n<p>&quot;Бермудский треугольник &ndash; это такая модная загадка, которую очень долго разгадывают, но&nbsp;так до&nbsp;конца и&nbsp;не ясно, что там происходит. Сначала одной из&nbsp;ведущих версий было то, что в&nbsp;этом районе часты густые туманы. Кстати, эта гипотеза объясняет исчезновения как&nbsp;кораблей, так и&nbsp;самолетов. А вот что касается огромных волн, то как&nbsp;раз этим данный район особенно не&nbsp;грешит: море там не&nbsp;очень глубокое, там много водорослей, и&nbsp;подняться таким волнам там очень непросто. Во всяком случае, до&nbsp;сих пор об&nbsp;этом ничего не&nbsp;было слышно. А вот &quot;метановая гипотеза&quot; с&nbsp;точки зрения физики, на&nbsp;мой взгляд, достаточно оправдана: насыщенная пузырьками газа вода обладает пониженной несущей способностью, и&nbsp;корабли при&nbsp;определенных условиях могут в&nbsp;такой воде потонуть. Что касается существования огромных &quot;волн-убийцы&quot;, то это можно считать установленным фактом &ndash; свидетельств неожиданного появления таких волн вполне достаточно. Но их появление именно в&nbsp;данном районе у&nbsp;меня вызывает сильные сомнения&quot;, &ndash; сказал Александр Жигалин.&nbsp;</p>\r\n\r\n<p>Он уверен, что компьютерное моделирование &ndash; это способ проверки гипотез, а&nbsp;строить на&nbsp;нем гипотезу нельзя.</p>\r\n\r\n<p>&quot;Я не&nbsp;очень доверяю моделированию. Мой многолетний опыт работы как&nbsp;геофизика показывает, что моделирование &ndash; это немножко лукавая вещь. Поскольку, грубо говоря, что в&nbsp;машину заложишь, то на&nbsp;выходе и&nbsp;получишь. Вот хочется нам получить волну в&nbsp;30 метров &ndash; так мы ее, скорее всего, и&nbsp;получим. Надо изучать любое явление в&nbsp;реальности, а&nbsp;не только в&nbsp;компьютере &ndash; только так мы получим ясный ответ на&nbsp;все вопросы. Моделирование &ndash; это способ проверки гипотез, а&nbsp;строить на&nbsp;нем гипотезу нельзя&quot;, &ndash; заключил Александр Жигалин.&nbsp;</p>\r\n</div>\r\n', 0, 'УЧЕНЫЙ НАЗВАТЬ ПРИЧИНА ИСЧЕЗНОВЕНИЕ КОРАБЛЬ В БЕРМУДСКИЙ ТРЕУГОЛЬНИК', 'УЧЕНЫЙ ИЗ NBSP УНИВЕРСИТЕТ САУТГЕМПТОН ВЕЛИКОБРИТАНИЯ ПРОВЕРИТЬ ОДИН ИЗ NBSP НАИБОЛЕЕ ВЕРОЯТНЫЙ ВЕРСИЯ ИСЧЕЗНОВЕНИЕ КОРАБЛЬ В NBSP БЕРМУДСКИЙ ТРЕУГОЛЬНИК NDASH БЛУЖДАТЬ ВОЛНА ОБ ЭТО ГОВОРИТЬСЯ В NBSP ДОКУМЕНТАЛЬНЫЙ ФИЛЬМ QUOT ЗАГАДКА БЕРМУДСКИЙ ТРЕУГОЛЬНИК QUOT НА NBSP CHANNEL 5 СООБЩАТЬ БРИТАНСКИЙ ГАЗЕТА DAILY STAR РАЙОН АТЛАНТИЧЕСКИЙ ОКЕАН ОГРАНИЧЕННЫЙ ТРЕУГОЛЬНИК ВЕРШИНА КОТОРЫЙ ЯВЛЯТЬСЯ ФЛОРИДА БЕРМУДСКИЙ ОСТРОВ И NBSP ПУЭРТО-РИКО ИЗВЕСТНЫЙ ТЕМ ЧТО ТАМ КАК NBSP СЧИТАТЬСЯ ЧАЩА ЧЕМ В NBSP ДРУГОЙ РАЙОН ПРОПАДАТЬ МОРСКОЙ И NBSP ВОЗДУШНЫЙ СУД NBSP БИОФИЗИК ТАЙНА БЕРМУДСКИЙ ТРЕУГОЛЬНИК ВОЗМОЖНО БЛИЗКИЙ К РАЗГАДКА СУЩЕСТВОВАТЬ МНОЖЕСТВО ВЕРСИЯ ЗАГАДОЧНЫЙ ИСЧЕЗНОВЕНИЕ ОДНАКО ОСНОВНЫЙ НАУЧНЫЙ МОЩНЫЙ ВЫБРОС МЕТАН ГЕНЕРАЦИЯ ВОЛНА ИНФРАЗВУК СЛИШКОМ БЫСТРЫЙ ТЕЧЕНИЕ И NBSP БЛУЖДАТЬ ВОЛНА ПОСЛЕДНИЙ ПРЕДСТАВЛЯТЬ СЕБЯ ГИГАНТСКИЙ ОДИНОЧНЫЙ ВОЛНА ВНЕЗАПНО ВОЗНИКАТЬ В NBSP ОКЕАН ВЫСОТА 20-30 МЕТР NBSP NBSP ИССЛЕДОВАТЕЛЬ ИЗ NBSP САУТГЕМПТОН РЕШИТЬ СОСРЕДОТОЧИТЬСЯ ИМЕННО НА NBSP ЭТОТ ВЕРСИЯ ОНИ СМОДЕЛИРОВАТЬ QUOT ВОЛНУ-УБИЙЦА QUOT НА NBSP КОМПЬЮТЕР И NBSP НАПРАВИТЬ К NBSP ОНА МОДЕЛЬ СУДНЫЙ КАК И NBSP ОЖИДАТЬСЯ КОРАБЛЬ QUOT ЗАТОНУТЬ QUOT ЗА NBSP СЧИТАТЬ МИНУТА ПО МНЕНИЕ УЧЕНЫЙ БЛУЖДАТЬ ВОЛНА МОЧЬ ОБЪЯСНИТЬ ИСЧЕЗНОВЕНИЕ КОРАБЛЬ НО NBSP НЕ САМОЛЁТ ПОЭТОМУ ГОВОРИТЬ О NBSP ПОЛНЫЙ РАСКРЫТИЕ СЕКРЕТ ЗАГАДОЧНЫЙ ТРЕУГОЛЬНИК ПОКА РАНО ВЕДУЩИЙ НАУЧНЫЙ СОТРУДНИК ИНСТИТУТ ФИЗИК ЗЕМЛЯ ИМЯ О Ю ШМИДТ РАНА АЛЕКСАНДР ЖИГАЛИНА В NBSP ЭФИР РАДИО SPUTNIK ПРОКОММЕНТИРОВАТЬ ВЫВОД БРИТАНСКИЙ УЧЕНЫЙ NBSP QUOT БЕРМУДСКИЙ ТРЕУГОЛЬНИК NDASH ЭТО ТАКАТЬ МОДНЫЙ ЗАГАДКА КОТОРЫЙ ОЧЕНЬ ДОЛГО РАЗГАДЫВАТЬ НО NBSP ТАК ДО NBSP КОНЕЦ И NBSP НЕ ЯСНО ЧТО ТАМ ПРОИСХОДИТЬ СНАЧАЛА ОДИН ИЗ NBSP ВЕДУЩИЙ ВЕРСИЯ БЫЛО ТО ЧТО В NBSP ЭТО РАЙОН ЧАСТЫЙ ГУСТОЙ ТУМАН КСТАТИ ЭТОТ ГИПОТЕЗА ОБЪЯСНЯТЬ ИСЧЕЗНОВЕНИЕ КАК NBSP КОРАБЛЬ ТАК И NBSP САМОЛЁТ А ВОТ ЧТО КАСАТЬСЯ ОГРОМНЫЙ ВОЛНА ТО КАК NBSP РАЗ ЭТО ДАННЫЙ РАЙОН ОСОБЕННО НЕ NBSP ГРЕШИТЬ МОР ТАМ НЕ NBSP ОЧЕНЬ ГЛУБОКИЙ ТАМ МНОГО ВОДОРОСЛЬ И NBSP ПОДНЯТЬСЯ ТАКОЙ ВОЛНА ТАМ ОЧЕНЬ НЕПРОСТО ВО ВСЯКИЙ СЛУЧАЙ ДО NBSP СЕЙ ПОРА ОБ NBSP ЭТО НИЧЕГО НЕ NBSP БЫЛО СЛЫШНО А ВОТ QUOT МЕТАНОВЫЙ ГИПОТЕЗА QUOT С NBSP ТОЧКА ЗРЕНИЕ ФИЗИК НА NBSP МЫТЬ ВЗГЛЯД ДОСТАТОЧНО ОПРАВДАТЬ НАСЫЩЕННЫЙ ПУЗЫРЁК ГАЗ ВОД ОБЛАДАТЬ ПОНИЖЕННЫЙ НЕСУЩИЙ СПОСОБНОСТЬ И NBSP КОРАБЛЬ ПРИ NBSP ОПРЕДЕЛЕННЫЙ УСЛОВИЕ МОЧЬ В NBSP ТАКОЙ ВОД ПОТОНУТЬ ЧТО КАСАТЬСЯ СУЩЕСТВОВАНИЕ ОГРОМНЫЙ QUOT ВОЛН-УБИЙЦА QUOT ТО ЭТО МОЖНО СЧИТАТЬ УСТАНОВЛЕННЫЙ ФАКТ NDASH СВИДЕТЕЛЬСТВО НЕОЖИДАННЫЙ ПОЯВЛЕНИЕ ТАКОЙ ВОЛНА ВПОЛНЕ ДОСТАТОЧНО НО ИХ ПОЯВЛЕНИЕ ИМЕННО В NBSP ДАННЫЙ РАЙОН У NBSP МЕНЬ ВЫЗЫВАТЬ СИЛЬНЫЙ СОМНЕНИЕ QUOT NDASH СКАЗАТЬ АЛЕКСАНДР ЖИГАЛИНА NBSP ОН УВЕРИТЬ ЧТО КОМПЬЮТЕРНЫЙ МОДЕЛИРОВАНИЕ NDASH ЭТО СПОСОБ ПРОВЕРКА ГИПОТЕЗА А NBSP СТРОИТЬ НА NBSP НЕМОЙ ГИПОТЕЗА НЕЛЬЗЯ QUOT Я НЕ NBSP ОЧЕНЬ ДОВЕРЯТЬ МОДЕЛИРОВАНИЕ МЫТЬ МНОГОЛЕТНИЙ ОПЫТ РАБОТА КАК NBSP ГЕОФИЗИК ПОКАЗЫВАТЬ ЧТО МОДЕЛИРОВАНИЕ NDASH ЭТО НЕМНОЖКО ЛУКАВЫЙ ВЕЩЬ ПОСКОЛЬКУ ГРУБО ГОВОРИТЬ ЧТО В NBSP МАШИН ЗАЛОЖИТЬ ТО НА NBSP ВЫХОД И NBSP ПОЛУЧИТЬ ВОТ ХОТЕТЬСЯ МЫ ПОЛУЧИТЬ ВОЛНА В NBSP 30 МЕТР NDASH ТАК МЫ ЕЕ СКОРЫЙ ВСЕГО И NBSP ПОЛУЧИТЬ НАДО ИЗУЧАТЬ ЛЮБОЙ ЯВЛЕНИЕ В NBSP РЕАЛЬНОСТЬ А NBSP НЕ ТОЛЬКО В NBSP КОМПЬЮТЕР NDASH ТОЛЬКО ТАК МЫ ПОЛУЧИТЬ ЯСНЫЙ ОТВЕТ НА NBSP ВСЕ ВОПРОС МОДЕЛИРОВАНИЕ NDASH ЭТО СПОСОБ ПРОВЕРКА ГИПОТЕЗА А NBSP СТРОИТЬ НА NBSP НЕМОЙ ГИПОТЕЗА НЕЛЬЗЯ QUOT NDASH ЗАКЛЮЧИТЬ АЛЕКСАНДР ЖИГАЛИНА NBSP', '', '', '', '', '', '', 0, 0),
(3, 'Марафон «Европа-Азия» соберёт более шести тысяч участников', '1533305394.jpg', '2018-08-01', 'Велосипедисты смогут проехать по дистанциям в 2, 14, 34 и 50 километров.', '<div>Продолжается офлайн-регистрация участников IV Международного легкоатлетического марафона &laquo;Европа &mdash; Азия&raquo;, который пройдет в Екатеринбурге в это воскресенье, 5 августа. На данный момент на одну из дистанций уже зарегистрировались более шести тысяч человек. Но организаторы ожидают еще больше участников, так как многие спортсмены регистрируются за день до марафона.<br />\r\n<br />\r\nУчастникам предлагают дистанции в 3, 10, 21 и 42 километра. Старт организуют на площади 1905 года. Через каждые пять километров марафонцев будет ждать пункт, где можно перекусить и освежиться.<br />\r\n<br />\r\nНапомним, что из-за марафона будут перекрываться улицы:<br />\r\n<br />\r\n&ndash; с 00:00 до 18:00 проспект Ленина (от переулка Банковского до улицы 8 Марта);<br />\r\n<br />\r\n&ndash; с 07:00 до 15:00 проспект Ленина (от Репина до переулка Банковского), Репина (от Металлургов до проспекта Ленина), четная сторона улицы Металлургов (от автодороги Пермь &mdash; Екатеринбург до Репина);<br />\r\n<br />\r\n&ndash; с 07:00 до 10:00 проспект Ленина (от 8 Марта до площади Кирова), площадь Кирова (от проспекта Ленина до улицы Мира), улица Мира (от Малышева до Первомайской), Толмачева (от проспекта Ленина до Царской), Царская (от Толмачева до Николая Никонова), Бориса Ельцина (от 8 Марта до Челюскинцев), 8 Марта (от проспекта Ленина до улицы Бориса Ельцина).<br />\r\n<br />\r\nТакже с 07:00 до 14:00 будет закрыто движение по дороге Пермь &mdash; Екатеринбург (с 343-го по 345-й километр, обратное направление), с 4 по 6 августа не будет работать парковка на площади 1905 года.<br />\r\n<br />\r\nКроме того, из-за забега схему движения <!--colorstart:#CC0000--><span style="color:#CC0000"><!--/colorstart--><u>изменят</u><!--colorend--></span><!--/colorend--> 17 трамвайных, девять троллейбусных маршрутов, 17 муниципальных и 29 коммерческих автобусов.</div>\r\n', 0, 'МАРАФОН ?ЕВРОПА-АЗИЯ ? СОБРАТЬ БОЛЕЕ ШЕСТЬ ТЫСЯЧА УЧАСТНИК', 'ПРОДОЛЖАТЬСЯ ОФЛАЙН-РЕГИСТРАЦИЯ УЧАСТНИК IV МЕЖДУНАРОДНЫЙ ЛЕГКОАТЛЕТИЧЕСКИЙ МАРАФОН LAQUO ЕВРОПА MDASH АЗИЯ RAQUO КОТОРЫЙ ПРОЙДЕТ В ЕКАТЕРИНБУРГ В ЭТО ВОСКРЕСЕНИЕ 5 АВГУСТ НА ДАННЫЙ МОМЕНТ НА ОДИН ИЗ ДИСТАНЦИЯ УЖЕ ЗАРЕГИСТРИРОВАТЬСЯ БОЛЕЕ ШЕСТЬ ТЫСЯЧА ЧЕЛОВЕК НО ОРГАНИЗАТОР ОЖИДАТЬ ЕЩИЙ БОЛЬШИЙ УЧАСТНИК ТАК КАК МНОГИЙ СПОРТСМЕН РЕГИСТРИРОВАТЬСЯ ЗА ДЕТЬ ДО МАРАФОН УЧАСТНИК ПРЕДЛАГАТЬ ДИСТАНЦИЯ В 3 10 21 И 42 КИЛОМЕТР СТАРТ ОРГАНИЗОВАТЬ НА ПЛОЩАДЬ 1905 ГОД ЧЕРЕЗ КАЖДЫЙ ПЯТЬ КИЛОМЕТРОВЫЙ МАРАФОНЕЦ БЫТЬ ЖДАТЬ ПУНКТ ГДЕ МОЖНО ПЕРЕКУСИТЬ И ОСВЕЖИТЬСЯ НАПОМНИТЬ ЧТО ИЗ-ЗА МАРАФОН БЫТЬ ПЕРЕКРЫВАТЬСЯ УЛИЦА NDASH С 00 00 ДО 18 00 ПРОСПЕКТ ЛЕНИН ОТ ПЕРЕУЛОК БАНКОВСКИЙ ДО УЛИЦА 8 МАРТ NDASH С 07 00 ДО 15 00 ПРОСПЕКТ ЛЕНИН ОТ РЕПИН ДО ПЕРЕУЛОК БАНКОВСКИЙ РЕПИН ОТ МЕТАЛЛУРГ ДО ПРОСПЕКТ ЛЕНИН ЧЕТНЫЙ СТОРОНА УЛИЦА МЕТАЛЛУРГ ОТ АВТОДОРОГА ПЕРМЬ MDASH ЕКАТЕРИНБУРГ ДО РЕПИН NDASH С 07 00 ДО 10 00 ПРОСПЕКТ ЛЕНИН ОТ 8 МАРТ ДО ПЛОЩАДЬ КИРОВ ПЛОЩАДЬ КИРОВ ОТ ПРОСПЕКТ ЛЕНИН ДО УЛИЦА МИР УЛИЦА МИР ОТ МАЛЫШЕВ ДО ПЕРВОМАЙСКИЙ ТОЛМАЧЕВ ОТ ПРОСПЕКТ ЛЕНИН ДО ЦАРСКИЙ ЦАРСКИЙ ОТ ТОЛМАЧЕВ ДО НИКОЛАЙ НИКОНОВ БОРИС ЕЛЬЦИН ОТ 8 МАРТ ДО ЧЕЛЮСКИНЕЦ 8 МАРТ ОТ ПРОСПЕКТ ЛЕНИН ДО УЛИЦА БОРИС ЕЛЬЦИН ТАКЖЕ С 07 00 ДО 14 00 БЫТЬ ЗАКРЫТЫЙ ДВИЖЕНИЕ ПО ДОРОГА ПЕРМЬ MDASH ЕКАТЕРИНБУРГ С 343-ГО ПО 345-Й КИЛОМЕТР ОБРАТНЫЙ НАПРАВЛЕНИЕ С 4 ПО 6 АВГУСТ НЕ БЫТЬ РАБОТАТЬ ПАРКОВКА НА ПЛОЩАДЬ 1905 ГОД КРОМЕ ТОГО ИЗ-ЗА ЗАБЕГ СХЕМА ДВИЖЕНИЕ ИЗМЕНИТЬ 17 ТРАМВАЙНЫЙ ДЕВЯТЬ ТРОЛЛЕЙБУСНЫЙ МАРШРУТ 17 МУНИЦИПАЛЬНЫЙ И 29 КОММЕРЧЕСКИЙ АВТОБУС', '', '', '', '', '', '', 0, 0);
HTML;
    $output = '';
    $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output; 
  }
  
  function setup_module_articles_corporate( $title, $name ){
    
    $table       = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $title_cat   = $title.' категории';
    $table_cat   = DB_PFX.$name.'_cat';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cat_id` int(11) DEFAULT '0',
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; "; 
    $sql_insert = "
      INSERT INTO `$table` (`id`, `cat_id`, `title`, `img`, `date`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `hide`, `ord`) VALUES  ";
    {
    $sql_insert .=<<<HTML
(1, 5, 'Основатель диджитал-студии Finch — о веб-дизайне в России и США', '1552333133.png', '', '<p>Программист Дмитрий Щипачев руководит агентством Finch, среди проектов которого &mdash; сайт &laquo;Спартака&raquo; и приложение для ТНТ-Club. Что происходит с веб-пространством и как его меняет мир мобильных приложений.</p>\r\n', '<div class="l-col-center__inner">\r\n<div class="article__overview ">\r\n<div class="article__rubric">&nbsp;</div>\r\n\r\n<div class="article__main-image">\r\n<div class="article__main-image__inner"><img alt="" class="js-rbcslider-image" itemprop="image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412030895.png" />\r\n<div class="article__main-image__copyrights">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__social js-social-likes">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="Основатель диджитал-студии Finch — о веб-дизайне в России и США">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__info">\r\n<div class="article__author">Автор <span class="article__author__name"> <!--\r\n                    --><!--\r\n                        -->Елена Фомина<!--\r\n                        --><!--\r\n                    --><!--\r\n                --> </span></div>\r\n\r\n<div class="article__date" content="2018-12-19T10:42:24+03:00" itemprop="datePublished">19 декабря 2018</div>\r\n\r\n<div class="article__date" content="2018-12-19T10:42:24+03:00" itemprop="datePublished">&nbsp;</div>\r\n<meta itemprop="dateModified" content="2018-12-19T10:42:24+03:00"></div>\r\n\r\n<div class="article__subtitle">Программист Дмитрий Щипачев руководит агентством Finch, среди проектов которого &mdash; сайт &laquo;Спартака&raquo; и приложение для ТНТ-Club. &laquo;РБК Стиль&raquo; узнал у него, что происходит с веб-пространством и как его меняет мир мобильных приложений.</div>\r\n</div>\r\n\r\n<div class="article__text" itemprop="articleBody">\r\n<p>Прихожая офиса Finch встречает горой ботинок, ворохом пуховиков и мотоциклом BMW. Над гардеробной-гаражом ютится небольшой оупенспейс и директорский кабинет. В нем&nbsp;&mdash; уютный полумрак. Так легче работается. Дмитрий Щипачев руководит студией разработки почти 12 лет, все успевает благодаря мотоциклу&nbsp;и мыслит стратегически. Его студия Finch сконструировала и запустила сайты для &laquo;Дома-2&raquo;, &laquo;Столото&raquo; и футбольного клуба &laquo;Спартак&raquo;, параллельно переключаясь на приложения для смартфонов. С Дмитрием мы поговорили о том, как устроен диджитал-бизнес в России и чем он отличается от западного.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О приложениях и сайтах</span></p>\r\n\r\n<p><strong>Дизайн приложений &mdash; это то, чем хочется заниматься, в отличие от сайтов.</strong> Потому что вся эстетика мобильных устройств располагает к тому, чтобы создавать красивые приложения. Плюс нам комфортнее работать с тем характером пользовательского потребления, которым приложения отличаются от веба.</p>\r\n\r\n<p><strong>Дополненная и виртуальная реальность дают только вау-фактор. </strong>Единственная область, в которой он применим, &mdash; это реклама.</p>\r\n\r\n<p><strong>Рекламой заниматься мы не любим.&nbsp;</strong>Никакой социально полезной функции в этом нет. Интереснее решить даже самую простую задачу, с которой пользователь сталкивается ежедневно, чем создать самый успешный рекламный кейс.</p>\r\n\r\n<p><strong>Ни один продукт не бесплатный.</strong> С тебя все равно получат деньги. Встроенная ли это покупка, или это подписка, или пользовательские данные, или реклама. Или все вместе. Ты все равно платишь за то, чем пользуешься в интернете. Все мы помним, как появился Gmail с бесплатными 10 Гб на диске, когда Mail.Ru давал только 200 Мб. И все сразу полезли в Google. Сейчас мы понимаем, что все это было не просто так. Google знал уже тогда, что пользовательские данные будут самым ценным товаром, видел будущее.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412723069.jpg" style="width: 800px; height: 1199px;" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>О понятии статусности по отношению к сайтам сложно говорить.&nbsp;​</strong>Мы измеряем любую вещь неопределенным количеством параметров. И мы не знаем, какие параметры для какого кейса рассматривались как приоритетные.&nbsp;Например, наш сайт для &laquo;Спартака&raquo; не слишком отличался посещаемостью, но для них этот проект создавался с целью привести их нынешнее IT-окружение в соответствие со статусом бренда. Раньше бросалось в глаза несоответствие между величиной и популярностью клуба и его отражением в интернете.</p>\r\n\r\n<p><strong>Сайты, как и приложения, должны со временем упрощаться в плане разнообразия дизайна.</strong> С одной стороны, гайдлайны операционных систем становятся все более продуманными, а сами приложения становятся все менее разнообразными, стремясь соответствовать этим гайдлайнам. И это очень правильно.</p>\r\n\r\n<p><strong>С сайтами произошло бы то же самое, что и с приложениями, если бы были единые технологии оформления.&nbsp;</strong>Но таких технологий нет и никогда не будет, потому что все, что с ними связано &mdash; это языки, HTML-стандарты, &mdash; управляется консорциумами, которые никогда не договорятся об унификации.</p>\r\n\r\n<p><strong>HTML должен умереть, он как каменное колесо для индустрии.</strong> И веб-дизайн тоже должен исчезнуть. Я могу легко представить ситуацию, когда через два-три года внешний вид сайта не будет программироваться создателем. Разработчик будет только компоновать составляющие, а операционная система отобразит конечный результат. Уже сейчас есть тому примеры: InstantView в Facebook, сервис Telegra.ph &mdash; в Telegram.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О российском диджитал-сегменте</span></p>\r\n\r\n<p><strong>Российский потребитель диджитала очень сильно избалован</strong>. Не только в плане дизайна. У нас и скорость интернета, и его доступность, и навыки дизайнеров и программистов выше, чем в Европе или США. По части веб-дизайна мы более мобильны. Если взять дизайн сайта газет Boston Globe или Wall Street Journal, можно заметить, что за Атлантикой дизайнеры и программисты меняются очень медленно. За 20 лет максимум они чуть-чуть изменят шрифты и внедрят адаптив для смартфонов.</p>\r\n\r\n<p><strong>Я работал в Америке в веб-дизайне и заметил, что делают они все очень качественно. </strong>Например, для интро сайта они могут арендовать целую площадку и три дня снимать фильм, чтобы потом превратить его в мультфильм. У нас бы сел моушен-дизайнер и за два часа нарисовал силуэты. То есть они подходят с точки зрения качества, но очень консервативно.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__textextract">\r\n<div class="article__textextract__text">\r\n<p>HTML должен умереть, он как каменное колесо для индустрии.</p>\r\n</div>\r\n\r\n<div class="article__textextract__social">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="\r\nHTML должен умереть, он как каменное колесо для индустрии.\r\n">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>У нас люди любят всякие красивые вещи. </strong>Для нас первична форма, а не содержание. Поэтому наш дизайн &laquo;лучше&raquo; смотрится. Ну и мы больше нацелены на вау-фактор. Американца ты спросишь, почему сайт New York Times такой неудобный, и он просто не поймет тебя: вот новости, вот текст, все работает. Что еще нужно? С другой стороны, на Западе, стараниями того же Apple, чувство стиля в диджитале выращивалось годами. Даже Android из соображений конкуренции начал подгонять свои гайдлайны, чтобы сделать красивый дизайн оболочки операционной системы и приложений. Потому что разница становилась со временем слишком очевидной.</p>\r\n\r\n<p><strong>Большое внимание дизайну уделяют те, кто оперирует крупными платформами.</strong> И они тянут всех остальных за собой.&nbsp;В России таких гигантов нет, зато есть разработчики-энтузиасты, которые где-то что-то подсматривают и стараются переносить к нам в красивом виде.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О проектах Finch</span></p>\r\n\r\n<p><strong>Комплекс проектов для &laquo;Спартака&raquo; получился очень красивый.</strong> При запуске мы смогли защитить визуальную составляющую от разных опасностей, правда, она продержалась недолго. Это естественный процесс развития любого продукта &mdash; когда ты только что-то запускаешь, оно выглядит цельным. Но чем дальше от запуска, тем быстрее продукт разлагается и разрушается.</p>\r\n\r\n<p><strong>Где-то полгода, и ты отпускаешь хватку, сползаешь и говоришь: все, окей, пусть будет что будет.</strong> И больше не следишь за консистентностью продукта (то есть за стабильной работой сайта. <em>&mdash; &laquo;РБК Стиль&raquo;</em>). Тут вопрос чисто финансовой мотивации.</p>\r\n\r\n<p><strong>Поддерживать качество быстро растущего продукта &mdash; очень трудоемкая работа, результат которой не всегда понятен для клиента.</strong> У него, допустим, десять партнеров, каждый из которых требует запустить свои функции.</p>\r\n\r\n<p><strong>Тяжело следить за тем, чтобы и работа делалась, и цельность сохранялась.</strong> Это ресурсоемкий процесс, и за него нужно доплачивать. И все равно в конце концов наступает момент, когда необходимо сделать полный редизайн и придумать все с нуля.&nbsp;Это происходит и из-за процессов внутри продукта, и из-за того, что внешняя среда тоже меняется: каждый год обновляются компоненты операционных систем, приложения, меняется конкурентное окружение, в целом развивается дизайн, возможности и так далее.</p>\r\n\r\n<p><strong>После скачка проекта на следующий уровень снова начнется процесс разложения. </strong>Для долгоживущих проектов это нормально. И хороший клиент это понимает.&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412723278.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О стереотипных программистах</span></p>\r\n\r\n<p><strong>Клиенты возрастом где-то за 45</strong> <strong>ожидают увидеть таких гиков из фильмов в рваных джинсах, свитере, говорящих непонятные слова.</strong> Если ты попадаешь в этот образ, то у них что-то щелкает и они тебе сразу доверяют.&nbsp; Если приедешь в пиджаке-галстуке, то они могут не понять, подумать, что ты такой же, как они, а они ничего не умеют в диджитале.</p>\r\n\r\n<p><strong>Клиенты моложе начинают условно соревноваться</strong> <strong>с тобой, кто моднее.</strong> Кто больше продуктолог, кто больше кастдев (от customer developer, тестировщик прототипа на потенциальных потребителях. &mdash; &laquo;РБК Стиль&raquo;) и тому подобное. Но это просто наблюдение, не закономерность.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size: 26px;">Об отношении к жизни</span></p>\r\n\r\n<p><strong>Чтобы мыслить на 10 лет вперед,</strong> <strong>начать нужно с того, чтобы не мыслить на 10 лет назад. </strong>Просто поменьше думать о том, что было раньше. В отношении себя и того, что ты делаешь. Как только появляется новый проект, все, что ты делал на предыдущем, &mdash; уже история, которую нужно забыть.</p>\r\n\r\n<p><strong>Силы появляются от деятельности</strong>. Если пытаться их экономить или копить &mdash; их станет только меньше.</p>\r\n\r\n<p><strong>Я даже стараюсь скрывать образование историка, потому что обычно начинаются вопросы &laquo;О, а как ты думаешь о том и об этом?&raquo;.</strong> Особенно когда с таксистами разговариваешь &mdash; они очень любят докапываться, если узнают, что у тебя историческое образование. Так что хобби никак не связано с ним.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412722931.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>Эмпатия отдельно &mdash; работа отдельно. </strong>Комфортные условия обеспечиваются не отношениями с клиентом, а принятым регламентом работы. Все, что сверх этого&nbsp;&mdash; симпатия, иногда даже дружба, &mdash; идет как бонус, нечестное конкурентное преимущество. Оно, конечно, очень полезно и помогает в работе, но не является обязательным условием.</p>\r\n\r\n<p><strong>Музыка мне нравилась всегда, она выступала мотивацией, в том числе и в работе.</strong> Плюс все вещи, связанные с оформлением концертных постеров, сайтов музыкальных групп, &mdash; визуально красивые. Это меня привлекает. Я долгое время занимаюсь музыкой, у меня куча инструментов. Правда, посвятить себя музыке полностью сейчас не получается.</p>\r\n\r\n<div class="article__textextract">\r\n<div class="article__textextract__text">\r\n<p>Чтобы мыслить на 10 лет вперед, начать нужно с того, чтобы не мыслить на 10 лет назад.</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\r\n\r\n<div class="article__textextract__social">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="\r\nЧтобы мыслить на 10 лет вперед, начать нужно с того, чтобы не мыслить на 10 лет назад.\r\n\r\n\r\n">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p><strong>Я окончил музыкальную школу</strong> <strong>по классу виолончели, потом занимался саксофоном.</strong> С тех пор играл на гитаре, барабанах.&nbsp;Барабаны даются сложнее всего. У тебя либо есть навык удержания четкого&nbsp;ритма, либо нет. У меня нет. Поэтому с барабанами не особенно. Но зато с клавишными все хорошо.</p>\r\n\r\n<p><strong>Когда ты семь лет имеешь дело с одним инструментом&nbsp;(с детского возраста), занятие начинает дико надоедать.</strong> Музыкальная школа у меня с пяти лет началась. Смысл в том, что каждый инструмент чем-то привлекает в разное время, и когда берешься за новый &mdash; кажется, что именно он идеальный. Точно так же и с проектами обстоят дела.</p>\r\n\r\n<p><strong>У меня в кабинете висит постер концерта Sly &amp; the Family Stone 1978 года в Сан-Франциско.</strong> Таскаю его с собой уже лет 10, ни одна вещь меня так не вдохновляла, как он.</p>\r\n\r\n<p><strong>Основная деятельность позволяет заниматься только утилитарными хобби &mdash; одно связано с тем, чтобы не умереть. </strong>Это бокс. А другое &mdash; чтобы побыстрее все успевать. Это мотоспорт.</p>\r\n\r\n<p><strong>Обычный человек без мотоцикла на встречу не&nbsp;успеет, а я успеваю. </strong>С присутствием мотоцикла в жизни для меня нормально, если встречу назначают где-то на Волгоградском шоссе в шесть вечера.&nbsp;</p>\r\n\r\n<p><strong>Я нахожусь в такой стадии просветления, когда уже не разделяешь, какой сайт красивый, а какой страшный. </strong>Но у меня есть свое понимание того, как должно быть хорошо.&nbsp;<span class="article__logo article__logo_no-left-margin"></span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\r\n</div>\r\n', '', '', '', '', '', '', 'ОСНОВАТЕЛЬ ДИДЖИТАЛ-СТУДИЯ FINCH ? ?? О ?ВЕБ-ДИЗАЙН В ?РОССИЯ И ?США', 'NBSP NBSP АВТОР ЕЛЕНА ФОМИН 19 ДЕКАБРЬ 2018 NBSP ПРОГРАММИСТ ДМИТРИЯ ЩИПАЧЕВ РУКОВОДИТЬ АГЕНТСТВО FINCH СРЕДИ ПРОЕКТ КОТОРЫЙ MDASH САЙТ LAQUO СПАРТАК RAQUO И ПРИЛОЖЕНИЕ ДЛЯ ТНТ-CLUB LAQUO РБК СТИЛЬ RAQUO УЗНАТЬ У ОН ЧТО ПРОИСХОДИТЬ С ВЕБ-ПРОСТРАНСТВО И КАК ЕГО МЕНЯТЬ МИР МОБИЛЬНЫЙ ПРИЛОЖЕНИЕ ПРИХОЖАЯ ОФИС FINCH ВСТРЕЧАТЬ ГОРА БОТИНОК ВОРОХ ПУХОВИК И МОТОЦИКЛ BMW НАД ГАРДЕРОБНОЙ-ГАРАЖ ЮТИТЬСЯ НЕБОЛЬШОЙ ОУПЕНСПЕЙС И ДИРЕКТОРСКИЙ КАБИНЕТ В НЕМОЙ NBSP MDASH УЮТНЫЙ ПОЛУМРАК ТАК ЛЕГЧИЙ РАБОТАТЬСЯ ДМИТРИЯ ЩИПАЧЕВ РУКОВОДИТЬ СТУДИЯ РАЗРАБОТКА ПОЧТИ 12 ГОД ВСЕ УСПЕВАТЬ БЛАГОДАРЯ МОТОЦИКЛ NBSP И МЫСЛИТЬ СТРАТЕГИЧЕСКИЙ ЕГО СТУДИЯ FINCH СКОНСТРУИРОВАТЬ И ЗАПУСТИТЬ САЙТ ДЛЯ LAQUO ДОМА-2 RAQUO LAQUO СТОЛОТЫЙ RAQUO И ФУТБОЛЬНЫЙ КЛУБ LAQUO СПАРТАК RAQUO ПАРАЛЛЕЛЬНО ПЕРЕКЛЮЧАТЬСЯ НА ПРИЛОЖЕНИЕ ДЛЯ СМАРТФОН С ДМИТРИЙ МЫ ПОГОВОРИТЬ О ТОМ КАК УСТРОЕННЫЙ ДИДЖИТАЛ-БИЗНЕС В РОССИЯ И ЧЕМ ОН ОТЛИЧАТЬСЯ ОТ ЗАПАДНЫЙ NBSP О ПРИЛОЖЕНИЕ И САЙТ ДИЗАЙН ПРИЛОЖЕНИЕ MDASH ЭТО ТО ЧЕМ ХОТЕТЬСЯ ЗАНИМАТЬСЯ В ОТЛИЧИЕ ОТ САЙТ ПОТОМУ ЧТО ВЕСЬ ЭСТЕТИК МОБИЛЬНЫЙ УСТРОЙСТВО РАСПОЛАГАТЬ К ТОМ ЧТОБЫ СОЗДАВАТЬ КРАСИВЫЙ ПРИЛОЖЕНИЕ ПЛЮС МЫ КОМФОРТНЫЙ РАБОТАТЬ С ТЕМ ХАРАКТЕР ПОЛЬЗОВАТЕЛЬСКИЙ ПОТРЕБЛЕНИЕ КОТОРЫЙ ПРИЛОЖЕНИЕ ОТЛИЧАТЬСЯ ОТ ВЕБ ДОПОЛНИТЬ И ВИРТУАЛЬНЫЙ РЕАЛЬНОСТЬ ДАВАТЬ ТОЛЬКО ВАУ-ФАКТОР ЕДИНСТВЕННЫЙ ОБЛАСТЬ В КОТОРЫЙ ОН ПРИМЕНИМЫЙ MDASH ЭТО РЕКЛАМА РЕКЛАМА ЗАНИМАТЬСЯ МЫ НЕ ЛЮБИМЫЙ NBSP НИКАКОЙ СОЦИАЛЬНО ПОЛЕЗНЫЙ ФУНКЦИЯ В ЭТО НЕТ ИНТЕРЕСНЫЙ РЕШИТЬ ДАЖЕ САМЫЙ ПРОСТОЙ ЗАДАЧА С КОТОРЫЙ ПОЛЬЗОВАТЕЛЬ СТАЛКИВАТЬСЯ ЕЖЕДНЕВНО ЧЕМ СОЗДАТЬ САМЫЙ УСПЕШНЫЙ РЕКЛАМНЫЙ КЕЙС НИ ОДИН ПРОДУКТ НЕ БЕСПЛАТНЫЙ С ТЫ ВСЕ РАВНО ПОЛУЧИТЬ ДЕНЬГА ВСТРОИТЬ ЛИ ЭТО ПОКУПКА ИЛИ ЭТО ПОДПИСКА ИЛИ ПОЛЬЗОВАТЕЛЬСКИЙ ДАННЫЙ ИЛИ РЕКЛАМА ИЛИ ВСЕ ВМЕСТЕ ТЫ ВСЕ РАВНО ПЛАТИТЬ ЗА ТО ЧЕМ ПОЛЬЗОВАТЬСЯ В ИНТЕРНЕТ ВСЕ МЫ ПОМНИТЬ КАК ПОЯВИТЬСЯ GMAIL С БЕСПЛАТНЫЙ 10 ГБ НА ДИСК КОГДА MAIL RU ДАВАТЬ ТОЛЬКО 200 МБ И ВСЕ СРАЗУ ПОЛЕЗТЬ В GOOGLE СЕЙЧАС МЫ ПОНИМАТЬ ЧТО ВСЕ ЭТО БЫЛО НЕ ПРОСТО ТАК GOOGLE ЗНАТЬ УЖЕ ТОГДА ЧТО ПОЛЬЗОВАТЕЛЬСКИЙ ДАННЫЙ БЫТЬ САМЫЙ ЦЕННЫЙ ТОВАР ВИДЕТЬ БУДУЩИЙ NBSP NBSP NBSP NBSP О ПОНЯТИЕ СТАТУСНОСТЬ ПО ОТНОШЕНИЕ К САЙТ СЛОЖНО ГОВОРИТЬ NBSP ??М ИЗМЕРЯТЬ ЛЮБОЙ ВЕЩЬ НЕОПРЕДЕЛЕННЫЙ КОЛИЧЕСТВО ПАРАМЕТР И МЫ НЕ ЗНАТЬ КАКОЙ ПАРАМЕТР ДЛЯ КАКОЙ КЕЙС РАССМАТРИВАТЬСЯ КАК ПРИОРИТЕТНЫЙ NBSP НАПРИМЕР НАШ САЙТ ДЛЯ LAQUO СПАРТАК RAQUO НЕ СЛИШКОМ ОТЛИЧАТЬСЯ ПОСЕЩАЕМОСТЬ НО ДЛЯ ОНИ ЭТОТ ПРОЕКТ СОЗДАВАТЬСЯ С ЦЕЛЬ ПРИВЕСТИ ИХ НЫНЕШНИЙ IT-ОКРУЖЕНИЕ В СООТВЕТСТВИЕ СО СТАТУС БРЕНД РАННИЙ БРОСАТЬСЯ В ГЛАЗ НЕСООТВЕТСТВИЕ МЕЖДУ ВЕЛИЧИНА И ПОПУЛЯРНОСТЬ КЛУБ И ЕГО ОТРАЖЕНИЕ В ИНТЕРНЕТ САЙТ КАК И ПРИЛОЖЕНИЕ ДОЛЖНЫЙ СО ВРЕМЯ УПРОЩАТЬСЯ В ПЛАН РАЗНООБРАЗИЕ ДИЗАЙН С ОДИН СТОРОНА ГАЙДЛАЙНА ОПЕРАЦИОННЫЙ СИСТЕМА СТАНОВИТЬСЯ ВСЕ БОЛЕЕ ПРОДУМАТЬ А САМ ПРИЛОЖЕНИЕ СТАНОВИТЬСЯ ВСЕ МЕНЕЕ РАЗНООБРАЗНЫЙ СТРЕМИТЬСЯ СООТВЕТСТВОВАТЬ ЭТО ГАЙДЛАЙНА И ЭТО ОЧЕНЬ ПРАВИЛЬНО С САЙТ ПРОИЗОЙТИ БЫ ТО ЖЕ САМЫЙ ЧТО И С ПРИЛОЖЕНИЕ ЕСЛИ БЫ БЫЛЬ ЕДИНЫЙ ТЕХНОЛОГИЯ ОФОРМЛЕНИЕ NBSP НО ТАКОЙ ТЕХНОЛОГИЯ НЕТ И НИКОГДА НЕ БЫТЬ ПОТОМУ ЧТО ВСЕ ЧТО С ОНИ СВЯЗАТЬ MDASH ЭТО ЯЗЫК HTML-СТАНДАРТ MDASH УПРАВЛЯТЬСЯ КОНСОРЦИУМ КОТОРЫЙ НИКОГДА НЕ ДОГОВОРИТЬСЯ ОБ УНИФИКАЦИЯ HTML ДОЛЖНЫЙ УМЕРЕТЬ ОН КАК КАМЕННЫЙ КОЛЕСО ДЛЯ ИНДУСТРИЯ И ВЕБ-ДИЗАЙН ТОЖЕ ДОЛЖНЫЙ ИСЧЕЗНУТЬ Я МОЧЬ ЛЕГКО ПРЕДСТАВИТЬ СИТУАЦИЯ КОГДА ЧЕРЕЗ ДВА-ТРЕТЬ ГОД ВНЕШНИЙ ВИД САЙТ НЕ БЫТЬ ПРОГРАММИРОВАТЬСЯ СОЗДАТЕЛЬ РАЗРАБОТЧИК БЫТЬ ТОЛЬКО КОМПОНОВАТЬ СОСТАВЛЯТЬ А ОПЕРАЦИОННЫЙ СИСТЕМА ОТОБРАЗИТЬ КОНЕЧНЫЙ РЕЗУЛЬТАТ УЖЕ СЕЙЧАС ЕСТЬ ТОМ ПРИМЕР INSTANTVIEW В FACEBOOK СЕРВИС TELEGRA PH MDASH В TELEGRAM NBSP О РОССИЙСКИЙ ДИДЖИТАЛ-СЕГМЕНТ РОССИЙСКИЙ ПОТРЕБИТЕЛЬ ДИДЖИТАТЬ ОЧЕНЬ СИЛЬНО ИЗБАЛОВАТЬ НЕ ТОЛЬКО В ПЛАН ДИЗАЙН У МЫ И СКОРОСТЬ ИНТЕРНЕТ И ЕГО ДОСТУПНОСТЬ И НАВЫК ДИЗАЙНЕР И ПРОГРАММИСТ ВЫШЕ ЧЕМ В ЕВРОПА ИЛИ США ПО ЧАСТИТЬ ВЕБ-ДИЗАЙН МЫ БОЛЕЕ МОБИЛЬНЫЙ ЕСЛИ ВЗЯТЬ ДИЗАЙН САЙТ ГАЗЕТА BOSTON GLOBE ИЛИ WALL STREET JOURNAL МОЖНО ЗАМЕТИТЬ ЧТО ЗА АТЛАНТИКА ДИЗАЙНЕР И ПРОГРАММИСТ МЕНЯТЬСЯ ОЧЕНЬ МЕДЛЕННО ЗА 20 ГОД МАКСИМУМ ОНИ ЧУТЬ-ЧУТЬ ИЗМЕНИТЬ ШРИФТ И ВНЕДРИТЬ АДАПТИТЬ ДЛЯ СМАРТФОН Я РАБОТАТЬ В АМЕРИКА В ВЕБ-ДИЗАЙН И ЗАМЕТИТЬ ЧТО ДЕЛАТЬ ОНИ ВСЕ ОЧЕНЬ КАЧЕСТВЕННО НАПРИМЕР ДЛЯ ИНТРО САЙТ ОНИ МОЧЬ АРЕНДОВАТЬ ЦЕЛОВАТЬ ПЛОЩАДКА И ТЕРЕТЬ ДЕНЬ СНИМАТЬ ФИЛЬМ ЧТОБЫ ПОТОМ ПРЕВРАТИТЬ ЕГО В МУЛЬТФИЛЬМ У МЫ БЫ СЕСТЬ МОУШЕН-ДИЗАЙНЕР И ЗА ДВА ЧАС НАРИСОВАТЬ СИЛУЭТ ТО ЕСТЬ ОНИ ПОДХОДИТЬ С ТОЧКА ЗРЕНИЕ КАЧЕСТВО НО ОЧЕНЬ КОНСЕРВАТИВНЫЙ NBSP HTML ДОЛЖНЫЙ УМЕРЕТЬ ОН КАК КАМЕННЫЙ КОЛЕСО ДЛЯ ИНДУСТРИЯ NBSP У МЫ ЧЕЛОВЕК ЛЮБИТЬ ВСЯКИЙ КРАСИВЫЙ ВЕЩИЙ ДЛЯ МЫ ПЕРВИЧНЫЙ ФОРМА А НЕ СОДЕРЖАНИЕ ПОЭТОМУ НАШ ДИЗАЙН LAQUO ЛУЧШЕ RAQUO СМОТРЕТЬСЯ НУ И МЫ БОЛЬШИЙ НАЦЕЛИТЬ НА ВАУ-ФАКТОР АМЕРИКАНЕЦ ТЫ СПРОСИТЬ ПОЧЕМУ САЙТ NEW YORK TIMES ТАКОЙ НЕУДОБНЫЙ И ОН ПРОСТО НЕ ПОЙМЕТ ТЫ ВОТ НОВОСТЬ ВОТ ТЕКСТ ВСЕ РАБОТАТЬ ЧТО ЕЩИЙ НУЖНЫЙ С ДРУГОЙ СТОРОНА НА ЗАПАД СТАРАНИЕ ТОГО ЖЕ APPLE ЧУВСТВО СТИЛЬ В ДИДЖИТАЛ ВЫРАЩИВАТЬСЯ ГОДАМИ ДАЖЕ ANDROID ИЗ СООБРАЖЕНИЕ КОНКУРЕНЦИЯ НАЧАЛО ПОДГОНЯТЬ СВОЕ ГАЙДЛАЙНА ЧТОБЫ СДЕЛАТЬ КРАСИВЫЙ ДИЗАЙН ОБОЛОЧКА ОПЕРАЦИОННЫЙ СИСТЕМА И ПРИЛОЖЕНИЕ ПОТОМУ ЧТО РАЗНИЦА СТАНОВИТЬСЯ СО ВРЕМЯ СЛИШКОМ ОЧЕВИДНЫЙ БОЛЬШОЙ ВНИМАНИЕ ДИЗАЙН УДЕЛЯТЬ ТОТ КТО ОПЕРИРОВАТЬ КРУПНЫЙ ПЛАТФОРМА И ОНИ ТЯНУТЫЙ ВЕСЬ ОСТАЛЬНОЙ ЗА СЕБЯ NBSP В РОССИЯ ТАКОЙ ГИГАНТ НЕТ ЗАТО ЕСТЬ РАЗРАБОТЧИКИ-ЭНТУЗИАСТ КОТОРЫЙ ГДЕ-ТО ЧТО-ТО ПОДСМАТРИВАТЬ И СТАРАТЬСЯ ПЕРЕНОСИТЬ К МЫ В КРАСИВЫЙ ВИД NBSP О ПРОЕКТ FINCH КОМПЛЕКС ПРОЕКТ ДЛЯ LAQUO СПАРТАК RAQUO ПОЛУЧИТЬСЯ ОЧЕНЬ КРАСИВЫЙ ПРИ ЗАПУСК МЫ СМОЧЬ ЗАЩИТИТЬ ВИЗУАЛЬНЫЙ СОСТАВЛЯТЬ ОТ РАЗНЫЙ ОПАСНОСТЬ ПРАВДА ОНА ПРОДЕРЖАТЬСЯ НЕДОЛГО ЭТО ЕСТЕСТВЕННЫЙ ПРОЦЕСС РАЗВИТИЕ ЛЮБОЙ ПРОДУКТ MDASH КОГДА ТЫ ТОЛЬКО ЧТО-ТО ЗАПУСКАТЬ ОНО ВЫГЛЯДЕТЬ ЦЕЛЬНЫЙ НО ЧЕМ ДАЛЁКИЙ ОТ ЗАПУСК ТЕМ БЫСТРЫЙ ПРОДУКТ РАЗЛАГАТЬСЯ И РАЗРУШАТЬСЯ ГДЕ-ТО ПОЛГОДА И ТЫ ОТПУСКАТЬ ХВАТКА СПОЛЗАТЬ И ГОВОРИТЬ ВСЕ ОКЬ ПУСТЬ БЫТЬ ЧТО БЫТЬ И БОЛЬШИЙ НЕ СЛЕДИТЬ ЗА КОНСИСТЕНТНОСТЬ ПРОДУКТ ТО ЕСТЬ ЗА СТАБИЛЬНЫЙ РАБОТА САЙТ MDASH LAQUO РБК СТИЛЬ RAQUO ТУТ ВОПРОС ЧИСТО ФИНАНСОВЫЙ МОТИВАЦИЯ ПОДДЕРЖИВАТЬ КАЧЕСТВО БЫСТРО РАСТИ ПРОДУКТ MDASH ОЧЕНЬ ТРУДОЕМКИЙ РАБОТА РЕЗУЛЬТАТ КОТОРЫЙ НЕ ВСЕГДА ПОНЯТНЫЙ ДЛЯ КЛИЕНТ У ОН ДОПУСТИМЫЙ ДЕСЯТЬ ПАРТНЕР КАЖДЫЙ ИЗ КОТОРЫЙ ТРЕБОВАТЬ ЗАПУСТИТЬ СВОЕ ФУНКЦИЯ ТЯЖЕЛО СЛЕДИТЬ ЗА ТЕМ ЧТОБЫ И РАБОТА ДЕЛАТЬСЯ И ЦЕЛЬНОСТЬ СОХРАНЯТЬСЯ ЭТО РЕСУРСОЕМКИЙ ПРОЦЕСС И ЗА ОН НУЖНЫЙ ДОПЛАЧИВАТЬ И ВСЕ РАВНО В КОНЕЦ КОНЕЦ НАСТУПАТЬ МОМЕНТ КОГДА НЕОБХОДИМЫЙ СДЕЛАТЬ ПОЛНЫЙ РЕДИЗАЙН И ПРИДУМАТЬ ВСЕ С НУЛЬ NBSP ЭТО ПРОИСХОДИТЬ И ИЗ-ЗА ПРОЦЕСС ВНУТРИ ПРОДУКТ И ИЗ-ЗА ТОГО ЧТО ВНЕШНИЙ СРЕДА ТОЖЕ МЕНЯТЬСЯ КАЖДЫЙ ГОД ОБНОВЛЯТЬСЯ КОМПОНЕНТ ОПЕРАЦИОННЫЙ СИСТЕМА ПРИЛОЖЕНИЕ МЕНЯТЬСЯ КОНКУРЕНТНЫЙ ОКРУЖЕНИЕ В ЦЕЛЫЙ РАЗВИВАТЬСЯ ДИЗАЙН ВОЗМОЖНОСТЬ И ТАК ДАЛЕЕ ПОСЛЕ СКАЧКА ПРОЕКТ НА СЛЕДУЮЩИЙ УРОВЕНЬ СНОВА НАЧНУТЬСЯ ПРОЦЕСС РАЗЛОЖЕНИЕ ДЛЯ ДОЛГОЖИВУЩИЙ ПРОЕКТ ЭТО НОРМАЛЬНО И ХОРОШИЙ КЛИЕНТ ЭТО ПОНИМАТЬ NBSP NBSP NBSP NBSP NBSP О СТЕРЕОТИПНЫЙ ПРОГРАММИСТ КЛИЕНТ ВОЗРАСТ ГДЕ-ТО ЗА 45 ОЖИДАТЬ УВИДЕТЬ ТАКОЙ ГИК ИЗ ФИЛЬМ В РВАНЫЙ ДЖИНСЫ СВИТЕР ГОВОРЯЩИЙ НЕПОНЯТНЫЙ СЛОВО ЕСЛИ ТЫ ПОПАДАТЬ В ЭТОТ ОБРАЗ ТО У ОНИ ЧТО-ТО ЩЕЛКАТЬ И ОНИ ТЫ СРАЗУ ДОВЕРЯТЬ NBSP ЕСЛИ ПРИЕХАТЬ В ПИДЖАКЕ-ГАЛСТУК ТО ОНИ МОЧЬ НЕ ПОНЯТЬ ПОДУМАТЬ ЧТО ТЫ ТАКОЙ ЖЕ КАК ОНИ А ОНИ НИЧЕГО НЕ УМЕТЬ В ДИДЖИТАЛ КЛИЕНТ МОЛОДОЙ НАЧИНАТЬ УСЛОВНО СОРЕВНОВАТЬСЯ С ТЫ КТО МОДНЫЙ КТО БОЛЬШИЙ ПРОДУКТОЛОГ КТО БОЛЬШИЙ КАСТДЕТЬ ОТ CUSTOMER DEVELOPER ТЕСТИРОВЩИК ПРОТОТИП НА ПОТЕНЦИАЛЬНЫЙ ПОТРЕБИТЕЛЬ MDASH LAQUO РБК СТИЛЬ RAQUO И ТОМ ПОДОБНЫЙ НО ЭТО ПРОСТО НАБЛЮДЕНИЕ НЕ ЗАКОНОМЕРНОСТЬ NBSP ОБ ОТНОШЕНИЕ К ЖИЗНЬ ЧТОБЫ МЫСЛИТЬ НА 10 ГОД ВПЕРЕД НАЧАТЬ НУЖНЫЙ С ТОГО ЧТОБЫ НЕ МЫСЛИТЬ НА 10 ГОД НАЗАД ПРОСТО МАЛЕНЬКИЙ ДУМАТЬ О ТОМ ЧТО БЫЛО РАННИЙ В ОТНОШЕНИЕ СЕБЯ И ТОГО ЧТО ТЫ ДЕЛАТЬ КАК ТОЛЬКО ПОЯВЛЯТЬСЯ НОВЫЙ ПРОЕКТ ВСЕ ЧТО ТЫ ДЕЛАТЬ НА ПРЕДЫДУЩИЙ MDASH УЖЕ ИСТОРИЯ КОТОРЫЙ НУЖНЫЙ ЗАБЫТЬ СИЛА ПОЯВЛЯТЬСЯ ОТ ДЕЯТЕЛЬНОСТЬ ЕСЛИ ПЫТАТЬСЯ ИХ ЭКОНОМИТЬ ИЛИ КОПИТЬ MDASH ИХ СТАТЬ ТОЛЬКО МАЛЕНЬКИЙ Я ДАЖЕ СТАРАТЬСЯ СКРЫВАТЬ ОБРАЗОВАНИЕ ИСТОРИК ПОТОМУ ЧТО ОБЫЧНО НАЧИНАТЬСЯ ВОПРОС LAQUO О А КАК ТЫ ДУМАТЬ О ТОМ И ОБ ЭТО RAQUO ОСОБЕННО КОГДА С ТАКСИСТ РАЗГОВАРИВАТЬ MDASH ОНИ ОЧЕНЬ ЛЮБИТЬ ДОКАПЫВАТЬСЯ ЕСЛИ УЗНАТЬ ЧТО У ТЫ ИСТОРИЧЕСКИЙ ОБРАЗОВАНИЕ ТАК ЧТО ХОББИ НИКАК НЕ СВЯЗАТЬ С ОН NBSP NBSP NBSP NBSP ЭМПАТИЯ ОТДЕЛЬНО MDASH РАБОТА ОТДЕЛЬНО КОМФОРТНЫЙ УСЛОВИЕ ОБЕСПЕЧИВАТЬСЯ НЕ ОТНОШЕНИЕ С КЛИЕНТ А ПРИНЯТЬ РЕГЛАМЕНТ РАБОТА ВСЕ ЧТО СВЕРХ ЭТО NBSP MDASH СИМПАТИЯ ИНОГДА ДАЖЕ ДРУЖБА MDASH ИДЕТ КАК БОНУС НЕЧЕСТНЫЙ КОНКУРЕНТНЫЙ ПРЕИМУЩЕСТВО ОНО КОНЕЧНЫЙ ОЧЕНЬ ПОЛЕЗНО И ПОМОГАТЬ В РАБОТА НО НЕ ЯВЛЯТЬСЯ ОБЯЗАТЕЛЬНЫЙ УСЛОВИЕ МУЗЫКА Я НРАВИТЬСЯ ВСЕГДА ОНА ВЫСТУПАТЬ МОТИВАЦИЯ В ТОМ ЧИСЛО И В РАБОТА ПЛЮС ВСЕ ВЕЩИЙ СВЯЗАТЬ С ОФОРМЛЕНИЕ КОНЦЕРТНЫЙ ПОСТЕР САЙТ МУЗЫКАЛЬНЫЙ ГРУППА MDASH ВИЗУАЛЬНО КРАСИВЫЙ ЭТО МЕНЬ ПРИВЛЕКАТЬ Я ДОЛГИЙ ВРЕМЯ ЗАНИМАТЬСЯ МУЗЫКА У МЕНЬ КУЧА ИНСТРУМЕНТ ПРАВДА ПОСВЯТИТЬ СЕБЯ МУЗЫКА ПОЛНОСТЬЮ СЕЙЧАС НЕ ПОЛУЧАТЬСЯ ЧТОБЫ МЫСЛИТЬ НА 10 ГОД ВПЕРЕД НАЧАТЬ НУЖНЫЙ С ТОГО ЧТОБЫ НЕ МЫСЛИТЬ НА 10 ГОД НАЗАД NBSP Я ОКОНЧИТЬ МУЗЫКАЛЬНЫЙ ШКОЛА ПО КЛАСС ВИОЛОНЧЕЛЬ ПОТОМ ЗАНИМАТЬСЯ САКСОФОН С ТОТ ПОРА ИГРАТЬ НА ГИТАРА БАРАБАН NBSP БАРАБАН ДАВАТЬСЯ СЛОЖНЫЙ ВСЕГО У ТЫ ЛИБО ЕСТЬ НАВЫК УДЕРЖАНИЕ ЧЕТКОГО NBSP РИТМ ЛИБО НЕТ У МЕНЬ НЕТ ПОЭТОМУ С БАРАБАН НЕ ОСОБЕННО НО ЗАТО С КЛАВИШНЫЙ ВСЕ ХОРОШО КОГДА ТЫ СЕМЬ ГОД ИМЕТЬ ДЕТЬ С ОДИН ИНСТРУМЕНТ NBSP С ДЕТСКИЙ ВОЗРАСТ ЗАНЯТИЕ НАЧИНАТЬ ДИКО НАДОЕДАТЬ МУЗЫКАЛЬНЫЙ ШКОЛА У МЕНЬ С ПЯТЬ ГОД НАЧАТЬСЯ СМЫСЛ В ТОМ ЧТО КАЖДЫЙ ИНСТРУМЕНТ ЧТО-ТО ПРИВЛЕКАТЬ В РАЗНЫЙ ВРЕМЯ И КОГДА БЕРЕСТЬСЯ ЗА НОВЫЙ MDASH КАЖЕТСЯ ЧТО ИМЕННО ОН ИДЕАЛЬНЫЙ ТОЧНО ТАК ЖЕ И С ПРОЕКТ ОБСТОЯТЬ ДЕТЬ У МЕНЬ В КАБИНЕТ ВИСЕТЬ ПОСТЕР КОНЦЕРТ SLY AMP THE FAMILY STONE 1978 ГОД В САН-ФРАНЦИСКО ТАСКАТЬ ЕГО С СЕБЯ УЖЕ ГОД 10 НИ ОДИН ВЕЩЬ МЕНЬ ТАК НЕ ВДОХНОВЛЯТЬ КАК ОН ОСНОВНЫЙ ДЕЯТЕЛЬНОСТЬ ПОЗВОЛЯТЬ ЗАНИМАТЬСЯ ТОЛЬКО УТИЛИТАРНЫЙ ХОББИ MDASH ОДИН СВЯЗАТЬ С ТЕМ ЧТОБЫ НЕ УМЕРЕТЬ ЭТО БОКС А ДРУГОЙ MDASH ЧТОБЫ БЫСТРЫЙ ВСЕ УСПЕВАТЬ ЭТО МОТОСПОРТ ОБЫЧНЫЙ ЧЕЛОВЕК БЕЗ МОТОЦИКЛ НА ВСТРЕЧА НЕ NBSP УСПЕТЬ А Я УСПЕВАТЬ С ПРИСУТСТВИЕ МОТОЦИКЛ В ЖИЗНЬ ДЛЯ МЕНЬ НОРМАЛЬНО ЕСЛИ ВСТРЕЧА НАЗНАЧАТЬ ГДЕ-ТО НА ВОЛГОГРАДСКИЙ ШОССЕ В ШЕСТЬ ВЕЧЕР NBSP Я НАХОДИТЬСЯ В ТАКОЙ СТАДИЯ ПРОСВЕТЛЕНИЕ КОГДА УЖЕ НЕ РАЗДЕЛЯТЬ КАКОЙ САЙТ КРАСИВЫЙ А КАКОЙ СТРАШНЫЙ НО У МЕНЬ ЕСТЬ СВОЕ ПОНИМАНИЕ ТОГО КАК ДОЛЖНЫЙ БЫТЬ ХОРОШО NBSP NBSP NBSP', 0, 0),

(2, 5, 'Киану Ривз: «Россия ассоциируется у меня с моральной силой»', '1552333645.png', '', '<p>В прокат вышел фильм &laquo;Профессионал&raquo; с Киану Ривзом в главной роли. Действие картины происходит в России. Накануне премьеры &laquo;РБК Стиль&raquo; поговорил с актером об особенностях русского менталитета, жизненных принципах и качестве современного кинематографа.</p>\r\n', '<div class="l-col-center__inner">\r\n<div class="article__overview ">\r\n<div class="article__rubric">&nbsp;</div>\r\n\r\n<div class="article__rubric"><img alt="" class="js-rbcslider-image" itemprop="image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380621811102.png" style="font-size: 1rem;" /></div>\r\n\r\n<div class="article__main-image">\r\n<div class="article__main-image__inner">\r\n<div class="article__main-image__copyrights">&nbsp;</div>\r\n\r\n<div class="article__main-image__copyrights">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__social js-social-likes">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="Киану Ривз: «Россия ассоциируется у меня с моральной силой»">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__info">\r\n<div class="article__author">Автор <span class="article__author__name"> <!--\r\n                    --><!--\r\n                        -->Марина Аржиловская<!--\r\n                        --><!--\r\n                    --><!--\r\n                --> </span></div>\r\n\r\n<div class="article__date" content="2018-09-28T08:22:29+03:00" itemprop="datePublished">28 сентября 2018</div>\r\n\r\n<div class="article__date" content="2018-09-28T08:22:29+03:00" itemprop="datePublished">&nbsp;</div>\r\n<meta itemprop="dateModified" content="2018-09-28T08:22:29+03:00"></div>\r\n\r\n<div class="article__subtitle">В прокат вышел фильм &laquo;Профессионал&raquo; с Киану Ривзом в главной роли. Действие картины происходит в России. Накануне премьеры &laquo;РБК Стиль&raquo; поговорил с актером об особенностях русского менталитета, жизненных принципах и качестве современного кинематографа.</div>\r\n</div>\r\n\r\n<div class="article__text" itemprop="articleBody">\r\n<p>В свои 54 года Киану Ривз продолжает оставаться одним из самых загадочных и закрытых актеров Голливуда. Он не перестает удивлять публику, выбирая для себя неожиданные роли, перемещаясь на метро как самый обычный пассажир и даже публикуя стихи.</p>\r\n\r\n<p>В этом году выходят сразу три фильма с участием Ривза, и все &mdash; в разных жанрах. Среди них &mdash; криминальная драма &laquo;Сибирь&raquo;, поступившая в российский прокат под названием &laquo;Профессионал&raquo;. Это довольно смелый эксперимент для актера: во время съемок и в процессе&nbsp;подготовки к ним&nbsp;он больше трех месяцев провел в разных регионах России &mdash; от Санкт-Петербурга до Новосибирска. Ривз играет торговца редкими видами бриллиантов, который оказывается вовлечен в криминальную сделку и совершает &laquo;рискованное путешествие&raquo; в Сибирь. Проект был задуман еще четыре года назад. В изначальной версии вместо Петербурга была Москва. Корректировку в сценарий внесли за два года до съемок.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Россия. Криминал. Сибирь. Если описывать фильм, то в таком порядке? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Ну, не совсем в таком <em> (смеется)</em>. На первом месте Россия, затем &mdash; Сибирь, а потом уж где-то криминал.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380628105421.png" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__text">Кадр из фильма &laquo;Профессионал&raquo;</div>\r\n\r\n<div class="article__picture_big__source">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Почему именно так? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Потому что Россия лично у меня ассоциируется в первую очередь с моральной силой. Для вас это норма. Только в жизни русские не такие суровые, как это обычно преподносится. В Петербурге, например, когда мы снимали сцены на улицах, люди очень робко просили автограф, стараясь не беспокоить лишний раз. Это выглядело довольно мило.</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> При этом в фильме &laquo;Профессионал&raquo; вы противостоите отнюдь не милым людям, а российским криминальным структурам. Почему вам захотелось участвовать в этом проекте? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Просто у меня таких еще не было. И сценарий оказался довольно увлекательным: там есть и любовный треугольник, и экшн, и колкий юмор.</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Сценарий чем-то напомнил &laquo;Джона Уика&raquo;.</span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Хм &hellip; согласен, мне не впервой ходить с дробовиком <em> (изображает крутого парня &mdash; прим. ред.). </em> На самом деле получилась такая классная колоритная картина.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> То есть это чистой воды эксперимент? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Скорее новый творческий опыт. К тому&nbsp;же я имел возможность расширить свой русский словарный запас.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Вы изучаете русский язык? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Знаю только самые ходовые фразы, к сожалению. Свободным владением пока похвастаться не могу.</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Вы&nbsp;же не только говорите на русском, но и читаете русскоязычные книги? </span></span><strong> </strong></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Классические пьесы и небольшие рассказы. Льва Толстого еще не осилил. Но пожалуйста, не просите меня сейчас произнести что-нибудь на русском, я еще не избавился от акцента. Пока только прислушиваюсь к вашему языку. Он красиво звучит, музыкально, в нем много интонаций и смысловых тональностей.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div><img class="js-galleryInfinity-img js-rbcslider-image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380628290003.png" /></div>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<div><img class="js-galleryInfinity-img js-rbcslider-image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380627211853.png" /></div>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<div><img class="js-galleryInfinity-img js-rbcslider-image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380627135444.png" /></div>\r\n\r\n<div class="gallery_infinity__info">\r\n<div class="gallery_infinity__navigation js-galleryInfinity-nav">\r\n<div class="gallery_infinity__arrow gallery_infinity__arrow__last js-galleryInfinity-last">&nbsp;</div>\r\n</div>\r\n\r\n<div class="gallery_infinity__description js-galleryInfinity-text" style="height: 45px;">\r\n<div class="gallery_infinity__flip" style="display: block;">\r\n<div>Кадр из фильма &laquo;Профессионал&raquo;\r\n<div class="gallery_infinity__copyright">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<div class="gallery_infinity__flip" style="display: none;">\r\n<div>Кадр из фильма &laquo;Профессионал&raquo;\r\n<div class="gallery_infinity__copyright">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<div class="gallery_infinity__flip" style="display: none;">\r\n<div>Кадр из фильма &laquo;Профессионал&raquo;\r\n<div class="gallery_infinity__copyright">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Кстати, о творчестве и языке. Вы выпустили уже две книги с собственными текстами, в том числе со стихами&nbsp;<em>(&laquo;Ода счастью&raquo; и &laquo;Тени&raquo; выпущены совместно с иллюстратором Александрой Грант&nbsp;&mdash; прим. ред.)</em>. Планируете издавать третью? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Вам настолько понравились две предыдущих? <em> (Немного задумавшись.) </em> Для меня поэзия &mdash; это не бизнес и даже не хобби. У меня накопилось много мыслей, и мне хотелось поделиться ими с миром&hellip; Как-то так. Не знаю, почему некоторые приняли это за троллинг. Я просто был честен. Рассказать о том, что спрятано внутри, бывает не так легко, как может показаться на первый взгляд. Я никого ни к чему не призывал, не хотел давать каких-то оценок. Мне было что сказать &mdash; и я это сделал. Не нужно искать скрытых мотивов и смыслов, думать об этом. Возвращаясь к вашему вопросу, отвечу так: вполне возможно, но это будет что-то другое, в другом жанре.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А мне как раз понравились ваша философская лирика, оттенки меланхолии. </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Искренне не советую в нее погружаться. Я люблю пошутить и часто не прочь подурачиться, не понимаю, почему меня упорно преподносят аудитории как эксперта по мрачному настроению&hellip;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Да, но вы однажды сказали: &laquo;Всегда нужно знать, что кому-то еще хуже, чем вам&raquo; &mdash; и фактически привели пример определенного временного отрезка из своей жизни. Это было смело.</span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> С тех пор многое изменилось. Я стараюсь заполнять свое время событиями или вещами, от которых получаю удовольствие.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Мотоциклы? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Одно из них, да. Каждый раз, когда я сажусь за руль, во мне просыпается что-то от зверя, необъяснимое дикое удовольствие. Это сложно объяснить словами. Это даже не выброс адреналина, а головокружительное ощущение безграничной свободы.</p>\r\n\r\n<div class="article__textextract">\r\n<div class="article__textextract__text">\r\n<p>Не понимаю, почему меня упорно преподносят аудитории как эксперта по мрачному настроению.</p>\r\n</div>\r\n\r\n<div class="article__textextract__social">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="\r\nНе понимаю, почему меня упорно преподносят аудитории как эксперта по мрачному настроению.\r\n">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А еще? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Опасный вопрос, не боитесь?</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> А стоит? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Пусть это останется моей тайной.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Есть ощущение, что у вас слишком много тайн, нет? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Не так много, как хотелось&nbsp;бы. Публичность &mdash; плата за успех.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Обычно здесь упоминают одиночество&hellip; </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Красивый способ вывести разговор на тему о личной жизни, но я не куплюсь! <em> (Смеется.) </em></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Вы до сих пор продолжаете искать своего &laquo;идеального человека&raquo;? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Да. И не намерен останавливаться. Если повезет &mdash; значит, я все-таки чем-то заслужил великое счастье.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Почему вы думаете, что счастье нужно именно заслужить? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Жизненный опыт. Я&nbsp;бы не хотел сейчас выводить длинную теорию, и вообще это долгий и обстоятельный разговор. Сформулирую кратко: есть определенный алгоритм Вселенной. Его нетрудно вычислить, если быть наблюдательным. Вы здорово сэкономите свое время, если поверите мне на слово.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380624923742.png" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__text">Кадр из фильма &laquo;Профессионал&raquo;</div>\r\n\r\n<div class="article__picture_big__source">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Ваше близкое окружение называет вас самым терпеливым человеком на планете. </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Серьезно? Вас разыграли! Я бываю очень раздражителен, подвержен сильным эмоциям и порывам, да, еще и жутко требовательным.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Трудно в это поверить.</span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Да-да, мне часто не хватает выдержки&hellip;</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Но меня вы прождали целый час. Я не знаю ни одну голливудскую знаменитость, способную на такой подвиг.<em> (По не зависящим от корреспондента &laquo;РБК Стиль&raquo; и героя причинам интервью началось позже запланированного времени. Киану Ривз отнесся к ситуации с пониманием и продолжил ждать нашего журналиста в отеле &mdash; прим. ред.) </em></span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Я всегда держу слово. Раз договорились об интервью &mdash; оно должно было состояться. Это, наверное, выглядит старомодно или недостаточно по-светски, но мне кажется это правильным.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А какие у вас еще жизненные правила? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Не знаю, я не замечал за собой, что живу по каким-то правилам. Важно делать то, что ты считаешь разумным и необходимым, быть полезным кому-то в нужные трудные моменты, от которых никто не застрахован.</p>\r\n\r\n<p>Идти вразрез с собственными убеждениями нельзя &mdash; это точно. Еще нужно быть самим собой &mdash; это, пожалуй, самое главное. Вообще интересный вопрос. Я сразу начал копаться в себе&hellip;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Многие постоянно говорят о кризисе в кино, о том, что найти качественный сценарий становится все сложнее. Вы с этим согласны? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Абсолютно! На поиск не идеального, но хорошего сценария уходит не меньше пяти-шести месяцев. Я очень кропотливо изучаю тексты и стараюсь разглядеть если не безупречную работу, то хотя&nbsp;бы перспективную основу, которую можно редактировать и наполнять. Сейчас редко попадается образцовое талантливое кино, в основном планка &mdash; это просто неплохая драматургия. Уверен, что зритель чувствует разницу.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Какие фильмы с вашим участием вы считаете главным достижением в своей карьере? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Я&nbsp;бы назвал фильмы, за которые мне точно никогда не будет стыдно. &laquo;Адвокат Дьявола&raquo;, &laquo;На гребне волны&raquo;, &laquo;Матрица&raquo;, &laquo;Помутнение&raquo;. Для меня еще очень важен язык картины, диалоги. Может быть, это идет со времен моей театральной деятельности, не знаю. Язык во многом определяет историю, он имеет не меньшее значение, чем сам сюжет и режиссерский стиль.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А еще &laquo;Константин&raquo; и &laquo;Дракула&raquo; Копполы. Кстати, как вы считаете &mdash; в вашей реальной жизни присутствует мистика? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> У меня есть одна сверхспособность: я оказываюсь в нужное время в нужном месте. Это ведь можно назвать интуицией или шестым чувством?</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Скорее чутьем. </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Ну или так, да. Но в мистику я верю, это правда.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Вы следите за своим гардеробом, за современной модой? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Да, мне это интересно. Я очень тщательно подбираю себе костюмы. Люблю черный и серый цвета. Предпочитаю обувь в классическом стиле.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Без чего вы не мыслите свой день? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Без яичницы с беконом, чашки кофе и книг. Хотя нет, без кофе могу обойтись, а без книги точно нет.&nbsp;<span class="article__logo article__logo_no-left-margin"></span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\r\n</div>\r\n', '', '', '', '', '', '', 'КИАНА РИВЗ ?РОССИЯ АССОЦИИРОВАТЬСЯ У МЕНЬ С МОРАЛЬНЫЙ СИЛОЙ ?', 'NBSP NBSP NBSP АВТОР МАРИН АРЖИЛОВСКИЙ 28 СЕНТЯБРЬ 2018 NBSP В ПРОКАТ ВЫЙТИ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO С КИАНА РИВЗЫЙ В ГЛАВНЫЙ РОЛЯ ДЕЙСТВИЕ КАРТИНА ПРОИСХОДИТЬ В РОССИЯ НАКАНУНЕ ПРЕМЬЕР LAQUO РБК СТИЛЬ RAQUO ПОГОВОРИТЬ С АКТЕР ОБ ОСОБЕННОСТЬ РУССКИЙ МЕНТАЛИТЕТ ЖИЗНЕННЫЙ ПРИНЦИП И КАЧЕСТВО СОВРЕМЕННЫЙ КИНЕМАТОГРАФ В СВОЕ 54 ГОД КИАНА РИВЗ ПРОДОЛЖАТЬ ОСТАВАТЬСЯ ОДИН ИЗ САМЫЙ ЗАГАДОЧНЫЙ И ЗАКРЫТЫЙ АКТЕР ГОЛЛИВУД ОН НЕ ПЕРЕСТАЯТЬ УДИВЛЯТЬ ПУБЛИКА ВЫБИРАТЬ ДЛЯ СЕБЯ НЕОЖИДАННЫЙ РОЛЯ ПЕРЕМЕЩАТЬСЯ НА МЕТРО КАК САМЫЙ ОБЫЧНЫЙ ПАССАЖИР И ДАЖЕ ПУБЛИКОВАТЬ СТИХ В ЭТО ГОД ВЫХОДИТЬ СРАЗУ ТЕРЕТЬ ФИЛЬМ С УЧАСТИЕ РИВЗ И ВСЕ MDASH В РАЗНЫЙ ЖАНР СРЕДИ ОНИ MDASH КРИМИНАЛЬНЫЙ ДРАМА LAQUO СИБИРЬ RAQUO ПОСТУПИТЬ В РОССИЙСКИЙ ПРОКАТ ПОД НАЗВАНИЕ LAQUO ПРОФЕССИОНАЛ RAQUO ЭТО ДОВОЛЬНО СМЕЛЫЙ ЭКСПЕРИМЕНТ ДЛЯ АКТЕР ВО ВРЕМЯ СЪЕМКА И В ПРОЦЕСС NBSP ПОДГОТОВКА К ОН NBSP ОН БОЛЬШИЙ ТРИ МЕСЯЦ ПРОВЕТЬ В РАЗНЫЙ РЕГИОН РОССИЯ MDASH ОТ САНКТ-ПЕТЕРБУРГ ДО НОВОСИБИРСК РИВЗ ИГРАТЬ ТОРГОВЕЦ РЕДКИЙ ВИД БРИЛЛИАНТ КОТОРЫЙ ОКАЗЫВАТЬСЯ ВОВЛЕЧЕНЫЙ В КРИМИНАЛЬНЫЙ СДЕЛКА И СОВЕРШАТЬ LAQUO РИСКОВАННЫЙ ПУТЕШЕСТВИЕ RAQUO В СИБИРЬ ПРОЕКТ БЫТЬ ЗАДУМАТЬ ЕЩИЙ ЧЕТЫРЕ ГОД НАЗАД В ИЗНАЧАЛЬНЫЙ ВЕРСИЯ ВМЕСТО ПЕТЕРБУРГ БЫТЬ МОСКВА КОРРЕКТИРОВКА В СЦЕНАРИЙ ВНЕСТИ ЗА ДВА ГОД ДО СЪЕМКА MDASH РОССИЯ КРИМИНАЛ СИБИРЬ ЕСЛИ ОПИСЫВАТЬ ФИЛЬМ ТО В ТАКОЙ ПОРЯДОК MDASH НУ НЕ СОВСЕМ В ТАКОЙ СМЕТЬСЯ НА ПЕРВЫЙ МЕСТО РОССИЯ ЗАТЕМ MDASH СИБИРЬ А ПОТОМ УЖ ГДЕ-ТО КРИМИНАЛ NBSP КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU NBSP MDASH ПОЧЕМУ ИМЕННО ТАК MDASH ПОТОМУ ЧТО РОССИЯ ЛИЧНО У МЕНЬ АССОЦИИРОВАТЬСЯ В ПЕРВЫЙ ОЧЕРЕДЬ С МОРАЛЬНЫЙ СИЛОЙ ДЛЯ ВЫ ЭТО НОРМА ТОЛЬКО В ЖИЗНЬ РУССКИЙ НЕ ТАКОЙ СУРОВЫЙ КАК ЭТО ОБЫЧНО ПРЕПОДНОСИТЬСЯ В ПЕТЕРБУРГ НАПРИМЕР КОГДА МЫ СНИМАТЬ СЦЕНА НА УЛИЦА ЧЕЛОВЕК ОЧЕНЬ РОБКО ПРОСИТЬ АВТОГРАФ СТАРАТЬСЯ НЕ БЕСПОКОИТЬ ЛИШНИЙ РАЗ ЭТО ВЫГЛЯДЕТЬ ДОВОЛЬНО МИЛО MDASH ПРИ ЭТО В ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO ВЫ ПРОТИВОСТОЯТЬ ОТНЮДЬ НЕ МИЛЫЙ ЧЕЛОВЕК А РОССИЙСКИЙ КРИМИНАЛЬНЫЙ СТРУКТУРА ПОЧЕМУ ВЫ ЗАХОТЕТЬСЯ УЧАСТВОВАТЬ В ЭТО ПРОЕКТ MDASH ПРОСТО У МЕНЬ ТАКОЙ ЕЩИЙ НЕ БЫЛО И СЦЕНАРИЙ ОКАЗАТЬСЯ ДОВОЛЬНО УВЛЕКАТЕЛЬНЫЙ ТАМ ЕСТЬ И ЛЮБОВНЫЙ ТРЕУГОЛЬНИК И ЭКШН И КОЛКИЙ ЮМОР MDASH СЦЕНАРИЙ ЧТО-ТО НАПОМНИТЬ LAQUO ДЖОН УИКА RAQUO MDASH ХМ HELLIP СОГЛАСНЫЙ Я НЕ ВПЕРВОЙ ХОДИТЬ С ДРОБОВИК ИЗОБРАЖАТЬ КРУТОЙ ПАРЕНЬ MDASH ПРИМА РЕДАКЦИЯ НА САМЫЙ ДЕЛО ПОЛУЧИТЬСЯ ТАКАТЬ КЛАССНЫЙ КОЛОРИТНЫЙ КАРТИНА MDASH ТО ЕСТЬ ЭТО ЧИСТЫЙ ВОД ЭКСПЕРИМЕНТ MDASH СКОРЫЙ НОВЫЙ ТВОРЧЕСКИЙ ОПЫТ К ТОМ NBSP ЖЕ Я ИМЕТЬ ВОЗМОЖНОСТЬ РАСШИРИТЬ СВОЙ РУССКИЙ СЛОВАРНЫЙ ЗАПАС MDASH ВЫ ИЗУЧАТЬ РУССКИЙ ЯЗЫК MDASH ЗНАТЬ ТОЛЬКО САМЫЙ ХОДОВОЙ ФРАЗА К СОЖАЛЕНИЕ СВОБОДНЫЙ ВЛАДЕНИЕ ПОКА ПОХВАСТАТЬСЯ НЕ МОЧЬ MDASH ВЫ NBSP ЖЕ НЕ ТОЛЬКО ГОВОРИТЬ НА РУССКИЙ НО И ЧИТАТЬ РУССКОЯЗЫЧНЫЙ КНИГА MDASH КЛАССИЧЕСКИЙ ПЬЕСА И НЕБОЛЬШОЙ РАССКАЗ ЛЕВ ТОЛСТОЙ ЕЩИЙ НЕ ОСИЛИТЬ НО ПОЖАЛУЙСТА НЕ ПРОСИТЬ МЕНЬ СЕЙЧАС ПРОИЗНЕСТИ ЧТО-НИБУДЬ НА РУССКИЙ Я ЕЩИЙ НЕ ИЗБАВИТЬСЯ ОТ АКЦЕНТ ПОКА ТОЛЬКО ПРИСЛУШИВАТЬСЯ К ВАШ ЯЗЫК ОН КРАСИВО ЗВУЧАТЬ МУЗЫКАЛЬНО В НЕМОЙ МНОГО ИНТОНАЦИЯ И СМЫСЛОВОЙ ТОНАЛЬНОСТЬ NBSP NBSP NBSP NBSP КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU NBSP MDASH КСТАТИ О ТВОРЧЕСТВО И ЯЗЫК ВЫ ВЫПУСТИТЬ УЖЕ ДВА КНИГА С СОБСТВЕННЫЙ ТЕКСТ В ТОМ ЧИСЛО СО СТИХ NBSP LAQUO ОДА СЧАСТИЕ RAQUO И LAQUO ТЕНИТЬ RAQUO ВЫПУСТИТЬ СОВМЕСТНО С ИЛЛЮСТРАТОР АЛЕКСАНДРА ГРАНТ NBSP MDASH ПРИМА РЕДАКЦИЯ ПЛАНИРОВАТЬ ИЗДАВАТЬ ТРЕТЬ MDASH ВЫ НАСТОЛЬКО ПОНРАВИТЬСЯ ДВА ПРЕДЫДУЩИЙ НЕМНОГО ЗАДУМАТЬСЯ ДЛЯ МЕНЬ ПОЭЗИЯ MDASH ЭТО НЕ БИЗНЕС И ДАЖЕ НЕ ХОББИ У МЕНЬ НАКОПИТЬСЯ МНОГО МЫСЛЬ И Я ХОТЕТЬСЯ ПОДЕЛИТЬСЯ ОНИ С МИР HELLIP КАК-ТО ТАК НЕ ЗНАТЬ ПОЧЕМУ НЕКОТОРЫЙ ПРИНЯТЬ ЭТО ЗА ТРОЛЛИНГ Я ПРОСТО БЫТЬ ЧЕСТНЫЙ РАССКАЗАТЬ О ТОМ ЧТО СПРЯТАТЬ ВНУТРИ БЫВАТЬ НЕ ТАК ЛЕГКО КАК МОЖЕТ ПОКАЗАТЬСЯ НА ПЕРВЫЙ ВЗГЛЯД Я НИКТО НИ К ЧТО НЕ ПРИЗЫВАТЬ НЕ ХОТЕТЬ ДАВАТЬ КАКОЙ-ТО ОЦЕНКА Я БЫЛО ЧТО СКАЗАТЬ MDASH И Я ЭТО СДЕЛАТЬ НЕ НУЖНЫЙ ИСКАТЬ СКРЫТЫЙ МОТИВ И СМЫСЛ ДУМАТЬ ОБ ЭТО ВОЗВРАЩАТЬСЯ К ВАШ ВОПРОС ОТВЕТИТЬ ТАК ВПОЛНЕ ВОЗМОЖНО НО ЭТО БЫТЬ ЧТО-ТО ДРУГОЙ В ДРУГ ЖАНР MDASH А Я КАК РАЗ ПОНРАВИТЬСЯ ВАШ ФИЛОСОФСКИЙ ЛИРИК ОТТЕНОК МЕЛАНХОЛИЯ MDASH ИСКРЕННЕ НЕ СОВЕТОВАТЬ В НЕЯ ПОГРУЖАТЬСЯ Я ЛЮБИТЬ ПОШУТИТЬ И ЧАСТО НЕ ПРОЧЬ ПОДУРАЧИТЬСЯ НЕ ПОНИМАТЬ ПОЧЕМУ МЕНЬ УПОРНО ПРЕПОДНОСИТЬ АУДИТОРИЯ КАК ЭКСПЕРТ ПО МРАЧНЫЙ НАСТРОЕНИЕ HELLIP MDASH ДА НО ВЫ ОДНАЖДЫ СКАЗАТЬ LAQUO ВСЕГДА НУЖНЫЙ ЗНАТЬ ЧТО КТО-ТО ЕЩИЙ ПЛОХО ЧЕМ ВЫ RAQUO MDASH И ФАКТИЧЕСКИЙ ПРИВЕСТИ ПРИМЕР ОПРЕДЕЛЕННЫЙ ВРЕМЕННЫЙ ОТРЕЗКА ИЗ СВОЙ ЖИЗНЬ ЭТО БЫЛО СМЕЛО MDASH С ТОТ ПОРА МНОГИЙ ИЗМЕНИТЬСЯ Я СТАРАТЬСЯ ЗАПОЛНЯТЬ СВОЕ ВРЕМЯ СОБЫТИЕ ИЛИ ВЕЩЬ ОТ КОТОРЫЙ ПОЛУЧАТЬ УДОВОЛЬСТВИЕ MDASH МОТОЦИКЛ MDASH ОДИН ИЗ ОНИ ДА КАЖДЫЙ РАЗ КОГДА Я САДИТЬСЯ ЗА РУЛЬ ВО Я ПРОСЫПАТЬСЯ ЧТО-ТО ОТ ЗВЕРЬ НЕОБЪЯСНИМЫЙ ДИКИЙ УДОВОЛЬСТВИЕ ЭТО СЛОЖНО ОБЪЯСНИТЬ СЛОВО ЭТО ДАЖЕ НЕ ВЫБРОС АДРЕНАЛИН А ГОЛОВОКРУЖИТЕЛЬНЫЙ ОЩУЩЕНИЕ БЕЗГРАНИЧНЫЙ СВОБОДА НЕ ПОНИМАТЬ ПОЧЕМУ МЕНЬ УПОРНО ПРЕПОДНОСИТЬ АУДИТОРИЯ КАК ЭКСПЕРТ ПО МРАЧНЫЙ НАСТРОЕНИЕ MDASH А ЕЩИЙ MDASH ОПАСНЫЙ ВОПРОС НЕ БОЯТЬСЯ MDASH А СТОИТЬ MDASH ПУСТЬ ЭТО ОСТАТЬСЯ МОЙ ТАЙНА MDASH ЕСТЬ ОЩУЩЕНИЕ ЧТО У ВЫ СЛИШКОМ МНОГО ТАЙНА НЕТ MDASH НЕ ТАК МНОГО КАК ХОТЕТЬСЯ NBSP БЫ ПУБЛИЧНОСТЬ MDASH ПЛАТ ЗА УСПЕХ MDASH ОБЫЧНО ЗДЕСЬ УПОМИНАТЬ ОДИНОЧЕСТВО HELLIP MDASH КРАСИВЫЙ СПОСОБ ВЫВЕСТИ РАЗГОВОР НА ТЕМА О ЛИЧНЫЙ ЖИЗНЬ НО Я НЕ КУПИТЬСЯ СМЕТЬСЯ MDASH ВЫ ДО СЕЙ ПОРА ПРОДОЛЖАТЬ ИСКАТЬ СВОЕ LAQUO ИДЕАЛЬНЫЙ ЧЕЛОВЕК RAQUO MDASH ДА И НЕ НАМЕРЕННЫЙ ОСТАНАВЛИВАТЬСЯ ЕСЛИ ПОВЕЗТЬ MDASH ЗНАЧИТ Я ВСЕ-ТАКИ ЧТО-ТО ЗАСЛУЖИТЬ ВЕЛИКОЕ СЧАСТИЕ MDASH ПОЧЕМУ ВЫ ДУМАТЬ ЧТО СЧАСТИЕ НУЖНЫЙ ИМЕННО ЗАСЛУЖИТЬ MDASH ЖИЗНЕННЫЙ ОПЫТ Я NBSP БЫ НЕ ХОТЕТЬ СЕЙЧАС ВЫВОДИТЬ ДЛИННЫЙ ТЕОРИЯ И ВООБЩЕ ЭТО ДОЛГИЙ И ОБСТОЯТЕЛЬНЫЙ РАЗГОВОР СФОРМУЛИРОВАТЬ КРАТКО ЕСТЬ ОПРЕДЕЛЕННЫЙ АЛГОРИТМ ВСЕЛЕННАЯ ЕГО НЕТРУДНО ВЫЧИСЛИТЬ ЕСЛИ БЫТЬ НАБЛЮДАТЕЛЬНЫЙ ВЫ ЗДОРОВО СЭКОНОМИТЬ СВОЕ ВРЕМЯ ЕСЛИ ПОВЕРИТЬ Я НА СЛОВО NBSP КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU NBSP MDASH ВАШ БЛИЗКИЙ ОКРУЖЕНИЕ НАЗЫВАТЬ ВЫ САМЫЙ ТЕРПЕЛИВЫЙ ЧЕЛОВЕК НА ПЛАНЕТ MDASH СЕРЬЕЗНЫЙ ВЫ РАЗЫГРАТЬ Я БЫВАТЬ ОЧЕНЬ РАЗДРАЖИТЕЛЬНЫЙ ПОДВЕРЖЕННЫЙ СИЛЬНЫЙ ЭМОЦИЯ И ПОРЫВ ДА ЕЩИЙ И ЖУТКО ТРЕБОВАТЕЛЬНЫЙ MDASH ТРУДНО В ЭТО ПОВЕРИТЬ MDASH ДА-Д Я ЧАСТО НЕ ХВАТАТЬ ВЫДЕРЖКА HELLIP MDASH НО МЕНЬ ВЫ ПРОЖДАТЬ ЦЕЛЫЙ ЧАС Я НЕ ЗНАТЬ НИ ОДИН ГОЛЛИВУДСКИЙ ЗНАМЕНИТОСТЬ СПОСОБНЫЙ НА ТАКОЙ ПОДВИГ ПО НЕ ЗАВИСЕТЬ ОТ КОРРЕСПОНДЕНТ LAQUO РБК СТИЛЬ RAQUO И ГЕРОЙ ПРИЧИНА ИНТЕРВЬЮ НАЧАТЬСЯ ПОЗДНИЙ ЗАПЛАНИРОВАТЬ ВРЕМЕНИТЬ КИАНА РИВЗ ОТНЕССЯ К СИТУАЦИЯ С ПОНИМАНИЕ И ПРОДОЛЖИТЬ ЖДАТЬ НАШ ЖУРНАЛИСТ В ОТЕЛЬ MDASH ПРИМА РЕДАКЦИЯ MDASH Я ВСЕГДА ДЕРЗИТЬ СЛОВО РАЗ ДОГОВОРИТЬСЯ ОБ ИНТЕРВЬЮ MDASH ОНО ДОЛЖНЫЙ БЫЛО СОСТОЯТЬСЯ ЭТО НАВЕРНОЕ ВЫГЛЯДЕТЬ СТАРОМОДНЫЙ ИЛИ НЕДОСТАТОЧНО ПО-СВЕТСКИ НО Я КАЖЕТСЯ ЭТО ПРАВИЛЬНЫЙ MDASH А КАКОЙ У ВЫ ЕЩИЙ ЖИЗНЕННЫЙ ПРАВИТЬ MDASH НЕ ЗНАТЬ Я НЕ ЗАМЕЧАТЬ ЗА СЕБЯ ЧТО ЖИТЬ ПО КАКОЙ-ТО ПРАВИЛО ВАЖНО ДЕЛАТЬ ТО ЧТО ТЫ СЧИТАТЬ РАЗУМНЫЙ И НЕОБХОДИМЫЙ БЫТЬ ПОЛЕЗНЫЙ КТО-ТО В НУЖНЫЙ ТРУДНЫЙ МОМЕНТ ОТ КОТОРЫЙ НИКТО НЕ ЗАСТРАХОВАТЬ ИДТИ ВРАЗРЕЗ С СОБСТВЕННЫЙ УБЕЖДЕНИЕ НЕЛЬЗЯ MDASH ЭТО ТОЧНО ЕЩИЙ НУЖНЫЙ БЫТЬ САМ СЕБЯ MDASH ЭТО ПОЖАЛУЙ САМЫЙ ГЛАВНОЕ ВООБЩЕ ИНТЕРЕСНЫЙ ВОПРОС Я СРАЗУ НАЧАЛО КОПАТЬСЯ В СЕБЕ HELLIP MDASH МНОГИЙ ПОСТОЯННО ГОВОРЯТ О КРИЗИС В КИНО О ТОМ ЧТО НАЙТИ КАЧЕСТВЕННЫЙ СЦЕНАРИЙ СТАНОВИТЬСЯ ВСЕ СЛОЖНЫЙ ВЫ С ЭТО СОГЛАСНЫЙ MDASH АБСОЛЮТНО НА ПОИСК НЕ ИДЕАЛЬНЫЙ НО ХОРОШИЙ СЦЕНАРИЙ УХОДИТЬ НЕ МАЛЕНЬКИЙ ПЯТИ-ШЕСТЬ МЕСЯЦ Я ОЧЕНЬ КРОПОТЛИВЫЙ ИЗУЧАТЬ ТЕКСТ И СТАРАТЬСЯ РАЗГЛЯДЕТЬ ЕСЛИ НЕ БЕЗУПРЕЧНЫЙ РАБОТА ТО ХОТЯ NBSP БЫ ПЕРСПЕКТИВНЫЙ ОСНОВА КОТОРЫЙ МОЖНО РЕДАКТИРОВАТЬ И НАПОЛНЯТЬ СЕЙЧАС РЕДКО ПОПАДАТЬСЯ ОБРАЗЦОВЫЙ ТАЛАНТЛИВЫЙ КИНО В ОСНОВНЫЙ ПЛАНКА MDASH ЭТО ПРОСТО НЕПЛОХОЙ ДРАМАТУРГИЯ УВЕРИТЬ ЧТО ЗРИТЕЛЬ ЧУВСТВОВАТЬ РАЗНИЦА MDASH КАКОЙ ФИЛЬМ С ВАШ УЧАСТИЕ ВЫ СЧИТАТЬ ГЛАВНЫЙ ДОСТИЖЕНИЕ В СВОЙ КАРЬЕР MDASH Я NBSP БЫ НАЗВАТЬ ФИЛЬМ ЗА КОТОРЫЙ Я ТОЧНО НИКОГДА НЕ БЫТЬ СТЫДНЫЙ LAQUO АДВОКАТ ДЬЯВОЛ RAQUO LAQUO НА ГРЕБЕНЬ ВОЛНА RAQUO LAQUO МАТРИЦА RAQUO LAQUO ПОМУТНЕНИЕ RAQUO ДЛЯ МЕНЬ ЕЩИЙ ОЧЕНЬ ВАЖНЫЙ ЯЗЫК КАРТИНА ДИАЛОГ МОЖЕТ БЫТЬ ЭТО ИДЕТ СО ВРЕМЕННЫЙ МОЙ ТЕАТРАЛЬНЫЙ ДЕЯТЕЛЬНОСТЬ НЕ ЗНАТЬ ЯЗЫК ВО МНОГИЙ ОПРЕДЕЛЯТЬ ИСТОРИЯ ОН ИМЕТЬ НЕ МЕНЬШИЙ ЗНАЧЕНИЕ ЧЕМ САМ СЮЖЕТ И РЕЖИССЕРСКИЙ СТИЛЬ MDASH А ЕЩИЙ LAQUO КОНСТАНТИН RAQUO И LAQUO ДРАКУЛА RAQUO КОППОЛ КСТАТИ КАК ВЫ СЧИТАТЬ MDASH В ВАШ РЕАЛЬНЫЙ ЖИЗНЬ ПРИСУТСТВОВАТЬ МИСТИК MDASH У МЕНЬ ЕСТЬ ОДИН СВЕРХСПОСОБНОСТЬ Я ОКАЗЫВАТЬСЯ В НУЖНЫЙ ВРЕМЯ В НУЖНЫЙ МЕСТО ЭТО ВЕДЬ МОЖНО НАЗВАТЬ ИНТУИЦИЯ ИЛИ ШЕСТАЯ ЧУВСТВО MDASH СКОРЫЙ ЧУТЬЕ MDASH НУ ИЛИ ТАК ДА НО В МИСТИК Я ВЕРИТЬ ЭТО ПРАВДА MDASH ВЫ СЛЕДИТЬ ЗА СВОЕ ГАРДЕРОБ ЗА СОВРЕМЕННЫЙ МОДА MDASH ДА Я ЭТО ИНТЕРЕСНО Я ОЧЕНЬ ТЩАТЕЛЬНО ПОДБИРАТЬ СЕБЕ КОСТЮМ ЛЮБИТЬ ЧЕРНЫЙ И СЕРЫЙ ЦВЕТА ПРЕДПОЧИТАТЬ ОБУВЬ В КЛАССИЧЕСКИЙ СТИЛЬ MDASH БЕЗ ЧЕГО ВЫ НЕ МЫСЛИТЬ СВОЙ ДЕТЬ MDASH БЕЗ ЯИЧНИЦА С БЕКОН ЧАШКА КОФЕ И КНИГА ХОТЯ НЕТ БЕЗ КОФЕ МОЧЬ ОБОЙТИСЬ А БЕЗ КНИГА ТОЧНО НЕТ NBSP NBSP NBSP', 0, 0),

(3, 5, 'От бритвы до автомобиля: почему мир переходит к сервисам по подписке', '1552335850.png', '', 'В 2018-м люди все чаще предпочитают не покупать какие-то вещи, а получать по подписке. Раз в месяц с карточки списывается небольшая сумма и курьер приносит домой посылку с тем, что мы вряд ли стали бы покупать специально. Почему так происходит?', '<div class="l-col-center__inner">\r\n<div class="article__overview ">\r\n<div class="article__rubric">&nbsp;</div>\r\n\r\n<div class="article__main-image">\r\n<div class="article__main-image__inner"><img alt="" class="js-rbcslider-image" itemprop="image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323404663357.png" /></div>\r\n</div>\r\n\r\n<div class="article__social js-social-likes">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="От бритвы до автомобиля: почему мир переходит к сервисам по подписке">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__info">\r\n<div class="article__author">&nbsp;</div>\r\n\r\n<div class="article__author">Автор <span class="article__author__name"> <!--\r\n                    --><!--\r\n                        -->Сергей Король<!--\r\n                        --><!--\r\n                    --><!--\r\n                --> </span></div>\r\n\r\n<div class="article__date" content="2018-07-23T13:52:37+03:00" itemprop="datePublished">23 июля 2018</div>\r\n<meta itemprop="dateModified" content="2018-07-23T13:52:37+03:00"></div>\r\n\r\n<div class="article__subtitle">&nbsp;</div>\r\n\r\n<div class="article__subtitle">В 2018-м люди все чаще предпочитают не покупать какие-то вещи, а получать по подписке. Раз в месяц с карточки списывается небольшая сумма и курьер приносит домой посылку с тем, что мы вряд ли стали бы покупать специально. Почему так происходит?</div>\r\n</div>\r\n\r\n<div class="article__text" itemprop="articleBody">\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Хочу новый образ</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">В ближайшем будущем можно будет купить не только джинсы и мотоцикл, но и образ жизни беспечного байкера &mdash; целиком. Только представьте, вы платите, условно, миллион рублей, чтобы получить желаемый образ целиком. В вашей квартире делают ремонт и обставляют ее: кровать два на два для вечеринок, в гардеробе &mdash; кожаные куртки, черные джинсы и ботинки Red Wings. Возле дома стоит мотоцикл, в кошельке &mdash; абонемент в подпольный бар. На столе &mdash; свеча в виде черепа и дискография Nine Inch Nails. Все это образуется в жизни сразу, не нужно долго искать свой образ и копить вещи.</span></p>\r\n\r\n<p dir="ltr"><span> </span></p>\r\n\r\n<div class="row">\r\n<div class="col-md-4" style="margin-bottom: 1rem;"><img src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323371587729.jpg" /></div>\r\n\r\n<div class="col-md-4" style="margin-bottom: 1rem;"><img src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323371588843.jpg" /></div>\r\n\r\n<div class="col-md-4" style="margin-bottom: 1rem;"><img src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323371824677.jpg" /></div>\r\n</div>\r\n\r\n<div class="gallery_infinity__copyright">&copy; lot2046.com</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Похожим образом действует LOT 2046 от российского стартапера Вадика Мармеладова. Он успешно продал предыдущий проект </span><span>&mdash;&nbsp;Lapka &mdash;&nbsp;компании Airbnb и переехал в Шэньчжэнь, чтобы продавать людям жизнь по подписке. Члены клуба LOT 2046 платят по $ 100 в месяц и ежемесячно получают посылки. В них одежда, обувь, аксессуары, и даже мыло &mdash; все создано в минималистичном стиле по размеру заказчика. Недавно LOT 2046 вышел за рамки пакета с вещами &mdash; сервис предлагает подписчикам переночевать друг у друга, расположившись на специально созданном матрасе и угостившись при этом специально испеченными лепешками.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Не знаю что выбрать...</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Сервисы по подписке Coffee Box и Coffeevine доставляет по подписке кофе. С хорошим кофе возникают проблемы у многих людей: непонятно что выбрать при огромном разнообразии и сложности вкуса. Компания заявляет: мы отобрали лучшее для разных поводов, да еще и самое доступное.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Эти проекты работают в особенной&nbsp;нише&nbsp;&mdash; своеобразное&nbsp;кураторство в мире, где трудно разобраться самому. Что выбрать? Как выбрать? Как найти интересное из мира косметики? Бьюти-сервис&nbsp;Julep предлагает не выбирать, а пользоваться тем, что уже выбрали эксперты. Есть даже сервисы по доставке колбасы &mdash; Carnivore раз в месяц высылает нарезки со всего мира.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323376190015.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; thecoffeevine.com</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Не хочу об этом думать</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Расцвет современных сервисов по подписке пошел от Dollar Shave Club &mdash; небольшого стартапа, который закрывал единственную, но важную задачу: помогал мужчинам всегда бриться острыми бритвами. Ведь лезвия, как правило,&nbsp;тупятся не вовремя (например, перед свиданием или важной встречей), а купить их в магазине забываешь. Доставка бритвенных принадлежностей закрывала эту проблему насовсем. Рынок поверил в такую модель, а основатели Dollar Shave Club Марк Левин и Майкл Дубин продали компанию за </span><span>$&nbsp;1 млрд кэшем.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323385299051.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; facebook.com/DollarShaveClub</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Позже подтянулись другие подписочные сервисы, удовлетворяющие базовые потребности: одни доставляют трусы и носки, другие &mdash; презервативы. Care/Of позволяет не думать о витаминах.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Популярная и активно развивающаяся подписочная сфера в России &mdash; доставка комплектов готовой еды. Growfood, Elementaree и другие сервисы отучают людей ходить в супермаркет. Зачем покупать полкило семги и хранить ее в холодильнике неделю? Куда приятнее собрать ужин, словно из конструктора, кубики которого регулярно доставляют на дом&nbsp;свежими.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Удивите меня</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">И все же многие подписываются не из практических соображений, а просто потому что скучают по сюрпризам.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Подписка &mdash; это гарантированный подарок каждый месяц. Люди&nbsp;жадно ждут свои коробки и с нетерпением открывают их: что там на этот раз? Часто производители подыгрывают этому чувству, оформляя подписочные коробки словно сюрпризы. Некоторые даже предлагают доплатить за особую упаковку.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Vinyl Me, Please раз в месяц присылает виниловую пластинку &mdash; узнаешь какую, когда откроешь. Сервис дополнительно давит на любовь человека к коллекционированию: мол, твоя аудиотека растет сама, только успевай слушать.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Creation Crate шлет наборы для создания электронных устройств, Stickii Club &mdash; наклейки, Accio присылает совершенно случайную ерунду: от кружек до шоколадок.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323389759162.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; vinylmeplease.com</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Дорогие удовольствия</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Совершенно отдельная и пока небольшая категория подписных сервисов заменяет пользование дорогими вещами. В ней бурно развиваются сервисы аренды машин по подписке. Предложения есть у большинства брендов. Стоимость аренды бюджетного автомобиля начинается от </span><span>$&nbsp;500 в месяц.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Можно не&nbsp;покупать одну или две машины, а ездить на разных. Летом &mdash; на&nbsp;кабриолете, зимой &mdash; на внедорожнике.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323391464705.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; bmw.ru</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p dir="ltr">&nbsp;</p>\r\n\r\n<p dir="ltr"><span>Вполне возможно, что в&nbsp;будущем пользование дорогими и условно дорогими&nbsp;вещами&nbsp;станет преимущественно&nbsp;подписочным. Сегодня мы платим за квартиру, а завтра&nbsp;начнем платить за ее наполнение мебелью и аксессуарами.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Главное,&nbsp;вовремя остановиться</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Подписочные сервисы только на первый взгляд кажутся экономным способом получать новые вещи. На самом деле в них кроется опасность.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Платить </span><span>$ 10&ndash;20 за один сервис &mdash; приятно и не особо обременительно. Хуже, когда становится пять-шесть, а то и десять сервисов: на каждый подписываешься, забывая о предыдущих. Да подумаешь, мелочи &mdash; в итоге в какой-то момент в начале каждого месяца они разом ополовинивают только что полученную зарплату.&nbsp;</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Еще немного &mdash; и придется заводить отдельный сервис, в котором можно управлять подписками на другие сервисы. Главное, чтобы он не распространялся </span><span>по подписке.</span>&nbsp;<span class="article__logo article__logo_no-left-margin"></span></p>\r\n\r\n<p dir="ltr">&nbsp;</p>\r\n\r\n<p dir="ltr">&nbsp;</p>\r\n</div>\r\n</div>\r\n', '', '', '', '', '', '', 'ОТ БРИТВА ДО АВТОМОБИЛЬ ПОЧЕМУ МИР ПЕРЕХОДИТЬ К СЕРВИС ПО ПОДПИСКА', 'NBSP NBSP АВТОР СЕРГЕЙ КОРОЛЬ 23 ИЮЛЬ 2018 NBSP В 2018-М ЧЕЛОВЕК ВСЕ ЧАЩА ПРЕДПОЧИТАТЬ НЕ ПОКУПАТЬ КАКОЙ-ТО ВЕЩИЙ А ПОЛУЧАТЬ ПО ПОДПИСКА РАЗ В МЕСЯЦ С КАРТОЧКА СПИСЫВАТЬСЯ НЕБОЛЬШОЙ СУММА И КУРЬЕР ПРИНОСИТЬ ДОМОЙ ПОСЫЛКА С ТЕМ ЧТО МЫ ВРЯД ЛИ СТАЛЬ БЫ ПОКУПАТЬ СПЕЦИАЛЬНО ПОЧЕМУ ТАК ПРОИСХОДИТЬ ХОТЕТЬ НОВЫЙ ОБРАЗ В БЛИЖНИЙ БУДУЩИЙ МОЖНО БЫТЬ КУПИТЬ НЕ ТОЛЬКО ДЖИНСА И МОТОЦИКЛ НО И ОБРАЗ ЖИЗНЬ БЕСПЕЧНЫЙ БАЙКЕР MDASH ЦЕЛИКОМ ТОЛЬКО ПРЕДСТАВИТЬ ВЫ ПЛАТИТЬ УСЛОВНО МИЛЛИОН РУБЛЬ ЧТОБЫ ПОЛУЧИТЬ ЖЕЛАЕМЫЙ ОБРАЗ ЦЕЛИКОМ В ВАШ КВАРТИРА ДЕЛАТЬ РЕМОНТ И ОБСТАВЛЯТЬ ЕЕ КРОВАТЬ ДВА НА ДВА ДЛЯ ВЕЧЕРИНКА В ГАРДЕРОБ MDASH КОЖАНЫЙ КУРТКА ЧЕРНЫЙ ДЖИНСА И БОТИНОК RED WINGS ВОЗЛЕ ДОМА СТОИТЬ МОТОЦИКЛ В КОШЕЛЁК MDASH АБОНЕМЕНТ В ПОДПОЛЬНЫЙ БАР НА СТОЛ MDASH СВЕЧА В ВИД ЧЕРЕП И ДИСКОГРАФИЯ NINE INCH NAILS ВСЕ ЭТО ОБРАЗОВАТЬСЯ В ЖИЗНЬ СРАЗУ НЕ НУЖНЫЙ ДОЛГО ИСКАТЬ СВОЙ ОБРАЗ И КОПИТЬ ВЕЩИЙ COPY LOT2046 COM NBSP ПОХОЖИЙ ОБРАЗ ДЕЙСТВОВАТЬ LOT 2046 ОТ РОССИЙСКИЙ СТАРТАПЕР ВАДИК МАРМЕЛАДОВЫЙ ОН УСПЕШНО ПРОДАТЬ ПРЕДЫДУЩИЙ ПРОЕКТ MDASH NBSP LAPKA MDASH NBSP КОМПАНИЯ AIRBNB И ПЕРЕЕХАТЬ В ШЭНЬЧЖЭНИЙ ЧТОБЫ ПРОДАВАТЬ ЧЕЛОВЕК ЖИЗНЬ ПО ПОДПИСКА ЧЛЕН КЛУБ LOT 2046 ПЛАТИТЬ ПО 100 В МЕСЯЦ И ЕЖЕМЕСЯЧНО ПОЛУЧАТЬ ПОСЫЛКА В ОНИ ОДЕЖДА ОБУВЬ АКСЕССУАР И ДАЖЕ МЫЛО MDASH ВСЕ СОЗДАТЬ В МИНИМАЛИСТИЧНЫЙ СТИЛЬ ПО РАЗМЕР ЗАКАЗЧИК НЕДАВНО LOT 2046 ВЫЙТИ ЗА РАМКА ПАКЕТ С ВЕЩЬ MDASH СЕРВИС ПРЕДЛАГАТЬ ПОДПИСЧИК ПЕРЕНОЧЕВАТЬ ДРУГ У ДРУГ РАСПОЛОЖИТЬСЯ НА СПЕЦИАЛЬНО СОЗДАТЬ МАТРАС И УГОСТИТЬСЯ ПРИ ЭТО СПЕЦИАЛЬНО ИСПЕЧЕННЫЙ ЛЕПЕШКА NBSP NBSP NBSP NBSP НЕ ЗНАТЬ ЧТО ВЫБРАТЬ СЕРВИС ПО ПОДПИСКА COFFEE BOX И COFFEEVINE ДОСТАВЛЯТЬ ПО ПОДПИСКА КОФЕ С ХОРОШИЙ КОФЕ ВОЗНИКАТЬ ПРОБЛЕМА У МНОГИЙ ЧЕЛОВЕК НЕПОНЯТНО ЧТО ВЫБРАТЬ ПРИ ОГРОМНЫЙ РАЗНООБРАЗИЕ И СЛОЖНОСТЬ ВКУС КОМПАНИЯ ЗАЯВЛЯТЬ МЫ ОТОБРАТЬ ХОРОШИЙ ДЛЯ РАЗНЫЙ ПОВОД ДА ЕЩИЙ И САМЫЙ ДОСТУПНЫЙ ЭТОТ ПРОЕКТ РАБОТАТЬ В ОСОБЕННЫЙ NBSP НИША NBSP MDASH СВОЕОБРАЗНЫЙ NBSP КУРАТОРСТВО В МИР ГДЕ ТРУДНО РАЗОБРАТЬСЯ САМЫЙ ЧТО ВЫБРАТЬ КАК ВЫБРАТЬ КАК НАЙТИ ИНТЕРЕСНЫЙ ИЗ МИР КОСМЕТИКА БЬЮТИ-СЕРВИС NBSP JULEP ПРЕДЛАГАТЬ НЕ ВЫБИРАТЬ А ПОЛЬЗОВАТЬСЯ ТЕМ ЧТО УЖЕ ВЫБРАТЬ ЭКСПЕРТ ЕСТЬ ДАЖЕ СЕРВИС ПО ДОСТАВКА КОЛБАСА MDASH CARNIVORE РАЗ В МЕСЯЦ ВЫСЫЛАТЬ НАРЕЗКА СО ВСЕГО МИР NBSP COPY THECOFFEEVINE COM NBSP NBSP NBSP NBSP NBSP НЕ ХОТЕТЬ ОБ ЭТО ДУМАТЬ РАСЦВЕТ СОВРЕМЕННЫЙ СЕРВИС ПО ПОДПИСКА ПОШЕЛ ОТ DOLLAR SHAVE CLUB MDASH НЕБОЛЬШОЙ СТАРТАП КОТОРЫЙ ЗАКРЫВАТЬ ЕДИНСТВЕННЫЙ НО ВАЖНЫЙ ЗАДАЧА ПОМОГАТЬ МУЖЧИНА ВСЕГДА БРИТЬСЯ ОСТРЫЙ БРИТВА ВЕДЬ ЛЕЗВИЕ КАК ПРАВИТЬ NBSP ТУПИТЬСЯ НЕ ВОВРЕМЯ НАПРИМЕР ПЕРЕД СВИДАНИЕ ИЛИ ВАЖНЫЙ ВСТРЕЧА А КУПИТЬ ИХ В МАГАЗИН ЗАБЫВАТЬ ДОСТАВКА БРИТВЕННЫЙ ПРИНАДЛЕЖНОСТЬ ЗАКРЫВАТЬ ЭТОТ ПРОБЛЕМА НАСОВСЕМ РЫНОК ПОВЕРИТЬ В ТАКОЙ МОДЕЛЬ А ОСНОВАТЕЛЬ DOLLAR SHAVE CLUB МАРК ЛЕВИН И МАЙКЛ ДУБИНА ПРОДАТЬ КОМПАНИЯ ЗА NBSP 1 МИЛЛИАРД КЭШ NBSP COPY FACEBOOK COM DOLLARSHAVECLUB NBSP ПОЗДНИЙ ПОДТЯНУТЬСЯ ДРУГОЙ ПОДПИСОЧНЫЙ СЕРВИС УДОВЛЕТВОРЯТЬ БАЗОВЫЙ ПОТРЕБНОСТЬ ОДИН ДОСТАВЛЯТЬ ТРУС И НОСКА ДРУГОЙ MDASH ПРЕЗЕРВАТИВ CARE OF ПОЗВОЛЯТЬ НЕ ДУМАТЬ О ВИТАМИН ПОПУЛЯРНЫЙ И АКТИВНО РАЗВИВАЮЩИЙСЯ ПОДПИСОЧНЫЙ СФЕРА В РОССИЯ MDASH ДОСТАВКА КОМПЛЕКТ ГОТОВЫЙ ЕДА GROWFOOD ELEMENTAREE И ДРУГОЙ СЕРВИС ОТУЧАТЬ ЧЕЛОВЕК ХОДИТЬ В СУПЕРМАРКЕТ ЗАЧЕМ ПОКУПАТЬ ПОЛКИЛО СЕМГ И ХРАНИТЬ ЕЕ В ХОЛОДИЛЬНИК НЕДЕЛЯ КУДА ПРИЯТНЫЙ СОБРАТЬ УЖИНЫЙ СЛОВНО ИЗ КОНСТРУКТОР КУБИК КОТОРЫЙ РЕГУЛЯРНО ДОСТАВЛЯТЬ НА ДОМ NBSP СВЕЖИЙ NBSP NBSP NBSP NBSP УДИВИТЬ МЕНЬ И ВСЕ ЖЕ МНОГИЙ ПОДПИСЫВАТЬСЯ НЕ ИЗ ПРАКТИЧЕСКИЙ СООБРАЖЕНИЕ А ПРОСТО ПОТОМУ ЧТО СКУЧАТЬ ПО СЮРПРИЗ ПОДПИСКА MDASH ЭТО ГАРАНТИРОВАТЬ ПОДАРОК КАЖДЫЙ МЕСЯЦ ЧЕЛОВЕК NBSP ЖАДНО ЖДАТЬ СВОЕ КОРОБКА И С НЕТЕРПЕНИЕ ОТКРЫВАТЬ ИХ ЧТО ТАМ НА ЭТОТ РАЗ ЧАСТО ПРОИЗВОДИТЕЛЬ ПОДЫГРЫВАТЬ ЭТО ЧУВСТВО ОФОРМЛЯТЬ ПОДПИСОЧНЫЙ КОРОБКА СЛОВНО СЮРПРИЗ НЕКОТОРЫЙ ДАЖЕ ПРЕДЛАГАТЬ ДОПЛАТИТЬ ЗА ОСОБЫЙ УПАКОВКА VINYL ME PLEASE РАЗ В МЕСЯЦ ПРИСЫЛАТЬ ВИНИЛОВЫЙ ПЛАСТИНКА MDASH УЗНАТЬ КАКОЙ КОГДА ОТКРЫТЬ СЕРВИС ДОПОЛНИТЕЛЬНО ДАВИТЬ НА ЛЮБОВЬ ЧЕЛОВЕК К КОЛЛЕКЦИОНИРОВАНИЕ МОЛ ТВОЙ АУДИОТЕКА РАСТИ САМ ТОЛЬКО УСПЕВАТЬ СЛУШАТЬ CREATION CRATE ШЛЕТ НАБОР ДЛЯ СОЗДАНИЕ ЭЛЕКТРОННЫЙ УСТРОЙСТВО STICKII CLUB MDASH НАКЛЕЙКА ACCIO ПРИСЫЛАТЬ СОВЕРШЕННО СЛУЧАЙНЫЙ ЕРУНДА ОТ КРУЖКА ДО ШОКОЛАДКА NBSP COPY VINYLMEPLEASE COM NBSP NBSP NBSP NBSP NBSP ДОРОГОЙ УДОВОЛЬСТВИЕ СОВЕРШЕННО ОТДЕЛЬНЫЙ И ПОКА НЕБОЛЬШОЙ КАТЕГОРИЯ ПОДПИСНОЙ СЕРВИС ЗАМЕНЯТЬ ПОЛЬЗОВАНИЕ ДОРОГОЙ ВЕЩЬ В ОНА БУРНО РАЗВИВАТЬСЯ СЕРВИС АРЕНДА МАШИН ПО ПОДПИСКА ПРЕДЛОЖЕНИЕ ЕСТЬ У БОЛЬШИНСТВО БРЕНД СТОИМОСТЬ АРЕНДА БЮДЖЕТНЫЙ АВТОМОБИЛЬ НАЧИНАТЬСЯ ОТ NBSP 500 В МЕСЯЦ МОЖНО НЕ NBSP ПОКУПАТЬ ОДИН ИЛИ ДВА МАШИН А ЕЗДИТЬ НА РАЗНЫЙ ЛЕТОМ MDASH НА NBSP КАБРИОЛЕТ ЗИМОЙ MDASH НА ВНЕДОРОЖНИК NBSP COPY BMW RU NBSP NBSP ВПОЛНЕ ВОЗМОЖНО ЧТО В NBSP БУДУЩИЙ ПОЛЬЗОВАНИЕ ДОРОГОЙ И УСЛОВНО ДОРОГОЙ NBSP ВЕЩЬ NBSP СТАТЬ ПРЕИМУЩЕСТВЕННО NBSP ПОДПИСОЧНЫЙ СЕГОДНЯ МЫ ПЛАТИТЬ ЗА КВАРТИРА А ЗАВТРА NBSP НАЧЕНЬ ПЛАТИТЬ ЗА ЕЕ НАПОЛНЕНИЕ МЕБЕЛЬ И АКСЕССУАР NBSP ГЛАВНОЕ NBSP ВОВРЕМЯ ОСТАНОВИТЬСЯ ПОДПИСОЧНЫЙ СЕРВИС ТОЛЬКО НА ПЕРВЫЙ ВЗГЛЯД КАЗАТЬСЯ ЭКОНОМНЫЙ СПОСОБ ПОЛУЧАТЬ НОВЫЙ ВЕЩИЙ НА САМЫЙ ДЕЛО В ОНИ КРЫТЬСЯ ОПАСНОСТЬ ПЛАТИТЬ 10 NDASH 20 ЗА ОДИН СЕРВИС MDASH ПРИЯТНО И НЕ ОСОБО ОБРЕМЕНИТЕЛЬНЫЙ ПЛОХО КОГДА СТАНОВИТЬСЯ ПЯТЬ-ШЕСТЬ А ТО И ДЕСЯТЬ СЕРВИС НА КАЖДЫЙ ПОДПИСЫВАТЬСЯ ЗАБЫВАТЬ О ПРЕДЫДУЩИЙ ДА ПОДУМАТЬ МЕЛОЧЬ MDASH В ИТОГ В КАКОЙ-ТО МОМЕНТ В НАЧАЛО КАЖДЫЙ МЕСЯЦ ОНИ РАЗОМ ОПОЛОВИНИВАТЬ ТОЛЬКО ЧТО ПОЛУЧИТЬ ЗАРПЛАТА NBSP ЕЩИЙ НЕМНОГО MDASH И ПРИСТИСЬ ЗАВОДИТЬ ОТДЕЛЬНЫЙ СЕРВИС В КОТОР МОЖНО УПРАВЛЯТЬ ПОДПИСКА НА ДРУГОЙ СЕРВИС ГЛАВНОЕ ЧТОБЫ ОН НЕ РАСПРОСТРАНЯТЬСЯ ПО ПОДПИСКА NBSP NBSP NBSP', 0, 0),

(4, 0, 'Политика организации по обработке персональных данных', '', '', '', '<strong>1. Общие положения</strong>\r\n<p>&nbsp;</p>\r\n\r\n<p>1.1. Настоящая Политика в отношении обработки персональных данных</p>\r\n\r\n<p>(далее &ndash; Политика) составлена в соответствии с пунктом 2 статьи 18.1 Федерального закона &laquo;О персональных данных&raquo; № 152-ФЗ от 27 июля 2006 г., а также иными нормативными правовыми актами Российской Федерации в области защиты и обработки персональных данных и действует в отношении всех персональных данных (далее &ndash; данные), которые Организация (далее &ndash; Оператор, Общество) может получить от субъекта персональных данных, являющегося стороной по гражданско-правовому договору, от пользователя сети Интернет (далее &ndash; Пользователь) во время использования им любого из сайтов, сервисов, служб, программ, продуктов или услуг ООО &laquo;&raquo;, а также от субъекта персональных данных, состоящего с Оператором в отношениях, регулируемых трудовым законодательством (далее &ndash; Работник).</p>\r\n\r\n<p>1.2. Оператор обеспечивает защиту обрабатываемых персональных данных от несанкционированного доступа и разглашения, неправомерного использования или утраты в соответствии с требованиями Федерального закона от 27 июля 2006 г. № 152-ФЗ &laquo;О персональных данных&raquo;.</p>\r\n\r\n<p>1.3. Оператор вправе вносить изменения в настоящую Политику. При внесении изменений в заголовке Политики указывается дата последнего обновления редакции. Новая редакция Политики вступает в силу с момента ее размещения на сайте, если иное не предусмотрено новой редакцией Политики.</p>\r\n\r\n<p><strong>2. Термины и принятые сокращения</strong></p>\r\n\r\n<p>Персональные данные &ndash; любая информация, относящаяся к прямо или косвенно определенному или определяемому физическому лицу (субъекту персональных данных).</p>\r\n\r\n<p>Обработка персональных данных &ndash; любое действие (операция) или совокупность действий (операций), совершаемых с использованием средств автоматизации или без использования таких средств с персональными данными, включая сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (распространение, предоставление, доступ), обезличивание, блокирование, удаление, уничтожение персональных данных. Автоматизированная обработка персональных данных &ndash; обработка персональных данных с помощью средств вычислительной техники. Информационная система персональных данных (ИСПД) &ndash; совокупность содержащихся в базах данных персональных данных и обеспечивающих их обработку информационных технологий и технических средств.</p>\r\n\r\n<p>Персональные данные, сделанные общедоступными субъектом персональных данных, &ndash; персональные данные, доступ неограниченного круга лиц к которым предоставлен субъектом персональных данных либо по его просьбе. Блокирование персональных данных &ndash; временное прекращение обработки персональных данных (за исключением случаев, если обработка необходима для уточнения персональных данных).</p>\r\n\r\n<p>Уничтожение персональных данных &ndash; действия, в результате которых становится невозможным восстановить содержание персональных данных в информационной системе персональных данных и (или) в результате которых уничтожаются материальные носители персональных данных.</p>\r\n\r\n<p>Оператор &ndash; организация, самостоятельно или совместно с другими лицами организующая обработку персональных данных, а также определяющая цели обработки персональных данных, подлежащих обработке, действия (операции), совершаемые с персональными данными. Оператором является ООО &laquo;&raquo;, расположенное по адресу:</p>\r\n\r\n<p><strong>3. Обработка персональных данных</strong></p>\r\n\r\n<p>3.1. Получение персональных данных.</p>\r\n\r\n<p>3.1.1. Все персональные данные следует получать от самого субъекта. Если персональные данные субъекта можно получить только у третьей стороны, то субъект должен быть уведомлен об этом или от него должно быть получено согласие.</p>\r\n\r\n<p>3.1.2. Оператор должен сообщить субъекту о целях, предполагаемых источниках и способах получения персональных данных, характере подлежащих получению персональных данных, перечне действий с персональными данными, сроке, в течение которого действует согласие, и порядке его отзыва, а также о последствиях отказа субъекта дать письменное согласие на их получение.</p>\r\n\r\n<p>3.1.3. Документы, содержащие персональные данные, создаются путем:</p>\r\n\r\n<p>&ndash; копирования оригиналов документов (паспорт, документ об образовании, свидетельство ИНН, пенсионное свидетельство и др.);</p>\r\n\r\n<p>&ndash; внесения сведений в учетные формы;</p>\r\n\r\n<p>&ndash; получения оригиналов необходимых документов (трудовая книжка, медицинское заключение, характеристика и др.).</p>\r\n\r\n<p>3.2. Обработка персональных данных.</p>\r\n\r\n<p>3.2.1. Обработка персональных данных осуществляется:</p>\r\n\r\n<p>&ndash; с согласия субъекта персональных данных на обработку его персональных данных;</p>\r\n\r\n<p>&ndash; в случаях, когда обработка персональных данных необходима для осуществления и выполнения возложенных законодательством РФ функций, полномочий и обязанностей;</p>\r\n\r\n<p>&ndash; в случаях, когда осуществляется обработка персональных данных, доступ неограниченного круга лиц к которым предоставлен субъектом персональных данных либо по его просьбе (далее &ndash; персональные данные, сделанные общедоступными субъектом персональных данных).</p>\r\n\r\n<p>3.2.2. Цели обработки персональных данных:</p>\r\n\r\n<p>&ndash; осуществление трудовых отношений;</p>\r\n\r\n<p>&ndash; осуществление гражданско-правовых отношений;</p>\r\n\r\n<p>&ndash; для идентификации пользователей (посетителей) сайта, для связи с пользователем, в том числе направление уведомлений, запросов и информации, касающихся использования услуг компании, исполнения соглашений и договоров, а также обработки запросов и заявок от пользователя.</p>\r\n\r\n<p>- обезличивания персональных данных для получения обезличенных статистических данных, которые передаются третьему лицу для проведения исследований, выполнения работ или оказания услуг по поручению организации.</p>\r\n\r\n<p>3.2.3. Категории субъектов персональных данных.</p>\r\n\r\n<p>Обрабатываются персональные данные следующих субъектов персональных</p>\r\n\r\n<p>данных:</p>\r\n\r\n<p>&ndash; физические лица, состоящие с Обществом в трудовых отношениях;</p>\r\n\r\n<p>&ndash; физические лица, уволившиеся из Общества;</p>\r\n\r\n<p>&ndash; физические лица, являющиеся кандидатами на работу;</p>\r\n\r\n<p>&ndash; физические лица, состоящие с Обществом в гражданско-правовых</p>\r\n\r\n<p>отношениях;</p>\r\n\r\n<p>&ndash; физические лица, являющиеся пользователями сайта организации.</p>\r\n\r\n<p>3.2.4. Персональные данные, обрабатываемые Оператором:</p>\r\n\r\n<p>&ndash; данные, полученные при осуществлении трудовых отношений;</p>\r\n\r\n<p>&ndash; данные, полученные для осуществления отбора кандидатов на работу;</p>\r\n\r\n<p>&ndash; данные, полученные при осуществлении гражданско-правовых отношений;</p>\r\n\r\n<p>&ndash; данные, полученные от пользователей сайта организации.</p>\r\n\r\n<p>3.2.5. Обработка персональных данных ведется:</p>\r\n\r\n<p>&ndash; с использованием средств автоматизации;</p>\r\n\r\n<p>&ndash; без использования средств автоматизации.</p>\r\n\r\n<p>3.3. Хранение персональных данных.</p>\r\n\r\n<p>3.3.1. Персональные данные субъектов могут быть получены, проходить дальнейшую обработку и передаваться на хранение как на бумажных носителях, так и в электронном виде.</p>\r\n\r\n<p>3.3.2. Персональные данные, зафиксированные на бумажных носителях, хранятся в запираемых шкафах либо в запираемых помещениях с ограниченным правом доступа.</p>\r\n\r\n<p>3.3.3. Персональные данные субъектов, обрабатываемые с использованием средств автоматизации в разных целях, хранятся в разных папках.</p>\r\n\r\n<p>3.3.4. Не допускается хранение и размещение документов, содержащих персональных данных, в открытых электронных каталогах (файлообменниках) в ИСПД.</p>\r\n\r\n<p>3.3.5. Хранение персональных данных в форме, позволяющей определить субъекта персональных данных, осуществляется не дольше, чем этого требуют цели их обработки, и они подлежат уничтожению по достижении целей обработки или в случае утраты необходимости в их достижении.</p>\r\n\r\n<p>3.4. Уничтожение персональных данных.</p>\r\n\r\n<p>3.4.1. Уничтожение документов (носителей), содержащих персональных данных, производится путем сожжения, дробления (измельчения), химического разложения, превращения в бесформенную массу или порошок. Для уничтожения бумажных документов допускается применение шредера.</p>\r\n\r\n<p>3.4.2. Персональные данные на электронных носителях уничтожаются путем стирания или форматирования носителя.</p>\r\n\r\n<p>3.4.3. Факт уничтожения персональных данных подтверждается документально актом об уничтожении носителей.</p>\r\n\r\n<p>3.5. Передача персональных данных.</p>\r\n\r\n<p>3.5.1. Оператор передает персональные данные третьим лицам в следующих случаях:</p>\r\n\r\n<p>&ndash; субъект выразил свое согласие на такие действия;</p>\r\n\r\n<p>&ndash; передача предусмотрена российским или иным применимым законодательством в рамках установленной законодательством процедуры.</p>\r\n\r\n<p>3.5.2. Перечень лиц, которым передаются персональные данные.</p>\r\n\r\n<p>&ndash; Пенсионный фонд РФ для учета (на законных основаниях);</p>\r\n\r\n<p>&ndash; налоговые органы РФ (на законных основаниях);</p>\r\n\r\n<p>&ndash; Фонд социального страхования РФ (на законных основаниях);</p>\r\n\r\n<p>&ndash; территориальный фонд обязательного медицинского страхования (на законных основаниях);</p>\r\n\r\n<p>&ndash; страховые медицинские организации по обязательному и добровольному медицинскому страхованию (на законных основаниях);</p>\r\n\r\n<p>&ndash; банки для начисления заработной платы (на основании договора);</p>\r\n\r\n<p>&ndash; органы МВД России в случаях, установленных законодательством;</p>\r\n\r\n<p>&ndash; обезличенные персональные данные Пользователей сайта организации передаются контрагентам организации.</p>\r\n\r\n<p><strong>4. Защита персональных данных</strong></p>\r\n\r\n<p>4.1. В соответствии с требованиями нормативных документов Оператором создана система защиты персональных данных (СЗПД), состоящая из подсистем правовой, организационной и технической защиты.</p>\r\n\r\n<p>4.2. Подсистема правовой защиты представляет собой комплекс правовых, организационно-распорядительных и нормативных документов, обеспечивающих создание, функционирование и совершенствование СЗПД.</p>\r\n\r\n<p>4.3. Подсистема организационной защиты включает в себя организацию структуры управления СЗПД, разрешительной системы, защиты информации при работе с сотрудниками, партнерами и сторонними лицами.</p>\r\n\r\n<p>4.4. Подсистема технической защиты включает в себя комплекс технических, программных, программно-аппаратных средств, обеспечивающих защиту персональных данных.</p>\r\n\r\n<p>4.4. Основными мерами защиты персональных данных, используемыми Оператором, являются:</p>\r\n\r\n<p>4.5.1. Назначение лица, ответственного за обработку персональных данных, которое осуществляет организацию обработки персональных данных, обучение и инструктаж, внутренний контроль за соблюдением учреждением и его работниками требований к защите персональных данных.</p>\r\n\r\n<p>4.5.2. Определение актуальных угроз безопасности персональных данных при их обработке в ИСПД и разработка мер и мероприятий по защите персональных данных.</p>\r\n\r\n<p>4.5.3. Разработка политики в отношении обработки персональных данных.</p>\r\n\r\n<p>4.5.4. Установление правил доступа к персональных данных, обрабатываемым в ИСПД, а также обеспечение регистрации и учета всех действий, совершаемых с персональными данными в ИСПД.</p>\r\n\r\n<p>4.5.5. Установление индивидуальных паролей доступа сотрудников в информационную систему в соответствии с их производственными обязанностями.</p>\r\n\r\n<p>4.5.6. Применение прошедших в установленном порядке процедуру оценки соответствия средств защиты информации.</p>\r\n\r\n<p>4.5.7. Сертифицированное антивирусное программное обеспечение с регулярно обновляемыми базами.</p>\r\n\r\n<p>4.5.8. Соблюдение условий, обеспечивающих сохранность персональных данных и исключающих несанкционированный к ним доступ.</p>\r\n\r\n<p>4.5.9. Обнаружение фактов несанкционированного доступа к персональным данным и принятие мер.</p>\r\n\r\n<p>4.5.10. Восстановление персональных данных, модифицированных или уничтоженных вследствие несанкционированного доступа к ним.</p>\r\n\r\n<p>4.5.11. Обучение работников Оператора, непосредственно осуществляющих обработку персональных данных, положениям законодательства РФ о персональных данных, в том числе требованиям к защите персональных данных, документам, определяющим политику Оператора в отношении обработки персональных данных, локальным актам по вопросам обработки персональных данных.</p>\r\n\r\n<p>4.5.12. Осуществление внутреннего контроля и аудита.</p>\r\n\r\n<p><strong>5. Основные права субъекта персональных данных и обязанности</strong></p>\r\n\r\n<p><strong>Оператора</strong></p>\r\n\r\n<p>5.1. Основные права субъекта персональных данных.</p>\r\n\r\n<p>Субъект имеет право на доступ к его персональным данным и следующим сведениям:</p>\r\n\r\n<p>&ndash; подтверждение факта обработки персональных данных Оператором;</p>\r\n\r\n<p>&ndash; правовые основания и цели обработки персональных данных;</p>\r\n\r\n<p>&ndash; цели и применяемые Оператором способы обработки персональных данных;</p>\r\n\r\n<p>&ndash; наименование и место нахождения Оператора, сведения о лицах (за исключением работников Оператора), которые имеют доступ к персональных данных или которым могут быть раскрыты персональные данные на основании договора с Оператором или на основании федерального закона;</p>\r\n\r\n<p>&ndash; сроки обработки персональных данных, в том числе сроки их хранения;</p>\r\n\r\n<p>&ndash; порядок осуществления субъектом персональных данных прав, предусмотренных Федеральным законом;</p>\r\n\r\n<p>&ndash; наименование или фамилия, имя, отчество и адрес лица, осуществляющего обработку персональных данных по поручению Оператора, если обработка поручена или будет поручена такому лицу;</p>\r\n\r\n<p>&ndash; обращение к Оператору и направление ему запросов;</p>\r\n\r\n<p>&ndash; обжалование действий или бездействия Оператора.</p>\r\n\r\n<p>5.2. Обязанности Оператора.</p>\r\n\r\n<p>Оператор обязан:</p>\r\n\r\n<p>&ndash; при сборе персональных данных предоставить информацию об обработке персональных данных;</p>\r\n\r\n<p>&ndash; в случаях если персональные данные были получены не от субъекта персональных данных, уведомить субъекта;</p>\r\n\r\n<p>&ndash; при отказе в предоставлении персональных данных субъекту разъясняются последствия такого отказа;</p>\r\n\r\n<p>&ndash; опубликовать или иным образом обеспечить неограниченный доступ к документу, определяющему его политику в отношении обработки персональных данных, к сведениям о реализуемых требованиях к защите персональных данных;</p>\r\n\r\n<p>&ndash; принимать необходимые правовые, организационные и технические меры или обеспечивать их принятие для защиты персональных данных от неправомерного или случайного доступа к ним, уничтожения, изменения, блокирования, копирования, предоставления, распространения персональных данных, а также от иных неправомерных действий в отношении персональных</p>\r\n\r\n<p>данных;</p>\r\n\r\n<p>&ndash; давать ответы на запросы и обращения субъектов персональных данных, их представителей и уполномоченного органа по защите прав субъектов персональных данных.</p>\r\n', '', '', '', '', '', '', 'ПОЛИТИК ОРГАНИЗАЦИЯ ПО ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ', '1 ОБЩИЙ ПОЛОЖЕНИЕ NBSP 1 1 НАСТОЯЩИЙ ПОЛИТИК В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДАЛЕЕ NDASH ПОЛИТИК СОСТАВИТЬ В СООТВЕТСТВИЕ С ПУНКТ 2 СТАТЬЯ 18 1 ФЕДЕРАЛЬНЫЙ ЗАКОН LAQUO О ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO ?? 152-ФЗ ОТ 27 ИЮЛЬ 2006 Г А ТАКЖЕ ИНОЙ НОРМАТИВНЫЙ ПРАВОВОЙ АКТ РОССИЙСКИЙ ФЕДЕРАЦИЯ В ОБЛАСТЬ ЗАЩИТА И ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ДЕЙСТВОВАТЬ В ОТНОШЕНИЕ ВЕСЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДАЛЕЕ NDASH ДАННЫЙ КОТОРЫЙ ОРГАНИЗАЦИЯ ДАЛЕЕ NDASH ОПЕРАТОР ОБЩЕСТВО МОЖЕТ ПОЛУЧИТЬ ОТ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЯВЛЯТЬСЯ СТОРОНОЙ ПО ГРАЖДАНСКО-ПРАВОВОЙ ДОГОВОР ОТ ПОЛЬЗОВАТЕЛЬ СЕТЬ ИНТЕРНЕТ ДАЛЕЕ NDASH ПОЛЬЗОВАТЕЛЬ ВО ВРЕМЯ ИСПОЛЬЗОВАНИЕ ИМЯ ЛЮБОЙ ИЗ САЙТ СЕРВИС СЛУЖБА ПРОГРАММА ПРОДУКТ ИЛИ УСЛУГА ООО LAQUO RAQUO А ТАКЖЕ ОТ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СОСТОЯТЬ С ОПЕРАТОР В ОТНОШЕНИЕ РЕГУЛИРУЕМЫЙ ТРУДОВОЙ ЗАКОНОДАТЕЛЬСТВО ДАЛЕЕ NDASH РАБОТНИК 1 2 ОПЕРАТОР ОБЕСПЕЧИВАТЬ ЗАЩИТА ОБРАБАТЫВАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОТ НЕСАНКЦИОНИРОВАННЫЙ ДОСТУП И РАЗГЛАШЕНИЕ НЕПРАВОМЕРНЫЙ ИСПОЛЬЗОВАНИЕ ИЛИ УТРАТА В СООТВЕТСТВИЕ С ТРЕБОВАНИЕ ФЕДЕРАЛЬНЫЙ ЗАКОН ОТ 27 ИЮЛЬ 2006 Г ?? 152-ФЗ LAQUO О ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO 1 3 ОПЕРАТОР ВПРАВЕ ВНОСИТЬ ИЗМЕНЕНИЕ В НАСТОЯЩИЙ ПОЛИТИК ПРИ ВНЕСЕНИЕ ИЗМЕНЕНИЕ В ЗАГОЛОВОК ПОЛИТИК УКАЗЫВАТЬСЯ ДАТА ПОСЛЕДНИЙ ОБНОВЛЕНИЕ РЕДАКЦИЯ НОВЫЙ РЕДАКЦИЯ ПОЛИТИК ВСТУПАТЬ В СИЛА С МОМЕНТ ЕЕ РАЗМЕЩЕНИЕ НА САЙТ ЕСЛИ ИНОЙ НЕ ПРЕДУСМОТРЕТЬ НОВЫЙ РЕДАКЦИЯ ПОЛИТИК 2 ТЕРМИН И ПРИНЯТЬ СОКРАЩЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ЛЮБОЙ ИНФОРМАЦИЯ ОТНОСИТЬСЯ К ПРЯМО ИЛИ КОСВЕННО ОПРЕДЕЛЕННЫЙ ИЛИ ОПРЕДЕЛЯТЬ ФИЗИЧЕСКИЙ ЛИЦО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ЛЮБОЙ ДЕЙСТВИЕ ОПЕРАЦИЯ ИЛИ СОВОКУПНОСТЬ ДЕЙСТВИЕ ОПЕРАЦИЯ СОВЕРШАТЬ С ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ ИЛИ БЕЗ ИСПОЛЬЗОВАНИЕ ТАКОЙ СРЕДСТВО С ПЕРСОНАЛЬНЫЙ ДАННЫЙ ВКЛЮЧАЯ СБОР ЗАПИСЬ СИСТЕМАТИЗАЦИЯ НАКОПЛЕНИЕ ХРАНЕНИЕ УТОЧНЕНИЕ ОБНОВЛЕНИЕ ИЗМЕНЕНИЕ ИЗВЛЕЧЕНИЕ ИСПОЛЬЗОВАНИЕ ПЕРЕДАЧА РАСПРОСТРАНЕНИЕ ПРЕДОСТАВЛЕНИЕ ДОСТУП ОБЕЗЛИЧИВАНИЕ БЛОКИРОВАНИЕ УДАЛЕНИЕ УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ АВТОМАТИЗИРОВАННЫЙ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ С ПОМОЩЬ СРЕДСТВО ВЫЧИСЛИТЕЛЬНЫЙ ТЕХНИК ИНФОРМАЦИОННЫЙ СИСТЕМА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИСПД NDASH СОВОКУПНОСТЬ СОДЕРЖАТЬСЯ В БАЗА ДАННЫЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ОБЕСПЕЧИВАТЬ ИХ ОБРАБОТКА ИНФОРМАЦИОННЫЙ ТЕХНОЛОГИЯ И ТЕХНИЧЕСКИЙ СРЕДСТВО ПЕРСОНАЛЬНЫЙ ДАННЫЙ СДЕЛАТЬ ОБЩЕДОСТУПНЫЙ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОСТУП НЕОГРАНИЧЕННЫЙ КРУГ ЛИЦО К КОТОРЫЙ ПРЕДОСТАВИТЬ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЛИБО ПО ЕГО ПРОСЬБА БЛОКИРОВАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ВРЕМЕННЫЙ ПРЕКРАЩЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЗА ИСКЛЮЧЕНИЕ СЛУЧАЙ ЕСЛИ ОБРАБОТКА НЕОБХОДИМЫЙ ДЛЯ УТОЧНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ДЕЙСТВИЕ В РЕЗУЛЬТАТ КОТОРЫЙ СТАНОВИТЬСЯ НЕВОЗМОЖНЫЙ ВОССТАНОВИТЬ СОДЕРЖАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ИНФОРМАЦИОННЫЙ СИСТЕМА ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ИЛИ В РЕЗУЛЬТАТ КОТОРЫЙ УНИЧТОЖАТЬСЯ МАТЕРИАЛЬНЫЙ НОСИТЕЛЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОПЕРАТОР NDASH ОРГАНИЗАЦИЯ САМОСТОЯТЕЛЬНО ИЛИ СОВМЕСТНО С ДРУГОЙ ЛИЦО ОРГАНИЗОВАТЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ А ТАКЖЕ ОПРЕДЕЛЯТЬ ЦЕЛЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОДЛЕЖАЩЕЕ ОБРАБОТКА ДЕЙСТВИЕ ОПЕРАЦИЯ СОВЕРШАТЬ С ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОПЕРАТОР ЯВЛЯТЬСЯ ООО LAQUO RAQUO РАСПОЛОЖИТЬ ПО АДРЕС 3 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 1 ПОЛУЧЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 1 1 ВСЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЛЕДОВАТЬ ПОЛУЧАТЬ ОТ САМЫЙ СУБЪЕКТ ЕСЛИ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ МОЖНО ПОЛУЧИТЬ ТОЛЬКО У ТРЕТИЙ СТОРОНА ТО СУБЪЕКТ ДОЛЖНЫЙ БЫТЬ УВЕДОМИТЬ ОБ ЭТО ИЛИ ОТ ОН ДОЛЖНЫЙ БЫТЬ ПОЛУЧИТЬ СОГЛАСИЕ 3 1 2 ОПЕРАТОР ДОЛЖНЫЙ СООБЩИТЬ СУБЪЕКТ О ЦЕЛЬ ПРЕДПОЛАГАТЬ ИСТОЧНИК И СПОСОБ ПОЛУЧЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ХАРАКТЕР ПОДЛЕЖАЩЕЕ ПОЛУЧЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПЕРЕЧЕНЬ ДЕЙСТВИЕ С ПЕРСОНАЛЬНЫЙ ДАННЫЙ СРОК В ТЕЧЕНИЕ КОТОРЫЙ ДЕЙСТВОВАТЬ СОГЛАСИЕ И ПОРЯДОК ЕГО ОТЗЫВ А ТАКЖЕ О ПОСЛЕДСТВИЕ ОТКАЗ СУБЪЕКТ ДАТЬ ПИСЬМЕННЫЙ СОГЛАСИЕ НА ИХ ПОЛУЧЕНИЕ 3 1 3 ДОКУМЕНТ СОДЕРЖАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СОЗДАВАТЬСЯ ПУТЬ NDASH КОПИРОВАНИЕ ОРИГИНАЛ ДОКУМЕНТ ПАСПОРТ ДОКУМЕНТ ОБ ОБРАЗОВАНИЕ СВИДЕТЕЛЬСТВО ИННА ПЕНСИОННЫЙ СВИДЕТЕЛЬСТВО И ДР NDASH ВНЕСЕНИЕ СВЕДЕНИЕ В УЧЕТНЫЙ ФОРМА NDASH ПОЛУЧЕНИЕ ОРИГИНАЛ НЕОБХОДИМЫЙ ДОКУМЕНТ ТРУДОВОЙ КНИЖКА МЕДИЦИНСКИЙ ЗАКЛЮЧЕНИЕ ХАРАКТЕРИСТИКА И ДР 3 2 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 2 1 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОСУЩЕСТВЛЯТЬСЯ NDASH С СОГЛАСИЕ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ НА ОБРАБОТКА ЕГО ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH В СЛУЧАЙ КОГДА ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ НЕОБХОДИМЫЙ ДЛЯ ОСУЩЕСТВЛЕНИЕ И ВЫПОЛНЕНИЕ ВОЗЛОЖИТЬ ЗАКОНОДАТЕЛЬСТВО РФ ФУНКЦИЯ ПОЛНОМОЧИЕ И ОБЯЗАННОСТЬ NDASH В СЛУЧАЙ КОГДА ОСУЩЕСТВЛЯТЬСЯ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОСТУП НЕОГРАНИЧЕННЫЙ КРУГ ЛИЦО К КОТОРЫЙ ПРЕДОСТАВИТЬ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЛИБО ПО ЕГО ПРОСЬБА ДАЛЕЕ NDASH ПЕРСОНАЛЬНЫЙ ДАННЫЙ СДЕЛАТЬ ОБЩЕДОСТУПНЫЙ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 2 2 ЦЕЛЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ОСУЩЕСТВЛЕНИЕ ТРУДОВОЙ ОТНОШЕНИЕ NDASH ОСУЩЕСТВЛЕНИЕ ГРАЖДАНСКО-ПРАВОВОЙ ОТНОШЕНИЕ NDASH ДЛЯ ИДЕНТИФИКАЦИЯ ПОЛЬЗОВАТЕЛЬ ПОСЕТИТЕЛЬ САЙТ ДЛЯ СВЯЗЬ С ПОЛЬЗОВАТЕЛЬ В ТОМ ЧИСЛО НАПРАВЛЕНИЕ УВЕДОМЛЕНИЕ ЗАПРОС И ИНФОРМАЦИЯ КАСАТЬСЯ ИСПОЛЬЗОВАНИЕ УСЛУГА КОМПАНИЯ ИСПОЛНЕНИЕ СОГЛАШЕНИЕ И ДОГОВОР А ТАКЖЕ ОБРАБОТКА ЗАПРОС И ЗАЯВКА ОТ ПОЛЬЗОВАТЕЛЬ - ОБЕЗЛИЧИВАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДЛЯ ПОЛУЧЕНИЕ ОБЕЗЛИЧИТЬ СТАТИСТИЧЕСКИЙ ДАННЫЙ КОТОРЫЙ ПЕРЕДАВАТЬСЯ ТРЕТИЙ ЛИЦО ДЛЯ ПРОВЕДЕНИЕ ИССЛЕДОВАНИЕ ВЫПОЛНЕНИЕ РАБОТА ИЛИ ОКАЗАНИЕ УСЛУГА ПО ПОРУЧЕНИЕ ОРГАНИЗАЦИЯ 3 2 3 КАТЕГОРИЯ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБАТЫВАТЬСЯ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЛЕДУЮЩИЙ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ФИЗИЧЕСКИЙ ЛИЦО СОСТОЯТЬ С ОБЩЕСТВО В ТРУДОВОЙ ОТНОШЕНИЕ NDASH ФИЗИЧЕСКИЙ ЛИЦО УВОЛИТЬСЯ ИЗ ОБЩЕСТВО NDASH ФИЗИЧЕСКИЙ ЛИЦО ЯВЛЯТЬСЯ КАНДИДАТ НА РАБОТА NDASH ФИЗИЧЕСКИЙ ЛИЦО СОСТОЯТЬ С ОБЩЕСТВО В ГРАЖДАНСКО-ПРАВОВОЙ ОТНОШЕНИЕ NDASH ФИЗИЧЕСКИЙ ЛИЦО ЯВЛЯТЬСЯ ПОЛЬЗОВАТЕЛЬ САЙТ ОРГАНИЗАЦИЯ 3 2 4 ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБАТЫВАТЬ ОПЕРАТОР NDASH ДАННЫЙ ПОЛУЧИТЬ ПРИ ОСУЩЕСТВЛЕНИЕ ТРУДОВОЙ ОТНОШЕНИЕ NDASH ДАННЫЙ ПОЛУЧИТЬ ДЛЯ ОСУЩЕСТВЛЕНИЕ ОТБОР КАНДИДАТ НА РАБОТА NDASH ДАННЫЙ ПОЛУЧИТЬ ПРИ ОСУЩЕСТВЛЕНИЕ ГРАЖДАНСКО-ПРАВОВОЙ ОТНОШЕНИЕ NDASH ДАННЫЙ ПОЛУЧИТЬ ОТ ПОЛЬЗОВАТЕЛЬ САЙТ ОРГАНИЗАЦИЯ 3 2 5 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ВЕСТИСЬ NDASH С ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ NDASH БЕЗ ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ 3 3 ХРАНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 3 1 ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ МОЧЬ БЫТЬ ПОЛУЧИТЬ ПРОХОДИТЬ ДАЛЬНЕЙШИЙ ОБРАБОТКА И ПЕРЕДАВАТЬСЯ НА ХРАНЕНИЕ КАК НА БУМАЖНЫЙ НОСИТЕЛЬ ТАК И В ЭЛЕКТРОННЫЙ ВИД 3 3 2 ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЗАФИКСИРОВАТЬ НА БУМАЖНЫЙ НОСИТЕЛЬ ХРАНИТЬСЯ В ЗАПИРАТЬ ШКАФ ЛИБО В ЗАПИРАТЬ ПОМЕЩЕНИЕ С ОГРАНИЧЕННЫЙ ПРАВО ДОСТУП 3 3 3 ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ ОБРАБАТЫВАТЬ С ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ В РАЗНЫЙ ЦЕЛЬ ХРАНИТЬСЯ В РАЗНЫЙ ПАПКА 3 3 4 НЕ ДОПУСКАТЬСЯ ХРАНЕНИЕ И РАЗМЕЩЕНИЕ ДОКУМЕНТ СОДЕРЖАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ОТКРЫТЫЙ ЭЛЕКТРОННЫЙ КАТАЛОГ ФАЙЛООБМЕННИКА В ИСПД 3 3 5 ХРАНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ФОРМА ПОЗВОЛЯТЬ ОПРЕДЕЛИТЬ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОСУЩЕСТВЛЯТЬСЯ НЕ ДОЛГИЙ ЧЕМ ЭТО ТРЕБОВАТЬ ЦЕЛЬ ИХ ОБРАБОТКА И ОНИ ПОДЛЕЖАТЬ УНИЧТОЖЕНИЕ ПО ДОСТИЖЕНИЕ ЦЕЛЬ ОБРАБОТКА ИЛИ В СЛУЧАЙ УТРАТА НЕОБХОДИМОСТЬ В ИХ ДОСТИЖЕНИЕ 3 4 УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 4 1 УНИЧТОЖЕНИЕ ДОКУМЕНТ НОСИТЕЛЬ СОДЕРЖАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРОИЗВОДИТЬСЯ ПУТЬ СОЖЖЕНИЕ ДРОБЛЕНИЕ ИЗМЕЛЬЧЕНИЕ ХИМИЧЕСКИЙ РАЗЛОЖЕНИЕ ПРЕВРАЩЕНИЕ В БЕСФОРМЕННЫЙ МАССА ИЛИ ПОРОШОК ДЛЯ УНИЧТОЖЕНИЕ БУМАЖНЫЙ ДОКУМЕНТ ДОПУСКАТЬСЯ ПРИМЕНЕНИЕ ШРЕДЕР 3 4 2 ПЕРСОНАЛЬНЫЙ ДАННЫЙ НА ЭЛЕКТРОННЫЙ НОСИТЕЛЬ УНИЧТОЖАТЬСЯ ПУТЬ СТИРАНИЕ ИЛИ ФОРМАТИРОВАНИЕ НОСИТЕЛЬ 3 4 3 ФАКТ УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОДТВЕРЖДАТЬСЯ ДОКУМЕНТАЛЬНЫЙ АКТ ОБ УНИЧТОЖЕНИЕ НОСИТЕЛЬ 3 5 ПЕРЕДАЧА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 5 1 ОПЕРАТОР ПЕРЕДАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ТРЕТИЙ ЛИЦО В СЛЕДУЮЩИЙ СЛУЧАЙ NDASH СУБЪЕКТ ВЫРАЗИТЬ СВОЕ СОГЛАСИЕ НА ТАКОЙ ДЕЙСТВИЕ NDASH ПЕРЕДАЧА ПРЕДУСМОТРЕТЬ РОССИЙСКИЙ ИЛИ ИНОЙ ПРИМЕНИМЫЙ ЗАКОНОДАТЕЛЬСТВО В РАМКА УСТАНОВЛЕННЫЙ ЗАКОНОДАТЕЛЬСТВО ПРОЦЕДУРА 3 5 2 ПЕРЕЧЕНЬ ЛИЦО КОТОРЫЙ ПЕРЕДАВАТЬСЯ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ПЕНСИОННЫЙ ФОНД РФ ДЛЯ УЧЕТ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH НАЛОГОВЫЙ ОРГАН РФ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH ФОНД СОЦИАЛЬНЫЙ СТРАХОВАНИЕ РФ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH ТЕРРИТОРИАЛЬНЫЙ ФОНД ОБЯЗАТЕЛЬНЫЙ МЕДИЦИНСКИЙ СТРАХОВАНИЕ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH СТРАХОВОЙ МЕДИЦИНСКИЙ ОРГАНИЗАЦИЯ ПО ОБЯЗАТЕЛЬНЫЙ И ДОБРОВОЛЬНЫЙ МЕДИЦИНСКИЙ СТРАХОВАНИЕ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH БАНКА ДЛЯ НАЧИСЛЕНИЕ ЗАРАБОТНЫЙ ПЛАТ НА ОСНОВАНИЕ ДОГОВОР NDASH ОРГАН МВД РОССИЯ В СЛУЧАЙ УСТАНОВЛЕННЫЙ ЗАКОНОДАТЕЛЬСТВО NDASH ОБЕЗЛИЧИТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОЛЬЗОВАТЕЛЬ САЙТ ОРГАНИЗАЦИЯ ПЕРЕДАВАТЬСЯ КОНТРАГЕНТ ОРГАНИЗАЦИЯ 4 ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 1 В СООТВЕТСТВИЕ С ТРЕБОВАНИЕ НОРМАТИВНЫЙ ДОКУМЕНТ ОПЕРАТОР СОЗДАТЬ СИСТЕМА ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЗПД СОСТОЯТЬ ИЗ ПОДСИСТЕМА ПРАВОВОЙ ОРГАНИЗАЦИОННЫЙ И ТЕХНИЧЕСКИЙ ЗАЩИТА 4 2 ПОДСИСТЕМА ПРАВОВОЙ ЗАЩИТА ПРЕДСТАВЛЯТЬ СЕБЯ КОМПЛЕКС ПРАВОВОЙ ОРГАНИЗАЦИОННО-РАСПОРЯДИТЕЛЬНЫЙ И НОРМАТИВНЫЙ ДОКУМЕНТ ОБЕСПЕЧИВАТЬ СОЗДАНИЕ ФУНКЦИОНИРОВАНИЕ И СОВЕРШЕНСТВОВАНИЕ СЗПД 4 3 ПОДСИСТЕМА ОРГАНИЗАЦИОННЫЙ ЗАЩИТА ВКЛЮЧАТЬ В СЕБЯ ОРГАНИЗАЦИЯ СТРУКТУРА УПРАВЛЕНИЕ СЗПД РАЗРЕШИТЕЛЬНЫЙ СИСТЕМА ЗАЩИТА ИНФОРМАЦИЯ ПРИ РАБОТА С СОТРУДНИК ПАРТНЕР И СТОРОННИЙ ЛИЦО 4 4 ПОДСИСТЕМА ТЕХНИЧЕСКИЙ ЗАЩИТА ВКЛЮЧАТЬ В СЕБЯ КОМПЛЕКС ТЕХНИЧЕСКИЙ ПРОГРАММНЫЙ ПРОГРАММНО-АППАРАТНЫЙ СРЕДСТВО ОБЕСПЕЧИВАТЬ ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 4 ОСНОВНЫЙ МЕРА ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИСПОЛЬЗОВАТЬ ОПЕРАТОР ЯВЛЯТЬСЯ 4 5 1 НАЗНАЧЕНИЕ ЛИЦО ОТВЕТСТВЕННЫЙ ЗА ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ КОТОРЫЙ ОСУЩЕСТВЛЯТЬ ОРГАНИЗАЦИЯ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБУЧЕНИЕ И ИНСТРУКТАЖ ВНУТРЕННИЙ КОНТРОЛЬ ЗА СОБЛЮДЕНИЕ УЧРЕЖДЕНИЕ И ЕГО РАБОТНИК ТРЕБОВАНИЕ К ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 2 ОПРЕДЕЛЕНИЕ АКТУАЛЬНЫЙ УГРОЗА БЕЗОПАСНОСТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРИ ИХ ОБРАБОТКА В ИСПД И РАЗРАБОТКА МЕРА И МЕРОПРИЯТИЕ ПО ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 3 РАЗРАБОТКА ПОЛИТИК В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 4 УСТАНОВЛЕНИЕ ПРАВИТЬ ДОСТУП К ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБАТЫВАТЬ В ИСПД А ТАКЖЕ ОБЕСПЕЧЕНИЕ РЕГИСТРАЦИЯ И УЧЕТ ВЕСЬ ДЕЙСТВИЕ СОВЕРШАТЬ С ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ИСПД 4 5 5 УСТАНОВЛЕНИЕ ИНДИВИДУАЛЬНЫЙ ПАРОЛЬ ДОСТУП СОТРУДНИК В ИНФОРМАЦИОННЫЙ СИСТЕМА В СООТВЕТСТВИЕ С ИХ ПРОИЗВОДСТВЕННЫЙ ОБЯЗАННОСТЬ 4 5 6 ПРИМЕНЕНИЕ ПРОШЕДШЕЕ В УСТАНОВЛЕННЫЙ ПОРЯДОК ПРОЦЕДУРА ОЦЕНКА СООТВЕТСТВИЕ СРЕДСТВО ЗАЩИТА ИНФОРМАЦИЯ 4 5 7 СЕРТИФИЦИРОВАННЫЙ АНТИВИРУСНЫЙ ПРОГРАММНЫЙ ОБЕСПЕЧЕНИЕ С РЕГУЛЯРНО ОБНОВЛЯТЬ БАЗА 4 5 8 СОБЛЮДЕНИЕ УСЛОВИЕ ОБЕСПЕЧИВАТЬ СОХРАННОСТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ИСКЛЮЧАТЬ НЕСАНКЦИОНИРОВАННЫЙ К ОН ДОСТУП 4 5 9 ОБНАРУЖЕНИЕ ФАКТ НЕСАНКЦИОНИРОВАННЫЙ ДОСТУП К ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ПРИНЯТИЕ МЕРА 4 5 10 ВОССТАНОВЛЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ МОДИФИЦИРОВАННЫЙ ИЛИ УНИЧТОЖИТЬ ВСЛЕДСТВИЕ НЕСАНКЦИОНИРОВАННЫЙ ДОСТУП К ОН 4 5 11 ОБУЧЕНИЕ РАБОТНИК ОПЕРАТОР НЕПОСРЕДСТВЕННО ОСУЩЕСТВЛЯТЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОЛОЖЕНИЕ ЗАКОНОДАТЕЛЬСТВО РФ О ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ТОМ ЧИСЛО ТРЕБОВАНИЕ К ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОКУМЕНТ ОПРЕДЕЛЯТЬ ПОЛИТИК ОПЕРАТОР В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЛОКАЛЬНЫЙ АКТАМ ПО ВОПРОС ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 12 ОСУЩЕСТВЛЕНИЕ ВНУТРЕННИЙ КОНТРОЛЬ И АУДИТ 5 ОСНОВНЫЙ ПРАВО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ОБЯЗАННОСТЬ ОПЕРАТОР 5 1 ОСНОВНЫЙ ПРАВО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ ИМЕТЬ ПРАВО НА ДОСТУП К ЕГО ПЕРСОНАЛЬНЫЙ ДАННЫЙ И СЛЕДУЮЩИЙ СВЕДЕНИЕ NDASH ПОДТВЕРЖДЕНИЕ ФАКТ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОПЕРАТОР NDASH ПРАВОВОЙ ОСНОВАНИЕ И ЦЕЛЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ЦЕЛЬ И ПРИМЕНЯТЬ ОПЕРАТОР СПОСОБ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH НАИМЕНОВАНИЕ И МЕСТО НАХОЖДЕНИЕ ОПЕРАТОР СВЕДЕНИЕ О ЛИЦО ЗА ИСКЛЮЧЕНИЕ РАБОТНИК ОПЕРАТОР КОТОРЫЙ ИМЕТЬ ДОСТУП К ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИЛИ КОТОРЫЙ МОЧЬ БЫТЬ РАСКРЫТЫЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ НА ОСНОВАНИЕ ДОГОВОР С ОПЕРАТОР ИЛИ НА ОСНОВАНИЕ ФЕДЕРАЛЬНЫЙ ЗАКОН NDASH СРОК ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ТОМ ЧИСЛО СРОК ИХ ХРАНЕНИЕ NDASH ПОРЯДОК ОСУЩЕСТВЛЕНИЕ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРАВО ПРЕДУСМОТРЕННЫЙ ФЕДЕРАЛЬНЫЙ ЗАКОН NDASH НАИМЕНОВАНИЕ ИЛИ ФАМИЛИЯ ИМЯ ОТЧЕСТВО И АДРЕС ЛИЦО ОСУЩЕСТВЛЯТЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПО ПОРУЧЕНИЕ ОПЕРАТОР ЕСЛИ ОБРАБОТКА ПОРУЧИТЬ ИЛИ БЫТЬ ПОРУЧИТЬ ТАКОЙ ЛИЦО NDASH ОБРАЩЕНИЕ К ОПЕРАТОР И НАПРАВЛЕНИЕ ОН ЗАПРОС NDASH ОБЖАЛОВАНИЕ ДЕЙСТВИЕ ИЛИ БЕЗДЕЙСТВИЕ ОПЕРАТОР 5 2 ОБЯЗАННОСТЬ ОПЕРАТОР ОПЕРАТОР ОБЯЗАТЬ NDASH ПРИ СБОР ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРЕДОСТАВИТЬ ИНФОРМАЦИЯ ОБ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH В СЛУЧАЙ ЕСЛИ ПЕРСОНАЛЬНЫЙ ДАННЫЙ БЫЛЬ ПОЛУЧИТЬ НЕ ОТ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ УВЕДОМИТЬ СУБЪЕКТ NDASH ПРИ ОТКАЗ В ПРЕДОСТАВЛЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ РАЗЪЯСНЯТЬСЯ ПОСЛЕДСТВИЕ ТАКОЙ ОТКАЗ NDASH ОПУБЛИКОВАТЬ ИЛИ ИНОЙ ОБРАЗ ОБЕСПЕЧИТЬ НЕОГРАНИЧЕННЫЙ ДОСТУП К ДОКУМЕНТ ОПРЕДЕЛЯТЬ ЕГО ПОЛИТИК В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ К СВЕДЕНИЕ О РЕАЛИЗОВАТЬ ТРЕБОВАНИЕ К ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ПРИНИМАТЬ НЕОБХОДИМЫЙ ПРАВОВОЙ ОРГАНИЗАЦИОННЫЙ И ТЕХНИЧЕСКИЙ МЕРА ИЛИ ОБЕСПЕЧИВАТЬ ИХ ПРИНЯТИЕ ДЛЯ ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОТ НЕПРАВОМЕРНЫЙ ИЛИ СЛУЧАЙНЫЙ ДОСТУП К ОН УНИЧТОЖЕНИЕ ИЗМЕНЕНИЕ БЛОКИРОВАНИЕ КОПИРОВАНИЕ ПРЕДОСТАВЛЕНИЕ РАСПРОСТРАНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ А ТАКЖЕ ОТ ИНОЙ НЕПРАВОМЕРНЫЙ ДЕЙСТВИЕ В ОТНОШЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ДАВАТЬ ОТВЕТ НА ЗАПРОС И ОБРАЩЕНИЕ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИХ ПРЕДСТАВИТЕЛЬ И УПОЛНОМОЧЕННЫЙ ОРГАН ПО ЗАЩИТА ПРАВО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ', 0, 0),

(5, 0, 'Согласие посетителя сайта на обработку персональных данных', '', '', '', '<p>1. Выражаю ООО &laquo;&raquo; и его представителям, свое согласие на автоматизированную и неавтоматизированную обработку моих персональных данных, включая сбор, систематизацию, накопление, хранение, уточнение (обновление, изменение), использование, распространение (в том числе передачу), обезличивание, блокирование, уничтожение следующих моих персональных данных, переданных мною лично ООО &laquo;&laquo;&raquo;&raquo;, в т.ч. с использованием интернет-сервисов</p>\r\n\r\n<p>Google analytics, Яндекс.Метрика:</p>\r\n\r\n<p>1.1.фамилия, имя, отчество,</p>\r\n\r\n<p>1.2.номер телефона (рабочий/личный сотовый/домашний);</p>\r\n\r\n<p>1.3. условия заказа в случае его оформления;</p>\r\n\r\n<p>1.4. адрес доставки в случае его указания при оформлении заказа;</p>\r\n\r\n<p>1.5. источник захода на сайт dimmtrans.ru (далее &ndash; сайт) и информация поискового или рекламного запроса;</p>\r\n\r\n<p>1.6. данные о пользовательском устройстве (среди которых разрешение, версия и другие атрибуты, характеризуемые пользовательское устройство, IP-адрес);</p>\r\n\r\n<p>1.7. информация о браузере пользователя (или иной программе, с помощью которой осуществляется доступ в сеть Интернет), технические характеристики оборудования и программного обеспечения, используемых пользователем;</p>\r\n\r\n<p>1.8. дата и время доступа;</p>\r\n\r\n<p>1.9. пользовательские клики, просмотры страниц, заполнения полей, показы и просмотры баннеров и видео;</p>\r\n\r\n<p>1.10. данные, характеризующие аудиторные сегменты;</p>\r\n\r\n<p>1.11. параметры сессии;</p>\r\n\r\n<p>1.12. данные о времени посещения;</p>\r\n\r\n<p>1.13. идентификатор пользователя, хранимый в cookie.</p>\r\n\r\n<p>2. ООО &laquo;&laquo;&raquo;&raquo; может обрабатывать мои персональные данные сроком 1 год в следующих целях:</p>\r\n\r\n<p>2.1.идентификации пользователей (посетителей) сайта ООО &laquo;&laquo;&raquo;&raquo;;</p>\r\n\r\n<p>2.2. связи со мной, в том числе направление уведомлений, запросов и информации, касающихся использования сайта, исполнения соглашений и договоров, а также обработки запросов и заявок от меня.</p>\r\n\r\n<p>2.3.обезличивания персональных данных для получения обезличенных статистических данных, которые передаются третьему лицу для проведения исследований, выполнения работ или оказания услуг по поручению ООО &laquo;&laquo;&raquo;&raquo;.</p>\r\n\r\n<p>3. Обработка (на бумажных носителях; в информационных системах персональных данных и без использования средств автоматизации, а также смешанным способом) ООО &laquo;&laquo;&raquo;&raquo; моих персональных данных должна осуществляться в соответствии с требованиями Федерального закона от 27.07.2006г. № 152-ФЗ &laquo;О персональных данных&raquo;, &laquo;Политикой защиты и обработки персональных данных&raquo; ООО &laquo;&laquo;&raquo;&raquo;.</p>\r\n\r\n<p>4. Также даю свое согласие на предоставление ООО &laquo;&laquo;&raquo;&raquo; моих персональных данных как посетителя сайта агентствам, с которыми он сотрудничает. ООО &laquo;&laquo;&raquo;&raquo; вправе осуществлять обработку моих персональных данных следующими способами: сбор, запись, систематизация, накопление, хранение, обновление, изменение, использование, передача (распространение, предоставление, доступ).</p>\r\n\r\n<p>5. Настоящее согласие, в том числе на передачу персональных данных третьей стороне в соответствии с п. 4 настоящего согласия, вступает в силу с момента моего перехода на сайт ООО &laquo;&laquo;&raquo;&raquo; и действует до дня отзыва в письменной форме.</p>\r\n\r\n<p>6. Настоящее согласие может быть отозвано письменным заявлением на e-mail.</p>\r\n', '', '', '', '', '', '', 'СОГЛАСИЕ ПОСЕТИТЕЛЬ САЙТ НА ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ', '1 ВЫРАЖАТЬ ООО LAQUO RAQUO И ЕГО ПРЕДСТАВИТЕЛЬ СВОЕ СОГЛАСИЕ НА АВТОМАТИЗИРОВАННЫЙ И НЕАВТОМАТИЗИРОВАННЫЙ ОБРАБОТКА МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ВКЛЮЧАЯ СБОР СИСТЕМАТИЗАЦИЯ НАКОПЛЕНИЕ ХРАНЕНИЕ УТОЧНЕНИЕ ОБНОВЛЕНИЕ ИЗМЕНЕНИЕ ИСПОЛЬЗОВАНИЕ РАСПРОСТРАНЕНИЕ В ТОМ ЧИСЛО ПЕРЕДАЧА ОБЕЗЛИЧИВАНИЕ БЛОКИРОВАНИЕ УНИЧТОЖЕНИЕ СЛЕДУЮЩИЙ МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПЕРЕДАТЬ Я ЛИЧНО ООО LAQUO LAQUO RAQUO RAQUO В Т Ч С ИСПОЛЬЗОВАНИЕ ИНТЕРНЕТ-СЕРВИС GOOGLE ANALYTICS ЯНДЕКС МЕТРИКА 1 1 ФАМИЛИЯ ИМЯ ОТЧЕСТВО 1 2 НОМЕР ТЕЛЕФОН РАБОЧИЙ ЛИЧНЫЙ СОТОВЫЙ ДОМАШНИЙ 1 3 УСЛОВИЕ ЗАКАЗ В СЛУЧАЙ ЕГО ОФОРМЛЕНИЕ 1 4 АДРЕС ДОСТАВКА В СЛУЧАЙ ЕГО УКАЗАНИЕ ПРИ ОФОРМЛЕНИЕ ЗАКАЗ 1 5 ИСТОЧНИК ЗАХОД НА САЙТ DIMMTRANS RU ДАЛЕЕ NDASH САЙТ И ИНФОРМАЦИЯ ПОИСКОВЫЙ ИЛИ РЕКЛАМНЫЙ ЗАПРОС 1 6 ДАННЫЙ О ПОЛЬЗОВАТЕЛЬСКИЙ УСТРОЙСТВО СРЕДИ КОТОРЫЙ РАЗРЕШЕНИЕ ВЕРСИЯ И ДРУГОЙ АТРИБУТ ХАРАКТЕРИЗОВАТЬ ПОЛЬЗОВАТЕЛЬСКИЙ УСТРОЙСТВО IP-АДРЕС 1 7 ИНФОРМАЦИЯ О БРАУЗЕР ПОЛЬЗОВАТЕЛЬ ИЛИ ИНОЙ ПРОГРАММА С ПОМОЩЬ КОТОРЫЙ ОСУЩЕСТВЛЯТЬСЯ ДОСТУП В СЕТЬ ИНТЕРНЕТ ТЕХНИЧЕСКИЙ ХАРАКТЕРИСТИКА ОБОРУДОВАНИЕ И ПРОГРАММНЫЙ ОБЕСПЕЧЕНИЕ ИСПОЛЬЗОВАТЬ ПОЛЬЗОВАТЕЛЬ 1 8 ДАТА И ВРЕМЯ ДОСТУП 1 9 ПОЛЬЗОВАТЕЛЬСКИЙ КЛИК ПРОСМОТР СТРАНИЦА ЗАПОЛНЕНИЕ ПОЛЫЙ ПОКАЗ И ПРОСМОТР БАННЕР И ВИДЕО 1 10 ДАННЫЙ ХАРАКТЕРИЗОВАТЬ АУДИТОРНЫЙ СЕГМЕНТ 1 11 ПАРАМЕТР СЕССИЯ 1 12 ДАННЫЙ О ВРЕМЕНИТЬ ПОСЕЩЕНИЕ 1 13 ИДЕНТИФИКАТОР ПОЛЬЗОВАТЕЛЬ ХРАНИТЬ В COOKIE 2 ООО LAQUO LAQUO RAQUO RAQUO МОЖЕТ ОБРАБАТЫВАТЬ МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СРОК 1 ГОД В СЛЕДУЮЩИЙ ЦЕЛЬ 2 1 ИДЕНТИФИКАЦИЯ ПОЛЬЗОВАТЕЛЬ ПОСЕТИТЕЛЬ САЙТ ООО LAQUO LAQUO RAQUO RAQUO 2 2 СВЯЗЬ СО Я В ТОМ ЧИСЛО НАПРАВЛЕНИЕ УВЕДОМЛЕНИЕ ЗАПРОС И ИНФОРМАЦИЯ КАСАТЬСЯ ИСПОЛЬЗОВАНИЕ САЙТ ИСПОЛНЕНИЕ СОГЛАШЕНИЕ И ДОГОВОР А ТАКЖЕ ОБРАБОТКА ЗАПРОС И ЗАЯВКА ОТ МЕНЬ 2 3 ОБЕЗЛИЧИВАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДЛЯ ПОЛУЧЕНИЕ ОБЕЗЛИЧИТЬ СТАТИСТИЧЕСКИЙ ДАННЫЙ КОТОРЫЙ ПЕРЕДАВАТЬСЯ ТРЕТИЙ ЛИЦО ДЛЯ ПРОВЕДЕНИЕ ИССЛЕДОВАНИЕ ВЫПОЛНЕНИЕ РАБОТА ИЛИ ОКАЗАНИЕ УСЛУГА ПО ПОРУЧЕНИЕ ООО LAQUO LAQUO RAQUO RAQUO 3 ОБРАБОТКА НА БУМАЖНЫЙ НОСИТЕЛЬ В ИНФОРМАЦИОННЫЙ СИСТЕМА ПЕРСОНАЛЬНЫЙ ДАННЫЙ И БЕЗ ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ А ТАКЖЕ СМЕШАННЫЙ СПОСОБ ООО LAQUO LAQUO RAQUO RAQUO МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОЛЖНЫЙ ОСУЩЕСТВЛЯТЬСЯ В СООТВЕТСТВИЕ С ТРЕБОВАНИЕ ФЕДЕРАЛЬНЫЙ ЗАКОН ОТ 27 07 2006Г ?? 152-ФЗ LAQUO О ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO LAQUO ПОЛИТИКА ЗАЩИТА И ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO ООО LAQUO LAQUO RAQUO RAQUO 4 ТАКЖЕ ДАЯ СВОЕ СОГЛАСИЕ НА ПРЕДОСТАВЛЕНИЕ ООО LAQUO LAQUO RAQUO RAQUO МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ КАК ПОСЕТИТЕЛЬ САЙТ АГЕНТСТВО С КОТОРЫЙ ОН СОТРУДНИЧАТЬ ООО LAQUO LAQUO RAQUO RAQUO ВПРАВЕ ОСУЩЕСТВЛЯТЬ ОБРАБОТКА МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЛЕДУЮЩИЙ СПОСОБ СБОР ЗАПИСЬ СИСТЕМАТИЗАЦИЯ НАКОПЛЕНИЕ ХРАНЕНИЕ ОБНОВЛЕНИЕ ИЗМЕНЕНИЕ ИСПОЛЬЗОВАНИЕ ПЕРЕДАЧА РАСПРОСТРАНЕНИЕ ПРЕДОСТАВЛЕНИЕ ДОСТУП 5 НАСТОЯЩИЙ СОГЛАСИЕ В ТОМ ЧИСЛО НА ПЕРЕДАЧА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ТРЕТИЙ СТОРОНА В СООТВЕТСТВИЕ С П 4 НАСТОЯЩИЙ СОГЛАСИЕ ВСТУПАТЬ В СИЛА С МОМЕНТ МОЙ ПЕРЕХОД НА САЙТ ООО LAQUO LAQUO RAQUO RAQUO И ДЕЙСТВОВАТЬ ДО ДЕНЬ ОТЗЫВ В ПИСЬМЕННЫЙ ФОРМА 6 НАСТОЯЩИЙ СОГЛАСИЕ МОЖЕТ БЫТЬ ОТОЗВАТЬ ПИСЬМЕННЫЙ ЗАЯВЛЕНИЕ НА E-MAIL', 0, 0);
HTML;
    }
    
    $sql_cat = "
      CREATE TABLE IF NOT EXISTS `$table_cat` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) DEFAULT '0',
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `link` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_cat_insert = "
      INSERT INTO `$table_cat` (`id`, `parent_id`, `title`, `img`, `link`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `hide`, `ord`) VALUES  ";
    {
    $sql_cat_insert .=<<<HTML
(1, 0, 'Главное меню', '', '', '', '', '', '', '', '', '', '', 'ГЛАВНОЕ МЕНЮ', '', 0, 0),
(2, 1, 'Главная', '', '/', '', '', '', '', '', '', '', '', 'ГЛАВНЫЙ', '', 0, 0),
(3, 1, 'О компании', '', '', '', '<h3>Пример текста &laquo;О компании&raquo;, как его написать &ndash; &laquo;пикап&raquo; потенциального клиента</h3>\r\n\r\n<p>Вы на рынке с 2002 года? Мне всё равно. &laquo;АвтоВАЗ&raquo; на рынке дольше вас&hellip; У вас хорошая динамика развития и молодой, дружный коллектив? Отлично, то есть, опыта у сотрудников маловато&hellip; Закупили дорогое немецкое оборудование (в кредит?), когда рубль балансировал на ободке унитаза? Значит, теперь мне за это расплачиваться? Прощайте!</p>\r\n\r\n<p>Как написать текст на страницу &laquo;О компании&raquo; и выбросить из словосочетания &laquo;потенциальный клиент&raquo; первое слово? Сделать это в среде &laquo;неправильных&raquo; клиентов. Ведь они, как девушка, которую постоянно атакуют пикаперы: интерес проявляет, но в машину не садится.</p>\r\n\r\n<p>Проблема в том, что у вас все как у всех: низкие цены, надежное оборудование и специалисты &ndash; профессионалы своего дела, у которых клиентоориенитрованность на нуле.</p>\r\n\r\n<p>Если не знаете, как написать текст о компании для сайта, и нужны примеры, то эта работа для <a href="//in-ri.ru" target="_blank">нас</a>.</p>\r\n', '', '', '', '', '', '', 'О КОМПАНИЯ', 'ПРИМЕР ТЕКСТ LAQUO О КОМПАНИЯ RAQUO КАК ЕГО НАПИСАТЬ NDASH LAQUO ПИКАП RAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ ВЫ НА РЫНОК С 2002 ГОД Я ВЕСЬ РАВНО LAQUO АВТОВАЗ RAQUO НА РЫНОК ДОЛГИЙ ВЫ HELLIP У ВЫ ХОРОШИЙ ДИНАМИК РАЗВИТИЕ И МОЛОДАЯ ДРУЖНЫЙ КОЛЛЕКТИВ ОТЛИЧНО ТО ЕСТЬ ОПЫТ У СОТРУДНИК МАЛОВАТЫЙ HELLIP ЗАКУПИТЬ ДОРОГОЙ НЕМЕЦКИЙ ОБОРУДОВАНИЕ В КРЕДИТ КОГДА РУБЛЬ БАЛАНСИРОВАТЬ НА ОБОДОК УНИТАЗ ЗНАЧИТ ТЕПЕРЬ Я ЗА ЭТО РАСПЛАЧИВАТЬСЯ ПРОЩАТЬ КАК НАПИСАТЬ ТЕКСТ НА СТРАНИЦА LAQUO О КОМПАНИЯ RAQUO И ВЫБРОСИТЬ ИЗ СЛОВОСОЧЕТАНИЕ LAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ RAQUO ПЕРВЫЙ СЛОВО СДЕЛАТЬ ЭТО В СРЕДА LAQUO НЕПРАВИЛЬНЫЙ RAQUO КЛИЕНТ ВЕДЬ ОНИ КАК ДЕВУШКА КОТОРЫЙ ПОСТОЯННО АТАКОВАТЬ ПИКАПЕР ИНТЕРЕС ПРОЯВЛЯТЬ НО В МАШИН НЕ САДИТЬСЯ ПРОБЛЕМА В ТОМ ЧТО У ВЫ ВСЕ КАК У ВЕСЬ НИЗКИЙ ЦЕНА НАДЕЖНЫЙ ОБОРУДОВАНИЕ И СПЕЦИАЛИСТ NDASH ПРОФЕССИОНАЛ СВОЕ ДЕТЬ У КОТОРЫЙ КЛИЕНТООРИЕНИТРОВАННОСТЬ НА НУЛЬ ЕСЛИ НЕ ЗНАТЬ КАК НАПИСАТЬ ТЕКСТ О КОМПАНИЯ ДЛЯ САЙТ И НУЖНЫЙ ПРИМЕР ТО ЭТОТ РАБОТА ДЛЯ МЫ', 0, 2),
(4, 1, 'Контакты', '', '', '', '<p><b>Директор: Ощепков Илья Александрович</b></p>\r\n\r\n<p><b>E-mail</b>: <a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n\r\n<p><b>Телефоны в Екатеринбурге:</b></p>\r\n\r\n<p><b>Сот. <a href="tel:+79058010809">+7 (905) 801-08-09</a></b></p>\r\n\r\n<p><b>Адрес:</b> 620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Время работы:</b> пн-пт 10:00-19:00, сб-вс 11:00-18:00<br />\r\n&nbsp;</p>\r\n<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script>\r\n\r\n<p>&nbsp;</p>\r\n', '', '', '', '', '', '', 'КОНТАКТ', 'ДИРЕКТОР ОЩЕПОК ИЛЬЯ АЛЕКСАНДР E-MAIL 1 IN-RI RU ТЕЛЕФОН В ЕКАТЕРИНБУРГ СОТЫ 7 905 801-08-09 АДРЕС 620100 Г ЕКАТЕРИНБУРГ УТЬ СИБИРСКИЙ ТРАКТ 12 2 ОФИС 404 ВРЕМЯ РАБОТА ПН-ПТ 10 00-19 00 СБ-ВС 11 00-18 00 NBSP NBSP', 0, 3),
(5, 6, 'Статьи', '', '', '', '', '', '', '', '', '', '', 'СТАТЬЯ', '', 0, 0),
(6, 1, 'Контент', '', '', '', '', '', '', '', '', '', '', 'КОНТЕНТ', '', 0, 0),
(7, 6, 'Фотогалерея', '', '', '', '', '', '', '', '', '', '', 'ФОТОГАЛЕРЕЯ', '', 0, 1),
(8, 6, 'Документы', '', '', '', '', '', '', '', '', '', '', 'ДОКУМЕНТ', '', 0, 0),
(9, 1, 'Новости', '', '/news', '', '', '', '', '', '', '', '', 'НОВОСТЬ', '', 0, 0);
HTML;
    }
    $output = '';
    
    $this->create_cat_img_dir( $name );
    
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    $output .= $this->setup_database_table($title_cat, $table_cat, $sql_cat, $sql_cat_insert, $script_name  );
    $output .= $this->copy_img_module_cat( $name, SOURCE_SITE_CORPORATE );
    
    return $output;
  }
  
  
  #--------------------- setup_database_module_onlineshop ---------------------

  function insert_def_module_mine_block_onlineshop( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt2`, `fl_is_fixed`, `hide`, `ord`) VALUES ";  
    $sql_insert .=<<<HTML
(2, 'Блок 3 Контакты', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h1>Блок</h1>\r\n\r\n<h2 style="text-align: center;">Контакты</h2>\r\n</div>\r\n</div>\r\n<!-- сontacts_inretactive -->\r\n\r\n<div class="сontacts_inretactive_box">\r\n<div class="сontacts_inretactive">\r\n<div class="ya_map"><script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script></div>\r\n\r\n<div class="сontacts_descr">\r\n<p><b>Наш адрес:</b><br />\r\n620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Телефоны:</b><br />\r\n<a href="tel:+79058010809">+7 (905) 801-08-09</a></p>\r\n\r\n<p><b>Электропочта:</b><br />\r\n<a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n</div>\r\n<!-- End сontacts_inretactive --></div>\r\n</div>\r\n<style type="text/css">/* --- сontacts --- */\r\n.сontacts_box{\r\n  background: url("img/mc_bg.png") no-repeat center center;\r\n  background-size: cover;\r\n  min-height: 490px;\r\n  padding-bottom: 0px;\r\n}\r\n.сontacts{\r\n  padding-bottom: 20px;\r\n}\r\n.сontacts_descr{\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  margin: 0 auto;\r\n  margin-top: 10px;\r\n  padding: 15px 25px;\r\n  border: 10px solid #17a2b8;\r\n  text-align: center;\r\n}\r\n.сontacts_descr b{\r\n  font: 400 25px/30px AvantGardeGothicBdITC-Reg, Arial, sans-serif;\r\n  color: #000000;\r\n}\r\n.сontacts_descr a{\r\n  color: #000000;\r\n}\r\n/* --- End сontacts --- */\r\n  \r\n  /* --- сontacts_inretactive_box --- */\r\n.сontacts_inretactive_box{\r\n  min-height: 500px;\r\n  padding-bottom: 0px;\r\n  \r\n}\r\n.сontacts_inretactive{\r\n  padding-bottom: 20px;\r\n  position: relative;\r\n}\r\n.ya_map{\r\n  position: absolute;\r\n  top: 0;\r\n  height: 500px;\r\n  width: 100%;\r\n}\r\n.сontacts_inretactive .mtitle_box{\r\n  /*position: relative;\r\n  z-index: 1;\r\n  text-align: right;\r\n  margin-right: 15px;*/\r\n}\r\n\r\n.сontacts_inretactive .сontacts_descr{\r\n  margin: 55px 15px 15px 25px;\r\n  position: relative;\r\n  z-index: 1;\r\n  max-width: 335px;\r\n  background: #ffffff;\r\n  float: right;\r\n}\r\n@media (max-width: 768px){\r\n  .сontacts_inretactive .сontacts_descr{\r\n    margin: 0 auto 0 auto;\r\n    float: none;\r\n  }\r\n  .сontacts_inretactive{\r\n    padding-top: 525px;\r\n  }\r\n}\r\n/* --- End сontacts_inretactive --- */\r\n</style>\r\n', 0, 0, 9),
(3, 'Блок 1 Коротко о нас', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок</h2>\r\n</div>\r\n</div>\r\n\r\n<div class="block_box bl_bg" style="">\r\n<div class="block_b" style="background: rgba(0, 0, 0, 0.3);\r\n    padding-top: 15px;\r\n    padding-bottom: 15px;">\r\n<div class="block">\r\n<h2 style="color: #fff;">Коротко о нас</h2>\r\n\r\n<p>Ни для кого не секрет, что в Сети лицом корпорации или фирмы является веб-сайт. Именно по этой причине солидные организации не жалеют денег на создание, поддержку, поисковую оптимизацию сайтов в Интернете. Первым шагом на пути к успеху является <b>разработка сайта</b>, которую лучше заказать у настоящих мастеров своего дела - студии, специализирующейся на сайтах. Дизайн студия, предлагает своим клиентам разработку сайтов в Екатеринбурге, в Москве и Челябинске. Нам важен каждый сайт, поэтому к каждому клиенту мы находим индивидуальный подход в разработке сайта.</p>\r\n\r\n<p>Очень важна грамотная и эффективная <strong>поисковая оптимизация сайта</strong>, которая призвана привлечь на сайт целевую аудиторию, увеличить посещаемость сайта, увеличить количество заказов с сайта.<br />\r\nСсылка на Ваш сайт появляется в поисковых системах Яндекс, Google, Mail, Рамблер в тот момент, когда клиенту нужны ваши товары/услуги, он как раз ищет поставщика, к кому обратиться. Такой клиент уже лояльно относится к Вашей компании.<br />\r\nВ последнее время эффект от <strong>поисковой оптимизации сайта в Екатеринбурге</strong> увеличивается в 2 раза за год, т.к. растет количество интернет-пользователей и их активность.</p>\r\n\r\n<p><b>Дизайн-студия занимается разработкой сайтов, поддержкой, созданием интернет-магазинов, интернет-рекламой и&nbsp;поисковой оптимизацией.</b></p>\r\n\r\n<p><b>Директор студии - Илья Крохалев</b> до создания студии 5 лет занимался разработкой сайтов, развитием городского портала E1.ru в компании Урал Релком.</p>\r\n\r\n<p><b>Студия работает с 1 марта 2005 года.</b> Многие специалисты студии работают в сфере создания сайтов более 15&nbsp;лет,</p>\r\nчто гарантирует высокое качество работы, решение любых технических вопросов.\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><b>Среди наших клиентов:</b> крупные заводы, торговые компании, турфирмы, агентства недвижимости, застройщики, известные интернет-магазины, медицинские компании и многие другие.<br />\r\n&nbsp;</p>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">.bl_bg{\r\n    background-image: url(/images_ckeditor/files/test.jpg);\r\n    background-attachment: fixed;\r\n    background-size: cover;\r\n    background-position: center center;\r\n    text-shadow: 1px 1px 16px rgba(0, 0, 0, 0.8);\r\n    color: #ffffff;\r\n    padding-top: 0;\r\n    padding-bottom: 0;\r\n    margin-bottom: 0px;\r\n    /*-webkit-transform: translate3d(0,0,0);*/\r\n    -webkit-backface-visibility: hidden;\r\n  }\r\n  .admin_edit_box .bl_bg{\r\n    /*position: relative;\r\n    z-index: 1600;*/\r\n  }\r\n</style>\r\n', 0, 0, 8),
(4, 'Блок 2 Приемущества', '', '', '<div class="block_box">\r\n<div class="block">\r\n<h2>Блок&nbsp;</h2>\r\n\r\n<div class="scheme">\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-globe fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Встречи 1 раз в неделю</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-tasks fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">График работы</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-external-link-alt fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Новые техники</div>\r\n</div>\r\n</div>\r\n\r\n<div class="row">\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-clock fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Поддержка</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="fas fa-percent fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">100%</div>\r\n</div>\r\n\r\n<div class="col-12 col-md-4">\r\n<div class="c_img_box"><i class="far fa-sun fa-5x">&nbsp;</i></div>\r\n\r\n<div class="c_title">Качественный результат</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style type="text/css">/* --- scheme --- */\r\n.scheme{\r\n	margin-top: 25px;\r\n  margin-bottom: 25px;\r\n}\r\n.scheme .c_img_box{\r\n  text-align:center;\r\n  /*color: #28a745;*/\r\n  padding-bottom: 25px;\r\n  padding-top: 15px;\r\n}\r\n.scheme .c_title{\r\n	text-align:center;\r\n  padding-bottom: 35px;\r\n  font-size: 24px;\r\n  font-weight: bold;\r\n}\r\n.steps{\r\n  \r\n}\r\n.steps .rim{\r\n  display: inline-block;\r\n  width: 25px;\r\n  text-align:center;\r\n}\r\n.steps .left{\r\n  /*text-align:right;*/\r\n}\r\n.steps .right{\r\n  border-left: 2px solid #37373e59;\r\n  border-right: 2px solid #37373e59;\r\n  border-top: 2px solid #37373e59;\r\n  padding-top: 20px;\r\n  padding-bottom: 20px;\r\n  /*min-height: 75px;*/\r\n}\r\n.steps .row:last-child {\r\n  \r\n  /*border-bottom: 3px solid #37373e;*/\r\n}\r\n/* --- END scheme --- */\r\n</style>\r\n', 1, 0, 7),
(5, 'Слайдер', '', 'block_mine_slider', '', 0, 0, 3),
(6, 'Шапка сайта', '', 'block_mine_header', '', 1, 0, 0),
(7, 'Меню сайта', '', 'block_mine_top_menu', '', 1, 0, 1),
(8, 'Контент на внутренних страницах', '', 'block_inner_content', '', 1, 0, 6),
(9, 'Поиск по сайту', '', 'block_search', '', 1, 0, 2),
(10, 'Товары на главной', '', 'block_mine_goods', '', 0, 0, 5),
(11, 'Подвал сайта', '', 'block_mine_footer', '', 1, 0, 10),
(12, 'Категории товаров на главной', '', 'block_mine_cat_goods', '<p class = "c_h1" >Категории товаров</p>\r\n', 0, 0, 4);
HTML;
    
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function insert_def_module_carusel_onlineshop( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `txt1`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
(1, 'Слайд №1', '1556487817.jpg', '/katalog-tovarov', 'Выбери свой!', 'Версия CAFE только при условии покупки комплекта PERFORMANCE', '', '', 0, 0),
(2, 'Слайд №2', '1556487833.jpg', '', '', '', '', '', 0, 1),
(3, 'Слайд №3', '1556487842.jpg', '', '', '', '', '', 0, 2),
(4, 'Слайд №4', '1556487852.jpg', '', '', '', '', '', 0, 3),
(5, 'Слайд №5', '1556487862.png', '', '', '', '', '', 0, 4);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_ONLINESHOP );
    
    return $output;
  }
  
  function setup_module_currency_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(256) NOT NULL,
        `title` varchar(256) NOT NULL,
        `val` text NOT NULL,
        `type` tinyint(1) NOT NULL DEFAULT '0',
        `comment` varchar(256) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `name`, `title`, `val`, `type`, `comment`, `hide`, `ord`) VALUES
      (1, 'eur', 'Курс EUR:', '70.50', 0, '', 0, 0),
      (2, 'usd', 'Курс USD:', '63.10', 0, '', 0, 0) ";
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_news_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `fl_show_mine` tinyint(1) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `date`, `longtxt1`, `longtxt2`, `fl_show_mine`, `orm_search_name`, `orm_search`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `hide`, `ord`) VALUES  ";
    $sql_insert .=<<<HTML
(1, 'Петросян отреагировал на сообщения о разделе имущества со Степаненко', '1533303902.jpg', '2018-08-02', 'Отмечалось, что иск подала Степаненко. Петросян назвал эти новости &quot;недоразумением&quot;. Юморист, телеведущий и певец Владимир Винокур позднее заявил, что раздел имущества между Евгением Петросяном и Еленой Степаненко не должен становиться предметом интереса СМИ.', '<p>Юмористы <strong>Евгений Петросян</strong> и <strong>Елена Степаненко</strong> делят совместно нажитое имущество в&nbsp;Хамовническом суде, сообщает агентство городских новостей &laquo;<a href="https://www.mskagency.ru/materials/2805128" target="_blank">Москва</a>&raquo;.</p>\r\n\r\n<p class="image-in-text" itemprop="image" itemscope="" itemtype="https://schema.org/ImageObject"><img data-author="Фото: скриншот" src="/images/news/orig/1533303902.jpg" /><span class="title"></span><br />\r\n<span class="author">Фото: скриншот</span></p>\r\n\r\n<p>С&nbsp;иском в&nbsp;суд обратилась Степаненко, сообщили в&nbsp;пресс-службе суда. Заявление будет рассмотрено 6&nbsp;августа.</p>\r\n\r\n<p>Петросян заявил, что ему об&nbsp;этом ничего неизвестно,&nbsp;&mdash; по&nbsp;его словам, &laquo;это какое-то недоразумение&raquo;.</p>\r\n\r\n<p>Также народный артист отказался комментировать информацию о&nbsp;разводе со&nbsp;Степаненко. &laquo;Давайте без комментариев. Какая вам разница, что у&nbsp;меня происходит? Без комментариев&raquo;,&nbsp;&mdash; сказал Евгений Петросян.</p>\r\n\r\n<p>Официально юмористы о&nbsp;своем разводе не&nbsp;объявляли.</p>\r\n', 0, 'ПЕТРОСЯН ОТРЕАГИРОВАТЬ НА СООБЩЕНИЕ О РАЗДЕЛ ИМУЩЕСТВО СО СТЕПАНЕНКО', 'ЮМОРИСТ ЕВГЕНИЯ ПЕТРОСЯН И ЕЛЕНА СТЕПАНЕНКО ДЕЛИТЬ СОВМЕСТНО НАЖИТОЕ ИМУЩЕСТВО В NBSP ХАМОВНИЧЕСКИЙ СУД СООБЩАТЬ АГЕНТСТВО ГОРОДСКОЙ НОВОСТЬ LAQUO МОСКВА RAQUO ФОТО СКРИНШОТ С NBSP ИСКОМЫЙ В NBSP СУД ОБРАТИТЬСЯ СТЕПАНЕНКО СООБЩИТЬ В NBSP ПРЕСС-СЛУЖБА СУД ЗАЯВЛЕНИЕ БЫТЬ РАССМОТРЕТЬ 6 NBSP АВГУСТ ПЕТРОСЯН ЗАЯВИТЬ ЧТО ОН ОБ NBSP ЭТО НИЧЕГО НЕИЗВЕСТНЫЙ NBSP MDASH ПО NBSP ЕГО СЛОВО LAQUO ЭТО КАКОЙ-ТО НЕДОРАЗУМЕНИЕ RAQUO ТАКЖЕ НАРОДНЫЙ АРТИСТ ОТКАЗАТЬСЯ КОММЕНТИРОВАТЬ ИНФОРМАЦИЯ О NBSP РАЗВОД СО NBSP СТЕПАНЕНКО LAQUO ДАВАТЬ БЕЗ КОММЕНТАРИЙ КАКАТЬ ВЫ РАЗНИЦА ЧТО У NBSP МЕНЬ ПРОИСХОДИТЬ БЕЗ КОММЕНТАРИЙ RAQUO NBSP MDASH СКАЗАТЬ ЕВГЕНИЯ ПЕТРОСЯН ОФИЦИАЛЬНО ЮМОРИСТ О NBSP СВОЕ РАЗВОД НЕ NBSP ОБЪЯВЛЯТЬ', '', '', '', '', '', '', 0, 0),
(2, 'Ученые назвали причину исчезновения кораблей в Бермудском треугольнике', '1533304968.jpg', '2018-08-03', 'Ученые из&nbsp;Университета Саутгемптона проверили одну из&nbsp;наиболее вероятных версий исчезновения кораблей в&nbsp;Бермудском треугольнике&nbsp;&mdash; блуждающие волны. Об этом говорится в&nbsp;документальном фильме &quot;Загадка Бермудского треугольника&quot; на&nbsp;Channel 5, сообщает Daily Star.', '<div itemprop="articleBody">\r\n<p>Ученые из&nbsp;Университета Саутгемптона (Великобритания) проверили одну из&nbsp;наиболее вероятных версий исчезновения кораблей в&nbsp;Бермудском треугольнике &ndash; блуждающие волны. Об этом говорится в&nbsp;документальном фильме &quot;Загадка Бермудского треугольника&quot; на&nbsp;Channel 5, сообщает британская газета Daily Star.</p>\r\n\r\n<p>Район Атлантического океана, ограниченный треугольником, вершинами которого являются Флорида, Бермудские острова и&nbsp;Пуэрто-Рико, известен тем, что там, как&nbsp;считается, чаще, чем в&nbsp;других районах пропадают морские и&nbsp;воздушные суда.&nbsp;</p>\r\n\r\n<div id="">\r\n<div><img alt="Поселение на Бермудских островах" src="//corporate.ready.in-ri.ru/images_ckeditor/images/news/1479120401.jpg" style="float: left; margin: 5px 5px 5px 0px;" title="Поселение на Бермудских островах" /></div>\r\n\r\n<div>Биофизик: тайна Бермудского треугольника, возможно, близка к разгадке</div>\r\n</div>\r\n\r\n<p>Существуют множество версий загадочных исчезновений, однако основные научные: мощные выбросы метана, генерация волнами инфразвука, слишком быстрое течение и&nbsp;блуждающие волны. Последние представляют собой гигантские одиночные волны, внезапно возникающие в&nbsp;океане, высотой 20-30 метров.&nbsp;&nbsp;</p>\r\n\r\n<p><span style="font-size: 1rem;">Исследователи из&nbsp;Саутгемптона решили сосредоточиться именно на&nbsp;этой версии. Они смоделировали &quot;волну-убийцу&quot; на&nbsp;компьютере и&nbsp;направили к&nbsp;ней модель судна. Как и&nbsp;ожидалось, корабль &quot;затонул&quot; за&nbsp;считанные минуты.</span></p>\r\n\r\n<p>По мнению ученых, блуждающие волны могут объяснить исчезновение кораблей, но&nbsp;не самолетов. Поэтому говорить о&nbsp;полном раскрытии секрета загадочного треугольника пока рано.</p>\r\n\r\n<p>Ведущий научный сотрудник Института физики Земли им. О.Ю. Шмидта РАН Александр Жигалин в&nbsp;эфире радио Sputnik прокомментировал выводы британских ученых.&nbsp;</p>\r\n\r\n<p>&quot;Бермудский треугольник &ndash; это такая модная загадка, которую очень долго разгадывают, но&nbsp;так до&nbsp;конца и&nbsp;не ясно, что там происходит. Сначала одной из&nbsp;ведущих версий было то, что в&nbsp;этом районе часты густые туманы. Кстати, эта гипотеза объясняет исчезновения как&nbsp;кораблей, так и&nbsp;самолетов. А вот что касается огромных волн, то как&nbsp;раз этим данный район особенно не&nbsp;грешит: море там не&nbsp;очень глубокое, там много водорослей, и&nbsp;подняться таким волнам там очень непросто. Во всяком случае, до&nbsp;сих пор об&nbsp;этом ничего не&nbsp;было слышно. А вот &quot;метановая гипотеза&quot; с&nbsp;точки зрения физики, на&nbsp;мой взгляд, достаточно оправдана: насыщенная пузырьками газа вода обладает пониженной несущей способностью, и&nbsp;корабли при&nbsp;определенных условиях могут в&nbsp;такой воде потонуть. Что касается существования огромных &quot;волн-убийцы&quot;, то это можно считать установленным фактом &ndash; свидетельств неожиданного появления таких волн вполне достаточно. Но их появление именно в&nbsp;данном районе у&nbsp;меня вызывает сильные сомнения&quot;, &ndash; сказал Александр Жигалин.&nbsp;</p>\r\n\r\n<p>Он уверен, что компьютерное моделирование &ndash; это способ проверки гипотез, а&nbsp;строить на&nbsp;нем гипотезу нельзя.</p>\r\n\r\n<p>&quot;Я не&nbsp;очень доверяю моделированию. Мой многолетний опыт работы как&nbsp;геофизика показывает, что моделирование &ndash; это немножко лукавая вещь. Поскольку, грубо говоря, что в&nbsp;машину заложишь, то на&nbsp;выходе и&nbsp;получишь. Вот хочется нам получить волну в&nbsp;30 метров &ndash; так мы ее, скорее всего, и&nbsp;получим. Надо изучать любое явление в&nbsp;реальности, а&nbsp;не только в&nbsp;компьютере &ndash; только так мы получим ясный ответ на&nbsp;все вопросы. Моделирование &ndash; это способ проверки гипотез, а&nbsp;строить на&nbsp;нем гипотезу нельзя&quot;, &ndash; заключил Александр Жигалин.&nbsp;</p>\r\n</div>\r\n', 0, 'УЧЕНЫЙ НАЗВАТЬ ПРИЧИНА ИСЧЕЗНОВЕНИЕ КОРАБЛЬ В БЕРМУДСКИЙ ТРЕУГОЛЬНИК', 'УЧЕНЫЙ ИЗ NBSP УНИВЕРСИТЕТ САУТГЕМПТОН ВЕЛИКОБРИТАНИЯ ПРОВЕРИТЬ ОДИН ИЗ NBSP НАИБОЛЕЕ ВЕРОЯТНЫЙ ВЕРСИЯ ИСЧЕЗНОВЕНИЕ КОРАБЛЬ В NBSP БЕРМУДСКИЙ ТРЕУГОЛЬНИК NDASH БЛУЖДАТЬ ВОЛНА ОБ ЭТО ГОВОРИТЬСЯ В NBSP ДОКУМЕНТАЛЬНЫЙ ФИЛЬМ QUOT ЗАГАДКА БЕРМУДСКИЙ ТРЕУГОЛЬНИК QUOT НА NBSP CHANNEL 5 СООБЩАТЬ БРИТАНСКИЙ ГАЗЕТА DAILY STAR РАЙОН АТЛАНТИЧЕСКИЙ ОКЕАН ОГРАНИЧЕННЫЙ ТРЕУГОЛЬНИК ВЕРШИНА КОТОРЫЙ ЯВЛЯТЬСЯ ФЛОРИДА БЕРМУДСКИЙ ОСТРОВ И NBSP ПУЭРТО-РИКО ИЗВЕСТНЫЙ ТЕМ ЧТО ТАМ КАК NBSP СЧИТАТЬСЯ ЧАЩА ЧЕМ В NBSP ДРУГОЙ РАЙОН ПРОПАДАТЬ МОРСКОЙ И NBSP ВОЗДУШНЫЙ СУД NBSP БИОФИЗИК ТАЙНА БЕРМУДСКИЙ ТРЕУГОЛЬНИК ВОЗМОЖНО БЛИЗКИЙ К РАЗГАДКА СУЩЕСТВОВАТЬ МНОЖЕСТВО ВЕРСИЯ ЗАГАДОЧНЫЙ ИСЧЕЗНОВЕНИЕ ОДНАКО ОСНОВНЫЙ НАУЧНЫЙ МОЩНЫЙ ВЫБРОС МЕТАН ГЕНЕРАЦИЯ ВОЛНА ИНФРАЗВУК СЛИШКОМ БЫСТРЫЙ ТЕЧЕНИЕ И NBSP БЛУЖДАТЬ ВОЛНА ПОСЛЕДНИЙ ПРЕДСТАВЛЯТЬ СЕБЯ ГИГАНТСКИЙ ОДИНОЧНЫЙ ВОЛНА ВНЕЗАПНО ВОЗНИКАТЬ В NBSP ОКЕАН ВЫСОТА 20-30 МЕТР NBSP NBSP ИССЛЕДОВАТЕЛЬ ИЗ NBSP САУТГЕМПТОН РЕШИТЬ СОСРЕДОТОЧИТЬСЯ ИМЕННО НА NBSP ЭТОТ ВЕРСИЯ ОНИ СМОДЕЛИРОВАТЬ QUOT ВОЛНУ-УБИЙЦА QUOT НА NBSP КОМПЬЮТЕР И NBSP НАПРАВИТЬ К NBSP ОНА МОДЕЛЬ СУДНЫЙ КАК И NBSP ОЖИДАТЬСЯ КОРАБЛЬ QUOT ЗАТОНУТЬ QUOT ЗА NBSP СЧИТАТЬ МИНУТА ПО МНЕНИЕ УЧЕНЫЙ БЛУЖДАТЬ ВОЛНА МОЧЬ ОБЪЯСНИТЬ ИСЧЕЗНОВЕНИЕ КОРАБЛЬ НО NBSP НЕ САМОЛЁТ ПОЭТОМУ ГОВОРИТЬ О NBSP ПОЛНЫЙ РАСКРЫТИЕ СЕКРЕТ ЗАГАДОЧНЫЙ ТРЕУГОЛЬНИК ПОКА РАНО ВЕДУЩИЙ НАУЧНЫЙ СОТРУДНИК ИНСТИТУТ ФИЗИК ЗЕМЛЯ ИМЯ О Ю ШМИДТ РАНА АЛЕКСАНДР ЖИГАЛИНА В NBSP ЭФИР РАДИО SPUTNIK ПРОКОММЕНТИРОВАТЬ ВЫВОД БРИТАНСКИЙ УЧЕНЫЙ NBSP QUOT БЕРМУДСКИЙ ТРЕУГОЛЬНИК NDASH ЭТО ТАКАТЬ МОДНЫЙ ЗАГАДКА КОТОРЫЙ ОЧЕНЬ ДОЛГО РАЗГАДЫВАТЬ НО NBSP ТАК ДО NBSP КОНЕЦ И NBSP НЕ ЯСНО ЧТО ТАМ ПРОИСХОДИТЬ СНАЧАЛА ОДИН ИЗ NBSP ВЕДУЩИЙ ВЕРСИЯ БЫЛО ТО ЧТО В NBSP ЭТО РАЙОН ЧАСТЫЙ ГУСТОЙ ТУМАН КСТАТИ ЭТОТ ГИПОТЕЗА ОБЪЯСНЯТЬ ИСЧЕЗНОВЕНИЕ КАК NBSP КОРАБЛЬ ТАК И NBSP САМОЛЁТ А ВОТ ЧТО КАСАТЬСЯ ОГРОМНЫЙ ВОЛНА ТО КАК NBSP РАЗ ЭТО ДАННЫЙ РАЙОН ОСОБЕННО НЕ NBSP ГРЕШИТЬ МОР ТАМ НЕ NBSP ОЧЕНЬ ГЛУБОКИЙ ТАМ МНОГО ВОДОРОСЛЬ И NBSP ПОДНЯТЬСЯ ТАКОЙ ВОЛНА ТАМ ОЧЕНЬ НЕПРОСТО ВО ВСЯКИЙ СЛУЧАЙ ДО NBSP СЕЙ ПОРА ОБ NBSP ЭТО НИЧЕГО НЕ NBSP БЫЛО СЛЫШНО А ВОТ QUOT МЕТАНОВЫЙ ГИПОТЕЗА QUOT С NBSP ТОЧКА ЗРЕНИЕ ФИЗИК НА NBSP МЫТЬ ВЗГЛЯД ДОСТАТОЧНО ОПРАВДАТЬ НАСЫЩЕННЫЙ ПУЗЫРЁК ГАЗ ВОД ОБЛАДАТЬ ПОНИЖЕННЫЙ НЕСУЩИЙ СПОСОБНОСТЬ И NBSP КОРАБЛЬ ПРИ NBSP ОПРЕДЕЛЕННЫЙ УСЛОВИЕ МОЧЬ В NBSP ТАКОЙ ВОД ПОТОНУТЬ ЧТО КАСАТЬСЯ СУЩЕСТВОВАНИЕ ОГРОМНЫЙ QUOT ВОЛН-УБИЙЦА QUOT ТО ЭТО МОЖНО СЧИТАТЬ УСТАНОВЛЕННЫЙ ФАКТ NDASH СВИДЕТЕЛЬСТВО НЕОЖИДАННЫЙ ПОЯВЛЕНИЕ ТАКОЙ ВОЛНА ВПОЛНЕ ДОСТАТОЧНО НО ИХ ПОЯВЛЕНИЕ ИМЕННО В NBSP ДАННЫЙ РАЙОН У NBSP МЕНЬ ВЫЗЫВАТЬ СИЛЬНЫЙ СОМНЕНИЕ QUOT NDASH СКАЗАТЬ АЛЕКСАНДР ЖИГАЛИНА NBSP ОН УВЕРИТЬ ЧТО КОМПЬЮТЕРНЫЙ МОДЕЛИРОВАНИЕ NDASH ЭТО СПОСОБ ПРОВЕРКА ГИПОТЕЗА А NBSP СТРОИТЬ НА NBSP НЕМОЙ ГИПОТЕЗА НЕЛЬЗЯ QUOT Я НЕ NBSP ОЧЕНЬ ДОВЕРЯТЬ МОДЕЛИРОВАНИЕ МЫТЬ МНОГОЛЕТНИЙ ОПЫТ РАБОТА КАК NBSP ГЕОФИЗИК ПОКАЗЫВАТЬ ЧТО МОДЕЛИРОВАНИЕ NDASH ЭТО НЕМНОЖКО ЛУКАВЫЙ ВЕЩЬ ПОСКОЛЬКУ ГРУБО ГОВОРИТЬ ЧТО В NBSP МАШИН ЗАЛОЖИТЬ ТО НА NBSP ВЫХОД И NBSP ПОЛУЧИТЬ ВОТ ХОТЕТЬСЯ МЫ ПОЛУЧИТЬ ВОЛНА В NBSP 30 МЕТР NDASH ТАК МЫ ЕЕ СКОРЫЙ ВСЕГО И NBSP ПОЛУЧИТЬ НАДО ИЗУЧАТЬ ЛЮБОЙ ЯВЛЕНИЕ В NBSP РЕАЛЬНОСТЬ А NBSP НЕ ТОЛЬКО В NBSP КОМПЬЮТЕР NDASH ТОЛЬКО ТАК МЫ ПОЛУЧИТЬ ЯСНЫЙ ОТВЕТ НА NBSP ВСЕ ВОПРОС МОДЕЛИРОВАНИЕ NDASH ЭТО СПОСОБ ПРОВЕРКА ГИПОТЕЗА А NBSP СТРОИТЬ НА NBSP НЕМОЙ ГИПОТЕЗА НЕЛЬЗЯ QUOT NDASH ЗАКЛЮЧИТЬ АЛЕКСАНДР ЖИГАЛИНА NBSP', '', '', '', '', '', '', 0, 0),
(3, 'Марафон «Европа-Азия» соберёт более шести тысяч участников', '1533305394.jpg', '2018-08-01', 'Велосипедисты смогут проехать по дистанциям в 2, 14, 34 и 50 километров.', '<div>Продолжается офлайн-регистрация участников IV Международного легкоатлетического марафона &laquo;Европа &mdash; Азия&raquo;, который пройдет в Екатеринбурге в это воскресенье, 5 августа. На данный момент на одну из дистанций уже зарегистрировались более шести тысяч человек. Но организаторы ожидают еще больше участников, так как многие спортсмены регистрируются за день до марафона.<br />\r\n<br />\r\nУчастникам предлагают дистанции в 3, 10, 21 и 42 километра. Старт организуют на площади 1905 года. Через каждые пять километров марафонцев будет ждать пункт, где можно перекусить и освежиться.<br />\r\n<br />\r\nНапомним, что из-за марафона будут перекрываться улицы:<br />\r\n<br />\r\n&ndash; с 00:00 до 18:00 проспект Ленина (от переулка Банковского до улицы 8 Марта);<br />\r\n<br />\r\n&ndash; с 07:00 до 15:00 проспект Ленина (от Репина до переулка Банковского), Репина (от Металлургов до проспекта Ленина), четная сторона улицы Металлургов (от автодороги Пермь &mdash; Екатеринбург до Репина);<br />\r\n<br />\r\n&ndash; с 07:00 до 10:00 проспект Ленина (от 8 Марта до площади Кирова), площадь Кирова (от проспекта Ленина до улицы Мира), улица Мира (от Малышева до Первомайской), Толмачева (от проспекта Ленина до Царской), Царская (от Толмачева до Николая Никонова), Бориса Ельцина (от 8 Марта до Челюскинцев), 8 Марта (от проспекта Ленина до улицы Бориса Ельцина).<br />\r\n<br />\r\nТакже с 07:00 до 14:00 будет закрыто движение по дороге Пермь &mdash; Екатеринбург (с 343-го по 345-й километр, обратное направление), с 4 по 6 августа не будет работать парковка на площади 1905 года.<br />\r\n<br />\r\nКроме того, из-за забега схему движения <!--colorstart:#CC0000--><span style="color:#CC0000"><!--/colorstart--><u>изменят</u><!--colorend--></span><!--/colorend--> 17 трамвайных, девять троллейбусных маршрутов, 17 муниципальных и 29 коммерческих автобусов.</div>\r\n', 0, 'МАРАФОН ?ЕВРОПА-АЗИЯ ? СОБРАТЬ БОЛЕЕ ШЕСТЬ ТЫСЯЧА УЧАСТНИК', 'ПРОДОЛЖАТЬСЯ ОФЛАЙН-РЕГИСТРАЦИЯ УЧАСТНИК IV МЕЖДУНАРОДНЫЙ ЛЕГКОАТЛЕТИЧЕСКИЙ МАРАФОН LAQUO ЕВРОПА MDASH АЗИЯ RAQUO КОТОРЫЙ ПРОЙДЕТ В ЕКАТЕРИНБУРГ В ЭТО ВОСКРЕСЕНИЕ 5 АВГУСТ НА ДАННЫЙ МОМЕНТ НА ОДИН ИЗ ДИСТАНЦИЯ УЖЕ ЗАРЕГИСТРИРОВАТЬСЯ БОЛЕЕ ШЕСТЬ ТЫСЯЧА ЧЕЛОВЕК НО ОРГАНИЗАТОР ОЖИДАТЬ ЕЩИЙ БОЛЬШИЙ УЧАСТНИК ТАК КАК МНОГИЙ СПОРТСМЕН РЕГИСТРИРОВАТЬСЯ ЗА ДЕТЬ ДО МАРАФОН УЧАСТНИК ПРЕДЛАГАТЬ ДИСТАНЦИЯ В 3 10 21 И 42 КИЛОМЕТР СТАРТ ОРГАНИЗОВАТЬ НА ПЛОЩАДЬ 1905 ГОД ЧЕРЕЗ КАЖДЫЙ ПЯТЬ КИЛОМЕТРОВЫЙ МАРАФОНЕЦ БЫТЬ ЖДАТЬ ПУНКТ ГДЕ МОЖНО ПЕРЕКУСИТЬ И ОСВЕЖИТЬСЯ НАПОМНИТЬ ЧТО ИЗ-ЗА МАРАФОН БЫТЬ ПЕРЕКРЫВАТЬСЯ УЛИЦА NDASH С 00 00 ДО 18 00 ПРОСПЕКТ ЛЕНИН ОТ ПЕРЕУЛОК БАНКОВСКИЙ ДО УЛИЦА 8 МАРТ NDASH С 07 00 ДО 15 00 ПРОСПЕКТ ЛЕНИН ОТ РЕПИН ДО ПЕРЕУЛОК БАНКОВСКИЙ РЕПИН ОТ МЕТАЛЛУРГ ДО ПРОСПЕКТ ЛЕНИН ЧЕТНЫЙ СТОРОНА УЛИЦА МЕТАЛЛУРГ ОТ АВТОДОРОГА ПЕРМЬ MDASH ЕКАТЕРИНБУРГ ДО РЕПИН NDASH С 07 00 ДО 10 00 ПРОСПЕКТ ЛЕНИН ОТ 8 МАРТ ДО ПЛОЩАДЬ КИРОВ ПЛОЩАДЬ КИРОВ ОТ ПРОСПЕКТ ЛЕНИН ДО УЛИЦА МИР УЛИЦА МИР ОТ МАЛЫШЕВ ДО ПЕРВОМАЙСКИЙ ТОЛМАЧЕВ ОТ ПРОСПЕКТ ЛЕНИН ДО ЦАРСКИЙ ЦАРСКИЙ ОТ ТОЛМАЧЕВ ДО НИКОЛАЙ НИКОНОВ БОРИС ЕЛЬЦИН ОТ 8 МАРТ ДО ЧЕЛЮСКИНЕЦ 8 МАРТ ОТ ПРОСПЕКТ ЛЕНИН ДО УЛИЦА БОРИС ЕЛЬЦИН ТАКЖЕ С 07 00 ДО 14 00 БЫТЬ ЗАКРЫТЫЙ ДВИЖЕНИЕ ПО ДОРОГА ПЕРМЬ MDASH ЕКАТЕРИНБУРГ С 343-ГО ПО 345-Й КИЛОМЕТР ОБРАТНЫЙ НАПРАВЛЕНИЕ С 4 ПО 6 АВГУСТ НЕ БЫТЬ РАБОТАТЬ ПАРКОВКА НА ПЛОЩАДЬ 1905 ГОД КРОМЕ ТОГО ИЗ-ЗА ЗАБЕГ СХЕМА ДВИЖЕНИЕ ИЗМЕНИТЬ 17 ТРАМВАЙНЫЙ ДЕВЯТЬ ТРОЛЛЕЙБУСНЫЙ МАРШРУТ 17 МУНИЦИПАЛЬНЫЙ И 29 КОММЕРЧЕСКИЙ АВТОБУС', '', '', '', '', '', '', 0, 0);
HTML;
    $output = '';
    $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CORPORATE );
    
    return $output; 
  }
  
  function setup_module_articles_onlineshop( $title, $name ){
    
    $table       = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $title_cat   = $title.' категории';
    $table_cat   = DB_PFX.$name.'_cat';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cat_id` int(11) DEFAULT '0',
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `date` varchar(10) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; "; 
    $sql_insert = "
      INSERT INTO `$table` (`id`, `cat_id`, `title`, `img`, `date`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `hide`, `ord`) VALUES  ";
    {
    $sql_insert .=<<<HTML
(1, 5, 'Основатель диджитал-студии Finch — о веб-дизайне в России и США', '1552333133.png', '', '<p>Программист Дмитрий Щипачев руководит агентством Finch, среди проектов которого &mdash; сайт &laquo;Спартака&raquo; и приложение для ТНТ-Club. Что происходит с веб-пространством и как его меняет мир мобильных приложений.</p>\r\n', '<div class="l-col-center__inner">\r\n<div class="article__overview ">\r\n<div class="article__rubric">&nbsp;</div>\r\n\r\n<div class="article__main-image">\r\n<div class="article__main-image__inner"><img alt="" class="js-rbcslider-image" itemprop="image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412030895.png" />\r\n<div class="article__main-image__copyrights">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__social js-social-likes">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="Основатель диджитал-студии Finch — о веб-дизайне в России и США">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__info">\r\n<div class="article__author">Автор <span class="article__author__name"> <!--\r\n                    --><!--\r\n                        -->Елена Фомина<!--\r\n                        --><!--\r\n                    --><!--\r\n                --> </span></div>\r\n\r\n<div class="article__date" content="2018-12-19T10:42:24+03:00" itemprop="datePublished">19 декабря 2018</div>\r\n\r\n<div class="article__date" content="2018-12-19T10:42:24+03:00" itemprop="datePublished">&nbsp;</div>\r\n<meta itemprop="dateModified" content="2018-12-19T10:42:24+03:00"></div>\r\n\r\n<div class="article__subtitle">Программист Дмитрий Щипачев руководит агентством Finch, среди проектов которого &mdash; сайт &laquo;Спартака&raquo; и приложение для ТНТ-Club. &laquo;РБК Стиль&raquo; узнал у него, что происходит с веб-пространством и как его меняет мир мобильных приложений.</div>\r\n</div>\r\n\r\n<div class="article__text" itemprop="articleBody">\r\n<p>Прихожая офиса Finch встречает горой ботинок, ворохом пуховиков и мотоциклом BMW. Над гардеробной-гаражом ютится небольшой оупенспейс и директорский кабинет. В нем&nbsp;&mdash; уютный полумрак. Так легче работается. Дмитрий Щипачев руководит студией разработки почти 12 лет, все успевает благодаря мотоциклу&nbsp;и мыслит стратегически. Его студия Finch сконструировала и запустила сайты для &laquo;Дома-2&raquo;, &laquo;Столото&raquo; и футбольного клуба &laquo;Спартак&raquo;, параллельно переключаясь на приложения для смартфонов. С Дмитрием мы поговорили о том, как устроен диджитал-бизнес в России и чем он отличается от западного.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О приложениях и сайтах</span></p>\r\n\r\n<p><strong>Дизайн приложений &mdash; это то, чем хочется заниматься, в отличие от сайтов.</strong> Потому что вся эстетика мобильных устройств располагает к тому, чтобы создавать красивые приложения. Плюс нам комфортнее работать с тем характером пользовательского потребления, которым приложения отличаются от веба.</p>\r\n\r\n<p><strong>Дополненная и виртуальная реальность дают только вау-фактор. </strong>Единственная область, в которой он применим, &mdash; это реклама.</p>\r\n\r\n<p><strong>Рекламой заниматься мы не любим.&nbsp;</strong>Никакой социально полезной функции в этом нет. Интереснее решить даже самую простую задачу, с которой пользователь сталкивается ежедневно, чем создать самый успешный рекламный кейс.</p>\r\n\r\n<p><strong>Ни один продукт не бесплатный.</strong> С тебя все равно получат деньги. Встроенная ли это покупка, или это подписка, или пользовательские данные, или реклама. Или все вместе. Ты все равно платишь за то, чем пользуешься в интернете. Все мы помним, как появился Gmail с бесплатными 10 Гб на диске, когда Mail.Ru давал только 200 Мб. И все сразу полезли в Google. Сейчас мы понимаем, что все это было не просто так. Google знал уже тогда, что пользовательские данные будут самым ценным товаром, видел будущее.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412723069.jpg" style="width: 800px; height: 1199px;" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>О понятии статусности по отношению к сайтам сложно говорить.&nbsp;​</strong>Мы измеряем любую вещь неопределенным количеством параметров. И мы не знаем, какие параметры для какого кейса рассматривались как приоритетные.&nbsp;Например, наш сайт для &laquo;Спартака&raquo; не слишком отличался посещаемостью, но для них этот проект создавался с целью привести их нынешнее IT-окружение в соответствие со статусом бренда. Раньше бросалось в глаза несоответствие между величиной и популярностью клуба и его отражением в интернете.</p>\r\n\r\n<p><strong>Сайты, как и приложения, должны со временем упрощаться в плане разнообразия дизайна.</strong> С одной стороны, гайдлайны операционных систем становятся все более продуманными, а сами приложения становятся все менее разнообразными, стремясь соответствовать этим гайдлайнам. И это очень правильно.</p>\r\n\r\n<p><strong>С сайтами произошло бы то же самое, что и с приложениями, если бы были единые технологии оформления.&nbsp;</strong>Но таких технологий нет и никогда не будет, потому что все, что с ними связано &mdash; это языки, HTML-стандарты, &mdash; управляется консорциумами, которые никогда не договорятся об унификации.</p>\r\n\r\n<p><strong>HTML должен умереть, он как каменное колесо для индустрии.</strong> И веб-дизайн тоже должен исчезнуть. Я могу легко представить ситуацию, когда через два-три года внешний вид сайта не будет программироваться создателем. Разработчик будет только компоновать составляющие, а операционная система отобразит конечный результат. Уже сейчас есть тому примеры: InstantView в Facebook, сервис Telegra.ph &mdash; в Telegram.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О российском диджитал-сегменте</span></p>\r\n\r\n<p><strong>Российский потребитель диджитала очень сильно избалован</strong>. Не только в плане дизайна. У нас и скорость интернета, и его доступность, и навыки дизайнеров и программистов выше, чем в Европе или США. По части веб-дизайна мы более мобильны. Если взять дизайн сайта газет Boston Globe или Wall Street Journal, можно заметить, что за Атлантикой дизайнеры и программисты меняются очень медленно. За 20 лет максимум они чуть-чуть изменят шрифты и внедрят адаптив для смартфонов.</p>\r\n\r\n<p><strong>Я работал в Америке в веб-дизайне и заметил, что делают они все очень качественно. </strong>Например, для интро сайта они могут арендовать целую площадку и три дня снимать фильм, чтобы потом превратить его в мультфильм. У нас бы сел моушен-дизайнер и за два часа нарисовал силуэты. То есть они подходят с точки зрения качества, но очень консервативно.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__textextract">\r\n<div class="article__textextract__text">\r\n<p>HTML должен умереть, он как каменное колесо для индустрии.</p>\r\n</div>\r\n\r\n<div class="article__textextract__social">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="\r\nHTML должен умереть, он как каменное колесо для индустрии.\r\n">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>У нас люди любят всякие красивые вещи. </strong>Для нас первична форма, а не содержание. Поэтому наш дизайн &laquo;лучше&raquo; смотрится. Ну и мы больше нацелены на вау-фактор. Американца ты спросишь, почему сайт New York Times такой неудобный, и он просто не поймет тебя: вот новости, вот текст, все работает. Что еще нужно? С другой стороны, на Западе, стараниями того же Apple, чувство стиля в диджитале выращивалось годами. Даже Android из соображений конкуренции начал подгонять свои гайдлайны, чтобы сделать красивый дизайн оболочки операционной системы и приложений. Потому что разница становилась со временем слишком очевидной.</p>\r\n\r\n<p><strong>Большое внимание дизайну уделяют те, кто оперирует крупными платформами.</strong> И они тянут всех остальных за собой.&nbsp;В России таких гигантов нет, зато есть разработчики-энтузиасты, которые где-то что-то подсматривают и стараются переносить к нам в красивом виде.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О проектах Finch</span></p>\r\n\r\n<p><strong>Комплекс проектов для &laquo;Спартака&raquo; получился очень красивый.</strong> При запуске мы смогли защитить визуальную составляющую от разных опасностей, правда, она продержалась недолго. Это естественный процесс развития любого продукта &mdash; когда ты только что-то запускаешь, оно выглядит цельным. Но чем дальше от запуска, тем быстрее продукт разлагается и разрушается.</p>\r\n\r\n<p><strong>Где-то полгода, и ты отпускаешь хватку, сползаешь и говоришь: все, окей, пусть будет что будет.</strong> И больше не следишь за консистентностью продукта (то есть за стабильной работой сайта. <em>&mdash; &laquo;РБК Стиль&raquo;</em>). Тут вопрос чисто финансовой мотивации.</p>\r\n\r\n<p><strong>Поддерживать качество быстро растущего продукта &mdash; очень трудоемкая работа, результат которой не всегда понятен для клиента.</strong> У него, допустим, десять партнеров, каждый из которых требует запустить свои функции.</p>\r\n\r\n<p><strong>Тяжело следить за тем, чтобы и работа делалась, и цельность сохранялась.</strong> Это ресурсоемкий процесс, и за него нужно доплачивать. И все равно в конце концов наступает момент, когда необходимо сделать полный редизайн и придумать все с нуля.&nbsp;Это происходит и из-за процессов внутри продукта, и из-за того, что внешняя среда тоже меняется: каждый год обновляются компоненты операционных систем, приложения, меняется конкурентное окружение, в целом развивается дизайн, возможности и так далее.</p>\r\n\r\n<p><strong>После скачка проекта на следующий уровень снова начнется процесс разложения. </strong>Для долгоживущих проектов это нормально. И хороший клиент это понимает.&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412723278.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:26px;">О стереотипных программистах</span></p>\r\n\r\n<p><strong>Клиенты возрастом где-то за 45</strong> <strong>ожидают увидеть таких гиков из фильмов в рваных джинсах, свитере, говорящих непонятные слова.</strong> Если ты попадаешь в этот образ, то у них что-то щелкает и они тебе сразу доверяют.&nbsp; Если приедешь в пиджаке-галстуке, то они могут не понять, подумать, что ты такой же, как они, а они ничего не умеют в диджитале.</p>\r\n\r\n<p><strong>Клиенты моложе начинают условно соревноваться</strong> <strong>с тобой, кто моднее.</strong> Кто больше продуктолог, кто больше кастдев (от customer developer, тестировщик прототипа на потенциальных потребителях. &mdash; &laquo;РБК Стиль&raquo;) и тому подобное. Но это просто наблюдение, не закономерность.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size: 26px;">Об отношении к жизни</span></p>\r\n\r\n<p><strong>Чтобы мыслить на 10 лет вперед,</strong> <strong>начать нужно с того, чтобы не мыслить на 10 лет назад. </strong>Просто поменьше думать о том, что было раньше. В отношении себя и того, что ты делаешь. Как только появляется новый проект, все, что ты делал на предыдущем, &mdash; уже история, которую нужно забыть.</p>\r\n\r\n<p><strong>Силы появляются от деятельности</strong>. Если пытаться их экономить или копить &mdash; их станет только меньше.</p>\r\n\r\n<p><strong>Я даже стараюсь скрывать образование историка, потому что обычно начинаются вопросы &laquo;О, а как ты думаешь о том и об этом?&raquo;.</strong> Особенно когда с таксистами разговариваешь &mdash; они очень любят докапываться, если узнают, что у тебя историческое образование. Так что хобби никак не связано с ним.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755444412722931.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><strong>Эмпатия отдельно &mdash; работа отдельно. </strong>Комфортные условия обеспечиваются не отношениями с клиентом, а принятым регламентом работы. Все, что сверх этого&nbsp;&mdash; симпатия, иногда даже дружба, &mdash; идет как бонус, нечестное конкурентное преимущество. Оно, конечно, очень полезно и помогает в работе, но не является обязательным условием.</p>\r\n\r\n<p><strong>Музыка мне нравилась всегда, она выступала мотивацией, в том числе и в работе.</strong> Плюс все вещи, связанные с оформлением концертных постеров, сайтов музыкальных групп, &mdash; визуально красивые. Это меня привлекает. Я долгое время занимаюсь музыкой, у меня куча инструментов. Правда, посвятить себя музыке полностью сейчас не получается.</p>\r\n\r\n<div class="article__textextract">\r\n<div class="article__textextract__text">\r\n<p>Чтобы мыслить на 10 лет вперед, начать нужно с того, чтобы не мыслить на 10 лет назад.</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\r\n\r\n<div class="article__textextract__social">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="\r\nЧтобы мыслить на 10 лет вперед, начать нужно с того, чтобы не мыслить на 10 лет назад.\r\n\r\n\r\n">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p><strong>Я окончил музыкальную школу</strong> <strong>по классу виолончели, потом занимался саксофоном.</strong> С тех пор играл на гитаре, барабанах.&nbsp;Барабаны даются сложнее всего. У тебя либо есть навык удержания четкого&nbsp;ритма, либо нет. У меня нет. Поэтому с барабанами не особенно. Но зато с клавишными все хорошо.</p>\r\n\r\n<p><strong>Когда ты семь лет имеешь дело с одним инструментом&nbsp;(с детского возраста), занятие начинает дико надоедать.</strong> Музыкальная школа у меня с пяти лет началась. Смысл в том, что каждый инструмент чем-то привлекает в разное время, и когда берешься за новый &mdash; кажется, что именно он идеальный. Точно так же и с проектами обстоят дела.</p>\r\n\r\n<p><strong>У меня в кабинете висит постер концерта Sly &amp; the Family Stone 1978 года в Сан-Франциско.</strong> Таскаю его с собой уже лет 10, ни одна вещь меня так не вдохновляла, как он.</p>\r\n\r\n<p><strong>Основная деятельность позволяет заниматься только утилитарными хобби &mdash; одно связано с тем, чтобы не умереть. </strong>Это бокс. А другое &mdash; чтобы побыстрее все успевать. Это мотоспорт.</p>\r\n\r\n<p><strong>Обычный человек без мотоцикла на встречу не&nbsp;успеет, а я успеваю. </strong>С присутствием мотоцикла в жизни для меня нормально, если встречу назначают где-то на Волгоградском шоссе в шесть вечера.&nbsp;</p>\r\n\r\n<p><strong>Я нахожусь в такой стадии просветления, когда уже не разделяешь, какой сайт красивый, а какой страшный. </strong>Но у меня есть свое понимание того, как должно быть хорошо.&nbsp;<span class="article__logo article__logo_no-left-margin"></span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\r\n</div>\r\n', '', '', '', '', '', '', 'ОСНОВАТЕЛЬ ДИДЖИТАЛ-СТУДИЯ FINCH ? ?? О ?ВЕБ-ДИЗАЙН В ?РОССИЯ И ?США', 'NBSP NBSP АВТОР ЕЛЕНА ФОМИН 19 ДЕКАБРЬ 2018 NBSP ПРОГРАММИСТ ДМИТРИЯ ЩИПАЧЕВ РУКОВОДИТЬ АГЕНТСТВО FINCH СРЕДИ ПРОЕКТ КОТОРЫЙ MDASH САЙТ LAQUO СПАРТАК RAQUO И ПРИЛОЖЕНИЕ ДЛЯ ТНТ-CLUB LAQUO РБК СТИЛЬ RAQUO УЗНАТЬ У ОН ЧТО ПРОИСХОДИТЬ С ВЕБ-ПРОСТРАНСТВО И КАК ЕГО МЕНЯТЬ МИР МОБИЛЬНЫЙ ПРИЛОЖЕНИЕ ПРИХОЖАЯ ОФИС FINCH ВСТРЕЧАТЬ ГОРА БОТИНОК ВОРОХ ПУХОВИК И МОТОЦИКЛ BMW НАД ГАРДЕРОБНОЙ-ГАРАЖ ЮТИТЬСЯ НЕБОЛЬШОЙ ОУПЕНСПЕЙС И ДИРЕКТОРСКИЙ КАБИНЕТ В НЕМОЙ NBSP MDASH УЮТНЫЙ ПОЛУМРАК ТАК ЛЕГЧИЙ РАБОТАТЬСЯ ДМИТРИЯ ЩИПАЧЕВ РУКОВОДИТЬ СТУДИЯ РАЗРАБОТКА ПОЧТИ 12 ГОД ВСЕ УСПЕВАТЬ БЛАГОДАРЯ МОТОЦИКЛ NBSP И МЫСЛИТЬ СТРАТЕГИЧЕСКИЙ ЕГО СТУДИЯ FINCH СКОНСТРУИРОВАТЬ И ЗАПУСТИТЬ САЙТ ДЛЯ LAQUO ДОМА-2 RAQUO LAQUO СТОЛОТЫЙ RAQUO И ФУТБОЛЬНЫЙ КЛУБ LAQUO СПАРТАК RAQUO ПАРАЛЛЕЛЬНО ПЕРЕКЛЮЧАТЬСЯ НА ПРИЛОЖЕНИЕ ДЛЯ СМАРТФОН С ДМИТРИЙ МЫ ПОГОВОРИТЬ О ТОМ КАК УСТРОЕННЫЙ ДИДЖИТАЛ-БИЗНЕС В РОССИЯ И ЧЕМ ОН ОТЛИЧАТЬСЯ ОТ ЗАПАДНЫЙ NBSP О ПРИЛОЖЕНИЕ И САЙТ ДИЗАЙН ПРИЛОЖЕНИЕ MDASH ЭТО ТО ЧЕМ ХОТЕТЬСЯ ЗАНИМАТЬСЯ В ОТЛИЧИЕ ОТ САЙТ ПОТОМУ ЧТО ВЕСЬ ЭСТЕТИК МОБИЛЬНЫЙ УСТРОЙСТВО РАСПОЛАГАТЬ К ТОМ ЧТОБЫ СОЗДАВАТЬ КРАСИВЫЙ ПРИЛОЖЕНИЕ ПЛЮС МЫ КОМФОРТНЫЙ РАБОТАТЬ С ТЕМ ХАРАКТЕР ПОЛЬЗОВАТЕЛЬСКИЙ ПОТРЕБЛЕНИЕ КОТОРЫЙ ПРИЛОЖЕНИЕ ОТЛИЧАТЬСЯ ОТ ВЕБ ДОПОЛНИТЬ И ВИРТУАЛЬНЫЙ РЕАЛЬНОСТЬ ДАВАТЬ ТОЛЬКО ВАУ-ФАКТОР ЕДИНСТВЕННЫЙ ОБЛАСТЬ В КОТОРЫЙ ОН ПРИМЕНИМЫЙ MDASH ЭТО РЕКЛАМА РЕКЛАМА ЗАНИМАТЬСЯ МЫ НЕ ЛЮБИМЫЙ NBSP НИКАКОЙ СОЦИАЛЬНО ПОЛЕЗНЫЙ ФУНКЦИЯ В ЭТО НЕТ ИНТЕРЕСНЫЙ РЕШИТЬ ДАЖЕ САМЫЙ ПРОСТОЙ ЗАДАЧА С КОТОРЫЙ ПОЛЬЗОВАТЕЛЬ СТАЛКИВАТЬСЯ ЕЖЕДНЕВНО ЧЕМ СОЗДАТЬ САМЫЙ УСПЕШНЫЙ РЕКЛАМНЫЙ КЕЙС НИ ОДИН ПРОДУКТ НЕ БЕСПЛАТНЫЙ С ТЫ ВСЕ РАВНО ПОЛУЧИТЬ ДЕНЬГА ВСТРОИТЬ ЛИ ЭТО ПОКУПКА ИЛИ ЭТО ПОДПИСКА ИЛИ ПОЛЬЗОВАТЕЛЬСКИЙ ДАННЫЙ ИЛИ РЕКЛАМА ИЛИ ВСЕ ВМЕСТЕ ТЫ ВСЕ РАВНО ПЛАТИТЬ ЗА ТО ЧЕМ ПОЛЬЗОВАТЬСЯ В ИНТЕРНЕТ ВСЕ МЫ ПОМНИТЬ КАК ПОЯВИТЬСЯ GMAIL С БЕСПЛАТНЫЙ 10 ГБ НА ДИСК КОГДА MAIL RU ДАВАТЬ ТОЛЬКО 200 МБ И ВСЕ СРАЗУ ПОЛЕЗТЬ В GOOGLE СЕЙЧАС МЫ ПОНИМАТЬ ЧТО ВСЕ ЭТО БЫЛО НЕ ПРОСТО ТАК GOOGLE ЗНАТЬ УЖЕ ТОГДА ЧТО ПОЛЬЗОВАТЕЛЬСКИЙ ДАННЫЙ БЫТЬ САМЫЙ ЦЕННЫЙ ТОВАР ВИДЕТЬ БУДУЩИЙ NBSP NBSP NBSP NBSP О ПОНЯТИЕ СТАТУСНОСТЬ ПО ОТНОШЕНИЕ К САЙТ СЛОЖНО ГОВОРИТЬ NBSP ??М ИЗМЕРЯТЬ ЛЮБОЙ ВЕЩЬ НЕОПРЕДЕЛЕННЫЙ КОЛИЧЕСТВО ПАРАМЕТР И МЫ НЕ ЗНАТЬ КАКОЙ ПАРАМЕТР ДЛЯ КАКОЙ КЕЙС РАССМАТРИВАТЬСЯ КАК ПРИОРИТЕТНЫЙ NBSP НАПРИМЕР НАШ САЙТ ДЛЯ LAQUO СПАРТАК RAQUO НЕ СЛИШКОМ ОТЛИЧАТЬСЯ ПОСЕЩАЕМОСТЬ НО ДЛЯ ОНИ ЭТОТ ПРОЕКТ СОЗДАВАТЬСЯ С ЦЕЛЬ ПРИВЕСТИ ИХ НЫНЕШНИЙ IT-ОКРУЖЕНИЕ В СООТВЕТСТВИЕ СО СТАТУС БРЕНД РАННИЙ БРОСАТЬСЯ В ГЛАЗ НЕСООТВЕТСТВИЕ МЕЖДУ ВЕЛИЧИНА И ПОПУЛЯРНОСТЬ КЛУБ И ЕГО ОТРАЖЕНИЕ В ИНТЕРНЕТ САЙТ КАК И ПРИЛОЖЕНИЕ ДОЛЖНЫЙ СО ВРЕМЯ УПРОЩАТЬСЯ В ПЛАН РАЗНООБРАЗИЕ ДИЗАЙН С ОДИН СТОРОНА ГАЙДЛАЙНА ОПЕРАЦИОННЫЙ СИСТЕМА СТАНОВИТЬСЯ ВСЕ БОЛЕЕ ПРОДУМАТЬ А САМ ПРИЛОЖЕНИЕ СТАНОВИТЬСЯ ВСЕ МЕНЕЕ РАЗНООБРАЗНЫЙ СТРЕМИТЬСЯ СООТВЕТСТВОВАТЬ ЭТО ГАЙДЛАЙНА И ЭТО ОЧЕНЬ ПРАВИЛЬНО С САЙТ ПРОИЗОЙТИ БЫ ТО ЖЕ САМЫЙ ЧТО И С ПРИЛОЖЕНИЕ ЕСЛИ БЫ БЫЛЬ ЕДИНЫЙ ТЕХНОЛОГИЯ ОФОРМЛЕНИЕ NBSP НО ТАКОЙ ТЕХНОЛОГИЯ НЕТ И НИКОГДА НЕ БЫТЬ ПОТОМУ ЧТО ВСЕ ЧТО С ОНИ СВЯЗАТЬ MDASH ЭТО ЯЗЫК HTML-СТАНДАРТ MDASH УПРАВЛЯТЬСЯ КОНСОРЦИУМ КОТОРЫЙ НИКОГДА НЕ ДОГОВОРИТЬСЯ ОБ УНИФИКАЦИЯ HTML ДОЛЖНЫЙ УМЕРЕТЬ ОН КАК КАМЕННЫЙ КОЛЕСО ДЛЯ ИНДУСТРИЯ И ВЕБ-ДИЗАЙН ТОЖЕ ДОЛЖНЫЙ ИСЧЕЗНУТЬ Я МОЧЬ ЛЕГКО ПРЕДСТАВИТЬ СИТУАЦИЯ КОГДА ЧЕРЕЗ ДВА-ТРЕТЬ ГОД ВНЕШНИЙ ВИД САЙТ НЕ БЫТЬ ПРОГРАММИРОВАТЬСЯ СОЗДАТЕЛЬ РАЗРАБОТЧИК БЫТЬ ТОЛЬКО КОМПОНОВАТЬ СОСТАВЛЯТЬ А ОПЕРАЦИОННЫЙ СИСТЕМА ОТОБРАЗИТЬ КОНЕЧНЫЙ РЕЗУЛЬТАТ УЖЕ СЕЙЧАС ЕСТЬ ТОМ ПРИМЕР INSTANTVIEW В FACEBOOK СЕРВИС TELEGRA PH MDASH В TELEGRAM NBSP О РОССИЙСКИЙ ДИДЖИТАЛ-СЕГМЕНТ РОССИЙСКИЙ ПОТРЕБИТЕЛЬ ДИДЖИТАТЬ ОЧЕНЬ СИЛЬНО ИЗБАЛОВАТЬ НЕ ТОЛЬКО В ПЛАН ДИЗАЙН У МЫ И СКОРОСТЬ ИНТЕРНЕТ И ЕГО ДОСТУПНОСТЬ И НАВЫК ДИЗАЙНЕР И ПРОГРАММИСТ ВЫШЕ ЧЕМ В ЕВРОПА ИЛИ США ПО ЧАСТИТЬ ВЕБ-ДИЗАЙН МЫ БОЛЕЕ МОБИЛЬНЫЙ ЕСЛИ ВЗЯТЬ ДИЗАЙН САЙТ ГАЗЕТА BOSTON GLOBE ИЛИ WALL STREET JOURNAL МОЖНО ЗАМЕТИТЬ ЧТО ЗА АТЛАНТИКА ДИЗАЙНЕР И ПРОГРАММИСТ МЕНЯТЬСЯ ОЧЕНЬ МЕДЛЕННО ЗА 20 ГОД МАКСИМУМ ОНИ ЧУТЬ-ЧУТЬ ИЗМЕНИТЬ ШРИФТ И ВНЕДРИТЬ АДАПТИТЬ ДЛЯ СМАРТФОН Я РАБОТАТЬ В АМЕРИКА В ВЕБ-ДИЗАЙН И ЗАМЕТИТЬ ЧТО ДЕЛАТЬ ОНИ ВСЕ ОЧЕНЬ КАЧЕСТВЕННО НАПРИМЕР ДЛЯ ИНТРО САЙТ ОНИ МОЧЬ АРЕНДОВАТЬ ЦЕЛОВАТЬ ПЛОЩАДКА И ТЕРЕТЬ ДЕНЬ СНИМАТЬ ФИЛЬМ ЧТОБЫ ПОТОМ ПРЕВРАТИТЬ ЕГО В МУЛЬТФИЛЬМ У МЫ БЫ СЕСТЬ МОУШЕН-ДИЗАЙНЕР И ЗА ДВА ЧАС НАРИСОВАТЬ СИЛУЭТ ТО ЕСТЬ ОНИ ПОДХОДИТЬ С ТОЧКА ЗРЕНИЕ КАЧЕСТВО НО ОЧЕНЬ КОНСЕРВАТИВНЫЙ NBSP HTML ДОЛЖНЫЙ УМЕРЕТЬ ОН КАК КАМЕННЫЙ КОЛЕСО ДЛЯ ИНДУСТРИЯ NBSP У МЫ ЧЕЛОВЕК ЛЮБИТЬ ВСЯКИЙ КРАСИВЫЙ ВЕЩИЙ ДЛЯ МЫ ПЕРВИЧНЫЙ ФОРМА А НЕ СОДЕРЖАНИЕ ПОЭТОМУ НАШ ДИЗАЙН LAQUO ЛУЧШЕ RAQUO СМОТРЕТЬСЯ НУ И МЫ БОЛЬШИЙ НАЦЕЛИТЬ НА ВАУ-ФАКТОР АМЕРИКАНЕЦ ТЫ СПРОСИТЬ ПОЧЕМУ САЙТ NEW YORK TIMES ТАКОЙ НЕУДОБНЫЙ И ОН ПРОСТО НЕ ПОЙМЕТ ТЫ ВОТ НОВОСТЬ ВОТ ТЕКСТ ВСЕ РАБОТАТЬ ЧТО ЕЩИЙ НУЖНЫЙ С ДРУГОЙ СТОРОНА НА ЗАПАД СТАРАНИЕ ТОГО ЖЕ APPLE ЧУВСТВО СТИЛЬ В ДИДЖИТАЛ ВЫРАЩИВАТЬСЯ ГОДАМИ ДАЖЕ ANDROID ИЗ СООБРАЖЕНИЕ КОНКУРЕНЦИЯ НАЧАЛО ПОДГОНЯТЬ СВОЕ ГАЙДЛАЙНА ЧТОБЫ СДЕЛАТЬ КРАСИВЫЙ ДИЗАЙН ОБОЛОЧКА ОПЕРАЦИОННЫЙ СИСТЕМА И ПРИЛОЖЕНИЕ ПОТОМУ ЧТО РАЗНИЦА СТАНОВИТЬСЯ СО ВРЕМЯ СЛИШКОМ ОЧЕВИДНЫЙ БОЛЬШОЙ ВНИМАНИЕ ДИЗАЙН УДЕЛЯТЬ ТОТ КТО ОПЕРИРОВАТЬ КРУПНЫЙ ПЛАТФОРМА И ОНИ ТЯНУТЫЙ ВЕСЬ ОСТАЛЬНОЙ ЗА СЕБЯ NBSP В РОССИЯ ТАКОЙ ГИГАНТ НЕТ ЗАТО ЕСТЬ РАЗРАБОТЧИКИ-ЭНТУЗИАСТ КОТОРЫЙ ГДЕ-ТО ЧТО-ТО ПОДСМАТРИВАТЬ И СТАРАТЬСЯ ПЕРЕНОСИТЬ К МЫ В КРАСИВЫЙ ВИД NBSP О ПРОЕКТ FINCH КОМПЛЕКС ПРОЕКТ ДЛЯ LAQUO СПАРТАК RAQUO ПОЛУЧИТЬСЯ ОЧЕНЬ КРАСИВЫЙ ПРИ ЗАПУСК МЫ СМОЧЬ ЗАЩИТИТЬ ВИЗУАЛЬНЫЙ СОСТАВЛЯТЬ ОТ РАЗНЫЙ ОПАСНОСТЬ ПРАВДА ОНА ПРОДЕРЖАТЬСЯ НЕДОЛГО ЭТО ЕСТЕСТВЕННЫЙ ПРОЦЕСС РАЗВИТИЕ ЛЮБОЙ ПРОДУКТ MDASH КОГДА ТЫ ТОЛЬКО ЧТО-ТО ЗАПУСКАТЬ ОНО ВЫГЛЯДЕТЬ ЦЕЛЬНЫЙ НО ЧЕМ ДАЛЁКИЙ ОТ ЗАПУСК ТЕМ БЫСТРЫЙ ПРОДУКТ РАЗЛАГАТЬСЯ И РАЗРУШАТЬСЯ ГДЕ-ТО ПОЛГОДА И ТЫ ОТПУСКАТЬ ХВАТКА СПОЛЗАТЬ И ГОВОРИТЬ ВСЕ ОКЬ ПУСТЬ БЫТЬ ЧТО БЫТЬ И БОЛЬШИЙ НЕ СЛЕДИТЬ ЗА КОНСИСТЕНТНОСТЬ ПРОДУКТ ТО ЕСТЬ ЗА СТАБИЛЬНЫЙ РАБОТА САЙТ MDASH LAQUO РБК СТИЛЬ RAQUO ТУТ ВОПРОС ЧИСТО ФИНАНСОВЫЙ МОТИВАЦИЯ ПОДДЕРЖИВАТЬ КАЧЕСТВО БЫСТРО РАСТИ ПРОДУКТ MDASH ОЧЕНЬ ТРУДОЕМКИЙ РАБОТА РЕЗУЛЬТАТ КОТОРЫЙ НЕ ВСЕГДА ПОНЯТНЫЙ ДЛЯ КЛИЕНТ У ОН ДОПУСТИМЫЙ ДЕСЯТЬ ПАРТНЕР КАЖДЫЙ ИЗ КОТОРЫЙ ТРЕБОВАТЬ ЗАПУСТИТЬ СВОЕ ФУНКЦИЯ ТЯЖЕЛО СЛЕДИТЬ ЗА ТЕМ ЧТОБЫ И РАБОТА ДЕЛАТЬСЯ И ЦЕЛЬНОСТЬ СОХРАНЯТЬСЯ ЭТО РЕСУРСОЕМКИЙ ПРОЦЕСС И ЗА ОН НУЖНЫЙ ДОПЛАЧИВАТЬ И ВСЕ РАВНО В КОНЕЦ КОНЕЦ НАСТУПАТЬ МОМЕНТ КОГДА НЕОБХОДИМЫЙ СДЕЛАТЬ ПОЛНЫЙ РЕДИЗАЙН И ПРИДУМАТЬ ВСЕ С НУЛЬ NBSP ЭТО ПРОИСХОДИТЬ И ИЗ-ЗА ПРОЦЕСС ВНУТРИ ПРОДУКТ И ИЗ-ЗА ТОГО ЧТО ВНЕШНИЙ СРЕДА ТОЖЕ МЕНЯТЬСЯ КАЖДЫЙ ГОД ОБНОВЛЯТЬСЯ КОМПОНЕНТ ОПЕРАЦИОННЫЙ СИСТЕМА ПРИЛОЖЕНИЕ МЕНЯТЬСЯ КОНКУРЕНТНЫЙ ОКРУЖЕНИЕ В ЦЕЛЫЙ РАЗВИВАТЬСЯ ДИЗАЙН ВОЗМОЖНОСТЬ И ТАК ДАЛЕЕ ПОСЛЕ СКАЧКА ПРОЕКТ НА СЛЕДУЮЩИЙ УРОВЕНЬ СНОВА НАЧНУТЬСЯ ПРОЦЕСС РАЗЛОЖЕНИЕ ДЛЯ ДОЛГОЖИВУЩИЙ ПРОЕКТ ЭТО НОРМАЛЬНО И ХОРОШИЙ КЛИЕНТ ЭТО ПОНИМАТЬ NBSP NBSP NBSP NBSP NBSP О СТЕРЕОТИПНЫЙ ПРОГРАММИСТ КЛИЕНТ ВОЗРАСТ ГДЕ-ТО ЗА 45 ОЖИДАТЬ УВИДЕТЬ ТАКОЙ ГИК ИЗ ФИЛЬМ В РВАНЫЙ ДЖИНСЫ СВИТЕР ГОВОРЯЩИЙ НЕПОНЯТНЫЙ СЛОВО ЕСЛИ ТЫ ПОПАДАТЬ В ЭТОТ ОБРАЗ ТО У ОНИ ЧТО-ТО ЩЕЛКАТЬ И ОНИ ТЫ СРАЗУ ДОВЕРЯТЬ NBSP ЕСЛИ ПРИЕХАТЬ В ПИДЖАКЕ-ГАЛСТУК ТО ОНИ МОЧЬ НЕ ПОНЯТЬ ПОДУМАТЬ ЧТО ТЫ ТАКОЙ ЖЕ КАК ОНИ А ОНИ НИЧЕГО НЕ УМЕТЬ В ДИДЖИТАЛ КЛИЕНТ МОЛОДОЙ НАЧИНАТЬ УСЛОВНО СОРЕВНОВАТЬСЯ С ТЫ КТО МОДНЫЙ КТО БОЛЬШИЙ ПРОДУКТОЛОГ КТО БОЛЬШИЙ КАСТДЕТЬ ОТ CUSTOMER DEVELOPER ТЕСТИРОВЩИК ПРОТОТИП НА ПОТЕНЦИАЛЬНЫЙ ПОТРЕБИТЕЛЬ MDASH LAQUO РБК СТИЛЬ RAQUO И ТОМ ПОДОБНЫЙ НО ЭТО ПРОСТО НАБЛЮДЕНИЕ НЕ ЗАКОНОМЕРНОСТЬ NBSP ОБ ОТНОШЕНИЕ К ЖИЗНЬ ЧТОБЫ МЫСЛИТЬ НА 10 ГОД ВПЕРЕД НАЧАТЬ НУЖНЫЙ С ТОГО ЧТОБЫ НЕ МЫСЛИТЬ НА 10 ГОД НАЗАД ПРОСТО МАЛЕНЬКИЙ ДУМАТЬ О ТОМ ЧТО БЫЛО РАННИЙ В ОТНОШЕНИЕ СЕБЯ И ТОГО ЧТО ТЫ ДЕЛАТЬ КАК ТОЛЬКО ПОЯВЛЯТЬСЯ НОВЫЙ ПРОЕКТ ВСЕ ЧТО ТЫ ДЕЛАТЬ НА ПРЕДЫДУЩИЙ MDASH УЖЕ ИСТОРИЯ КОТОРЫЙ НУЖНЫЙ ЗАБЫТЬ СИЛА ПОЯВЛЯТЬСЯ ОТ ДЕЯТЕЛЬНОСТЬ ЕСЛИ ПЫТАТЬСЯ ИХ ЭКОНОМИТЬ ИЛИ КОПИТЬ MDASH ИХ СТАТЬ ТОЛЬКО МАЛЕНЬКИЙ Я ДАЖЕ СТАРАТЬСЯ СКРЫВАТЬ ОБРАЗОВАНИЕ ИСТОРИК ПОТОМУ ЧТО ОБЫЧНО НАЧИНАТЬСЯ ВОПРОС LAQUO О А КАК ТЫ ДУМАТЬ О ТОМ И ОБ ЭТО RAQUO ОСОБЕННО КОГДА С ТАКСИСТ РАЗГОВАРИВАТЬ MDASH ОНИ ОЧЕНЬ ЛЮБИТЬ ДОКАПЫВАТЬСЯ ЕСЛИ УЗНАТЬ ЧТО У ТЫ ИСТОРИЧЕСКИЙ ОБРАЗОВАНИЕ ТАК ЧТО ХОББИ НИКАК НЕ СВЯЗАТЬ С ОН NBSP NBSP NBSP NBSP ЭМПАТИЯ ОТДЕЛЬНО MDASH РАБОТА ОТДЕЛЬНО КОМФОРТНЫЙ УСЛОВИЕ ОБЕСПЕЧИВАТЬСЯ НЕ ОТНОШЕНИЕ С КЛИЕНТ А ПРИНЯТЬ РЕГЛАМЕНТ РАБОТА ВСЕ ЧТО СВЕРХ ЭТО NBSP MDASH СИМПАТИЯ ИНОГДА ДАЖЕ ДРУЖБА MDASH ИДЕТ КАК БОНУС НЕЧЕСТНЫЙ КОНКУРЕНТНЫЙ ПРЕИМУЩЕСТВО ОНО КОНЕЧНЫЙ ОЧЕНЬ ПОЛЕЗНО И ПОМОГАТЬ В РАБОТА НО НЕ ЯВЛЯТЬСЯ ОБЯЗАТЕЛЬНЫЙ УСЛОВИЕ МУЗЫКА Я НРАВИТЬСЯ ВСЕГДА ОНА ВЫСТУПАТЬ МОТИВАЦИЯ В ТОМ ЧИСЛО И В РАБОТА ПЛЮС ВСЕ ВЕЩИЙ СВЯЗАТЬ С ОФОРМЛЕНИЕ КОНЦЕРТНЫЙ ПОСТЕР САЙТ МУЗЫКАЛЬНЫЙ ГРУППА MDASH ВИЗУАЛЬНО КРАСИВЫЙ ЭТО МЕНЬ ПРИВЛЕКАТЬ Я ДОЛГИЙ ВРЕМЯ ЗАНИМАТЬСЯ МУЗЫКА У МЕНЬ КУЧА ИНСТРУМЕНТ ПРАВДА ПОСВЯТИТЬ СЕБЯ МУЗЫКА ПОЛНОСТЬЮ СЕЙЧАС НЕ ПОЛУЧАТЬСЯ ЧТОБЫ МЫСЛИТЬ НА 10 ГОД ВПЕРЕД НАЧАТЬ НУЖНЫЙ С ТОГО ЧТОБЫ НЕ МЫСЛИТЬ НА 10 ГОД НАЗАД NBSP Я ОКОНЧИТЬ МУЗЫКАЛЬНЫЙ ШКОЛА ПО КЛАСС ВИОЛОНЧЕЛЬ ПОТОМ ЗАНИМАТЬСЯ САКСОФОН С ТОТ ПОРА ИГРАТЬ НА ГИТАРА БАРАБАН NBSP БАРАБАН ДАВАТЬСЯ СЛОЖНЫЙ ВСЕГО У ТЫ ЛИБО ЕСТЬ НАВЫК УДЕРЖАНИЕ ЧЕТКОГО NBSP РИТМ ЛИБО НЕТ У МЕНЬ НЕТ ПОЭТОМУ С БАРАБАН НЕ ОСОБЕННО НО ЗАТО С КЛАВИШНЫЙ ВСЕ ХОРОШО КОГДА ТЫ СЕМЬ ГОД ИМЕТЬ ДЕТЬ С ОДИН ИНСТРУМЕНТ NBSP С ДЕТСКИЙ ВОЗРАСТ ЗАНЯТИЕ НАЧИНАТЬ ДИКО НАДОЕДАТЬ МУЗЫКАЛЬНЫЙ ШКОЛА У МЕНЬ С ПЯТЬ ГОД НАЧАТЬСЯ СМЫСЛ В ТОМ ЧТО КАЖДЫЙ ИНСТРУМЕНТ ЧТО-ТО ПРИВЛЕКАТЬ В РАЗНЫЙ ВРЕМЯ И КОГДА БЕРЕСТЬСЯ ЗА НОВЫЙ MDASH КАЖЕТСЯ ЧТО ИМЕННО ОН ИДЕАЛЬНЫЙ ТОЧНО ТАК ЖЕ И С ПРОЕКТ ОБСТОЯТЬ ДЕТЬ У МЕНЬ В КАБИНЕТ ВИСЕТЬ ПОСТЕР КОНЦЕРТ SLY AMP THE FAMILY STONE 1978 ГОД В САН-ФРАНЦИСКО ТАСКАТЬ ЕГО С СЕБЯ УЖЕ ГОД 10 НИ ОДИН ВЕЩЬ МЕНЬ ТАК НЕ ВДОХНОВЛЯТЬ КАК ОН ОСНОВНЫЙ ДЕЯТЕЛЬНОСТЬ ПОЗВОЛЯТЬ ЗАНИМАТЬСЯ ТОЛЬКО УТИЛИТАРНЫЙ ХОББИ MDASH ОДИН СВЯЗАТЬ С ТЕМ ЧТОБЫ НЕ УМЕРЕТЬ ЭТО БОКС А ДРУГОЙ MDASH ЧТОБЫ БЫСТРЫЙ ВСЕ УСПЕВАТЬ ЭТО МОТОСПОРТ ОБЫЧНЫЙ ЧЕЛОВЕК БЕЗ МОТОЦИКЛ НА ВСТРЕЧА НЕ NBSP УСПЕТЬ А Я УСПЕВАТЬ С ПРИСУТСТВИЕ МОТОЦИКЛ В ЖИЗНЬ ДЛЯ МЕНЬ НОРМАЛЬНО ЕСЛИ ВСТРЕЧА НАЗНАЧАТЬ ГДЕ-ТО НА ВОЛГОГРАДСКИЙ ШОССЕ В ШЕСТЬ ВЕЧЕР NBSP Я НАХОДИТЬСЯ В ТАКОЙ СТАДИЯ ПРОСВЕТЛЕНИЕ КОГДА УЖЕ НЕ РАЗДЕЛЯТЬ КАКОЙ САЙТ КРАСИВЫЙ А КАКОЙ СТРАШНЫЙ НО У МЕНЬ ЕСТЬ СВОЕ ПОНИМАНИЕ ТОГО КАК ДОЛЖНЫЙ БЫТЬ ХОРОШО NBSP NBSP NBSP', 0, 0),

(2, 5, 'Киану Ривз: «Россия ассоциируется у меня с моральной силой»', '1552333645.png', '', '<p>В прокат вышел фильм &laquo;Профессионал&raquo; с Киану Ривзом в главной роли. Действие картины происходит в России. Накануне премьеры &laquo;РБК Стиль&raquo; поговорил с актером об особенностях русского менталитета, жизненных принципах и качестве современного кинематографа.</p>\r\n', '<div class="l-col-center__inner">\r\n<div class="article__overview ">\r\n<div class="article__rubric">&nbsp;</div>\r\n\r\n<div class="article__rubric"><img alt="" class="js-rbcslider-image" itemprop="image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380621811102.png" style="font-size: 1rem;" /></div>\r\n\r\n<div class="article__main-image">\r\n<div class="article__main-image__inner">\r\n<div class="article__main-image__copyrights">&nbsp;</div>\r\n\r\n<div class="article__main-image__copyrights">&nbsp;</div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__social js-social-likes">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="Киану Ривз: «Россия ассоциируется у меня с моральной силой»">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__info">\r\n<div class="article__author">Автор <span class="article__author__name"> <!--\r\n                    --><!--\r\n                        -->Марина Аржиловская<!--\r\n                        --><!--\r\n                    --><!--\r\n                --> </span></div>\r\n\r\n<div class="article__date" content="2018-09-28T08:22:29+03:00" itemprop="datePublished">28 сентября 2018</div>\r\n\r\n<div class="article__date" content="2018-09-28T08:22:29+03:00" itemprop="datePublished">&nbsp;</div>\r\n<meta itemprop="dateModified" content="2018-09-28T08:22:29+03:00"></div>\r\n\r\n<div class="article__subtitle">В прокат вышел фильм &laquo;Профессионал&raquo; с Киану Ривзом в главной роли. Действие картины происходит в России. Накануне премьеры &laquo;РБК Стиль&raquo; поговорил с актером об особенностях русского менталитета, жизненных принципах и качестве современного кинематографа.</div>\r\n</div>\r\n\r\n<div class="article__text" itemprop="articleBody">\r\n<p>В свои 54 года Киану Ривз продолжает оставаться одним из самых загадочных и закрытых актеров Голливуда. Он не перестает удивлять публику, выбирая для себя неожиданные роли, перемещаясь на метро как самый обычный пассажир и даже публикуя стихи.</p>\r\n\r\n<p>В этом году выходят сразу три фильма с участием Ривза, и все &mdash; в разных жанрах. Среди них &mdash; криминальная драма &laquo;Сибирь&raquo;, поступившая в российский прокат под названием &laquo;Профессионал&raquo;. Это довольно смелый эксперимент для актера: во время съемок и в процессе&nbsp;подготовки к ним&nbsp;он больше трех месяцев провел в разных регионах России &mdash; от Санкт-Петербурга до Новосибирска. Ривз играет торговца редкими видами бриллиантов, который оказывается вовлечен в криминальную сделку и совершает &laquo;рискованное путешествие&raquo; в Сибирь. Проект был задуман еще четыре года назад. В изначальной версии вместо Петербурга была Москва. Корректировку в сценарий внесли за два года до съемок.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Россия. Криминал. Сибирь. Если описывать фильм, то в таком порядке? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Ну, не совсем в таком <em> (смеется)</em>. На первом месте Россия, затем &mdash; Сибирь, а потом уж где-то криминал.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380628105421.png" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__text">Кадр из фильма &laquo;Профессионал&raquo;</div>\r\n\r\n<div class="article__picture_big__source">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Почему именно так? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Потому что Россия лично у меня ассоциируется в первую очередь с моральной силой. Для вас это норма. Только в жизни русские не такие суровые, как это обычно преподносится. В Петербурге, например, когда мы снимали сцены на улицах, люди очень робко просили автограф, стараясь не беспокоить лишний раз. Это выглядело довольно мило.</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> При этом в фильме &laquo;Профессионал&raquo; вы противостоите отнюдь не милым людям, а российским криминальным структурам. Почему вам захотелось участвовать в этом проекте? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Просто у меня таких еще не было. И сценарий оказался довольно увлекательным: там есть и любовный треугольник, и экшн, и колкий юмор.</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Сценарий чем-то напомнил &laquo;Джона Уика&raquo;.</span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Хм &hellip; согласен, мне не впервой ходить с дробовиком <em> (изображает крутого парня &mdash; прим. ред.). </em> На самом деле получилась такая классная колоритная картина.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> То есть это чистой воды эксперимент? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Скорее новый творческий опыт. К тому&nbsp;же я имел возможность расширить свой русский словарный запас.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Вы изучаете русский язык? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Знаю только самые ходовые фразы, к сожалению. Свободным владением пока похвастаться не могу.</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Вы&nbsp;же не только говорите на русском, но и читаете русскоязычные книги? </span></span><strong> </strong></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Классические пьесы и небольшие рассказы. Льва Толстого еще не осилил. Но пожалуйста, не просите меня сейчас произнести что-нибудь на русском, я еще не избавился от акцента. Пока только прислушиваюсь к вашему языку. Он красиво звучит, музыкально, в нем много интонаций и смысловых тональностей.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div><img class="js-galleryInfinity-img js-rbcslider-image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380628290003.png" /></div>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<div><img class="js-galleryInfinity-img js-rbcslider-image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380627211853.png" /></div>\r\n\r\n<div>&nbsp;</div>\r\n\r\n<div><img class="js-galleryInfinity-img js-rbcslider-image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380627135444.png" /></div>\r\n\r\n<div class="gallery_infinity__info">\r\n<div class="gallery_infinity__navigation js-galleryInfinity-nav">\r\n<div class="gallery_infinity__arrow gallery_infinity__arrow__last js-galleryInfinity-last">&nbsp;</div>\r\n</div>\r\n\r\n<div class="gallery_infinity__description js-galleryInfinity-text" style="height: 45px;">\r\n<div class="gallery_infinity__flip" style="display: block;">\r\n<div>Кадр из фильма &laquo;Профессионал&raquo;\r\n<div class="gallery_infinity__copyright">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<div class="gallery_infinity__flip" style="display: none;">\r\n<div>Кадр из фильма &laquo;Профессионал&raquo;\r\n<div class="gallery_infinity__copyright">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<div class="gallery_infinity__flip" style="display: none;">\r\n<div>Кадр из фильма &laquo;Профессионал&raquo;\r\n<div class="gallery_infinity__copyright">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Кстати, о творчестве и языке. Вы выпустили уже две книги с собственными текстами, в том числе со стихами&nbsp;<em>(&laquo;Ода счастью&raquo; и &laquo;Тени&raquo; выпущены совместно с иллюстратором Александрой Грант&nbsp;&mdash; прим. ред.)</em>. Планируете издавать третью? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Вам настолько понравились две предыдущих? <em> (Немного задумавшись.) </em> Для меня поэзия &mdash; это не бизнес и даже не хобби. У меня накопилось много мыслей, и мне хотелось поделиться ими с миром&hellip; Как-то так. Не знаю, почему некоторые приняли это за троллинг. Я просто был честен. Рассказать о том, что спрятано внутри, бывает не так легко, как может показаться на первый взгляд. Я никого ни к чему не призывал, не хотел давать каких-то оценок. Мне было что сказать &mdash; и я это сделал. Не нужно искать скрытых мотивов и смыслов, думать об этом. Возвращаясь к вашему вопросу, отвечу так: вполне возможно, но это будет что-то другое, в другом жанре.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А мне как раз понравились ваша философская лирика, оттенки меланхолии. </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Искренне не советую в нее погружаться. Я люблю пошутить и часто не прочь подурачиться, не понимаю, почему меня упорно преподносят аудитории как эксперта по мрачному настроению&hellip;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Да, но вы однажды сказали: &laquo;Всегда нужно знать, что кому-то еще хуже, чем вам&raquo; &mdash; и фактически привели пример определенного временного отрезка из своей жизни. Это было смело.</span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> С тех пор многое изменилось. Я стараюсь заполнять свое время событиями или вещами, от которых получаю удовольствие.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Мотоциклы? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Одно из них, да. Каждый раз, когда я сажусь за руль, во мне просыпается что-то от зверя, необъяснимое дикое удовольствие. Это сложно объяснить словами. Это даже не выброс адреналина, а головокружительное ощущение безграничной свободы.</p>\r\n\r\n<div class="article__textextract">\r\n<div class="article__textextract__text">\r\n<p>Не понимаю, почему меня упорно преподносят аудитории как эксперта по мрачному настроению.</p>\r\n</div>\r\n\r\n<div class="article__textextract__social">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="\r\nНе понимаю, почему меня упорно преподносят аудитории как эксперта по мрачному настроению.\r\n">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n</div>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А еще? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Опасный вопрос, не боитесь?</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> А стоит? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Пусть это останется моей тайной.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Есть ощущение, что у вас слишком много тайн, нет? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Не так много, как хотелось&nbsp;бы. Публичность &mdash; плата за успех.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Обычно здесь упоминают одиночество&hellip; </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Красивый способ вывести разговор на тему о личной жизни, но я не куплюсь! <em> (Смеется.) </em></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Вы до сих пор продолжаете искать своего &laquo;идеального человека&raquo;? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Да. И не намерен останавливаться. Если повезет &mdash; значит, я все-таки чем-то заслужил великое счастье.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Почему вы думаете, что счастье нужно именно заслужить? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Жизненный опыт. Я&nbsp;бы не хотел сейчас выводить длинную теорию, и вообще это долгий и обстоятельный разговор. Сформулирую кратко: есть определенный алгоритм Вселенной. Его нетрудно вычислить, если быть наблюдательным. Вы здорово сэкономите свое время, если поверите мне на слово.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755380624923742.png" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__text">Кадр из фильма &laquo;Профессионал&raquo;</div>\r\n\r\n<div class="article__picture_big__source">&copy; kinopoisk.ru</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Ваше близкое окружение называет вас самым терпеливым человеком на планете. </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Серьезно? Вас разыграли! Я бываю очень раздражителен, подвержен сильным эмоциям и порывам, да, еще и жутко требовательным.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Трудно в это поверить.</span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Да-да, мне часто не хватает выдержки&hellip;</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Но меня вы прождали целый час. Я не знаю ни одну голливудскую знаменитость, способную на такой подвиг.<em> (По не зависящим от корреспондента &laquo;РБК Стиль&raquo; и героя причинам интервью началось позже запланированного времени. Киану Ривз отнесся к ситуации с пониманием и продолжил ждать нашего журналиста в отеле &mdash; прим. ред.) </em></span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Я всегда держу слово. Раз договорились об интервью &mdash; оно должно было состояться. Это, наверное, выглядит старомодно или недостаточно по-светски, но мне кажется это правильным.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А какие у вас еще жизненные правила? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Не знаю, я не замечал за собой, что живу по каким-то правилам. Важно делать то, что ты считаешь разумным и необходимым, быть полезным кому-то в нужные трудные моменты, от которых никто не застрахован.</p>\r\n\r\n<p>Идти вразрез с собственными убеждениями нельзя &mdash; это точно. Еще нужно быть самим собой &mdash; это, пожалуй, самое главное. Вообще интересный вопрос. Я сразу начал копаться в себе&hellip;</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Многие постоянно говорят о кризисе в кино, о том, что найти качественный сценарий становится все сложнее. Вы с этим согласны? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Абсолютно! На поиск не идеального, но хорошего сценария уходит не меньше пяти-шести месяцев. Я очень кропотливо изучаю тексты и стараюсь разглядеть если не безупречную работу, то хотя&nbsp;бы перспективную основу, которую можно редактировать и наполнять. Сейчас редко попадается образцовое талантливое кино, в основном планка &mdash; это просто неплохая драматургия. Уверен, что зритель чувствует разницу.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Какие фильмы с вашим участием вы считаете главным достижением в своей карьере? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Я&nbsp;бы назвал фильмы, за которые мне точно никогда не будет стыдно. &laquo;Адвокат Дьявола&raquo;, &laquo;На гребне волны&raquo;, &laquo;Матрица&raquo;, &laquo;Помутнение&raquo;. Для меня еще очень важен язык картины, диалоги. Может быть, это идет со времен моей театральной деятельности, не знаю. Язык во многом определяет историю, он имеет не меньшее значение, чем сам сюжет и режиссерский стиль.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> А еще &laquo;Константин&raquo; и &laquo;Дракула&raquo; Копполы. Кстати, как вы считаете &mdash; в вашей реальной жизни присутствует мистика? </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> У меня есть одна сверхспособность: я оказываюсь в нужное время в нужном месте. Это ведь можно назвать интуицией или шестым чувством?</p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span><span style="color:#808080;"> Скорее чутьем. </span></span></p>\r\n\r\n<p><span style="font-size:24px;"><span style="color:#FF0000;">&mdash;</span></span> Ну или так, да. Но в мистику я верю, это правда.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Вы следите за своим гардеробом, за современной модой? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Да, мне это интересно. Я очень тщательно подбираю себе костюмы. Люблю черный и серый цвета. Предпочитаю обувь в классическом стиле.</p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span><span style="color:#808080;"><span style="font-size:24px;"> Без чего вы не мыслите свой день? </span></span></p>\r\n\r\n<p><span style="color:#FF0000;"><span style="font-size:24px;">&mdash;</span></span> Без яичницы с беконом, чашки кофе и книг. Хотя нет, без кофе могу обойтись, а без книги точно нет.&nbsp;<span class="article__logo article__logo_no-left-margin"></span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\r\n</div>\r\n', '', '', '', '', '', '', 'КИАНА РИВЗ ?РОССИЯ АССОЦИИРОВАТЬСЯ У МЕНЬ С МОРАЛЬНЫЙ СИЛОЙ ?', 'NBSP NBSP NBSP АВТОР МАРИН АРЖИЛОВСКИЙ 28 СЕНТЯБРЬ 2018 NBSP В ПРОКАТ ВЫЙТИ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO С КИАНА РИВЗЫЙ В ГЛАВНЫЙ РОЛЯ ДЕЙСТВИЕ КАРТИНА ПРОИСХОДИТЬ В РОССИЯ НАКАНУНЕ ПРЕМЬЕР LAQUO РБК СТИЛЬ RAQUO ПОГОВОРИТЬ С АКТЕР ОБ ОСОБЕННОСТЬ РУССКИЙ МЕНТАЛИТЕТ ЖИЗНЕННЫЙ ПРИНЦИП И КАЧЕСТВО СОВРЕМЕННЫЙ КИНЕМАТОГРАФ В СВОЕ 54 ГОД КИАНА РИВЗ ПРОДОЛЖАТЬ ОСТАВАТЬСЯ ОДИН ИЗ САМЫЙ ЗАГАДОЧНЫЙ И ЗАКРЫТЫЙ АКТЕР ГОЛЛИВУД ОН НЕ ПЕРЕСТАЯТЬ УДИВЛЯТЬ ПУБЛИКА ВЫБИРАТЬ ДЛЯ СЕБЯ НЕОЖИДАННЫЙ РОЛЯ ПЕРЕМЕЩАТЬСЯ НА МЕТРО КАК САМЫЙ ОБЫЧНЫЙ ПАССАЖИР И ДАЖЕ ПУБЛИКОВАТЬ СТИХ В ЭТО ГОД ВЫХОДИТЬ СРАЗУ ТЕРЕТЬ ФИЛЬМ С УЧАСТИЕ РИВЗ И ВСЕ MDASH В РАЗНЫЙ ЖАНР СРЕДИ ОНИ MDASH КРИМИНАЛЬНЫЙ ДРАМА LAQUO СИБИРЬ RAQUO ПОСТУПИТЬ В РОССИЙСКИЙ ПРОКАТ ПОД НАЗВАНИЕ LAQUO ПРОФЕССИОНАЛ RAQUO ЭТО ДОВОЛЬНО СМЕЛЫЙ ЭКСПЕРИМЕНТ ДЛЯ АКТЕР ВО ВРЕМЯ СЪЕМКА И В ПРОЦЕСС NBSP ПОДГОТОВКА К ОН NBSP ОН БОЛЬШИЙ ТРИ МЕСЯЦ ПРОВЕТЬ В РАЗНЫЙ РЕГИОН РОССИЯ MDASH ОТ САНКТ-ПЕТЕРБУРГ ДО НОВОСИБИРСК РИВЗ ИГРАТЬ ТОРГОВЕЦ РЕДКИЙ ВИД БРИЛЛИАНТ КОТОРЫЙ ОКАЗЫВАТЬСЯ ВОВЛЕЧЕНЫЙ В КРИМИНАЛЬНЫЙ СДЕЛКА И СОВЕРШАТЬ LAQUO РИСКОВАННЫЙ ПУТЕШЕСТВИЕ RAQUO В СИБИРЬ ПРОЕКТ БЫТЬ ЗАДУМАТЬ ЕЩИЙ ЧЕТЫРЕ ГОД НАЗАД В ИЗНАЧАЛЬНЫЙ ВЕРСИЯ ВМЕСТО ПЕТЕРБУРГ БЫТЬ МОСКВА КОРРЕКТИРОВКА В СЦЕНАРИЙ ВНЕСТИ ЗА ДВА ГОД ДО СЪЕМКА MDASH РОССИЯ КРИМИНАЛ СИБИРЬ ЕСЛИ ОПИСЫВАТЬ ФИЛЬМ ТО В ТАКОЙ ПОРЯДОК MDASH НУ НЕ СОВСЕМ В ТАКОЙ СМЕТЬСЯ НА ПЕРВЫЙ МЕСТО РОССИЯ ЗАТЕМ MDASH СИБИРЬ А ПОТОМ УЖ ГДЕ-ТО КРИМИНАЛ NBSP КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU NBSP MDASH ПОЧЕМУ ИМЕННО ТАК MDASH ПОТОМУ ЧТО РОССИЯ ЛИЧНО У МЕНЬ АССОЦИИРОВАТЬСЯ В ПЕРВЫЙ ОЧЕРЕДЬ С МОРАЛЬНЫЙ СИЛОЙ ДЛЯ ВЫ ЭТО НОРМА ТОЛЬКО В ЖИЗНЬ РУССКИЙ НЕ ТАКОЙ СУРОВЫЙ КАК ЭТО ОБЫЧНО ПРЕПОДНОСИТЬСЯ В ПЕТЕРБУРГ НАПРИМЕР КОГДА МЫ СНИМАТЬ СЦЕНА НА УЛИЦА ЧЕЛОВЕК ОЧЕНЬ РОБКО ПРОСИТЬ АВТОГРАФ СТАРАТЬСЯ НЕ БЕСПОКОИТЬ ЛИШНИЙ РАЗ ЭТО ВЫГЛЯДЕТЬ ДОВОЛЬНО МИЛО MDASH ПРИ ЭТО В ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO ВЫ ПРОТИВОСТОЯТЬ ОТНЮДЬ НЕ МИЛЫЙ ЧЕЛОВЕК А РОССИЙСКИЙ КРИМИНАЛЬНЫЙ СТРУКТУРА ПОЧЕМУ ВЫ ЗАХОТЕТЬСЯ УЧАСТВОВАТЬ В ЭТО ПРОЕКТ MDASH ПРОСТО У МЕНЬ ТАКОЙ ЕЩИЙ НЕ БЫЛО И СЦЕНАРИЙ ОКАЗАТЬСЯ ДОВОЛЬНО УВЛЕКАТЕЛЬНЫЙ ТАМ ЕСТЬ И ЛЮБОВНЫЙ ТРЕУГОЛЬНИК И ЭКШН И КОЛКИЙ ЮМОР MDASH СЦЕНАРИЙ ЧТО-ТО НАПОМНИТЬ LAQUO ДЖОН УИКА RAQUO MDASH ХМ HELLIP СОГЛАСНЫЙ Я НЕ ВПЕРВОЙ ХОДИТЬ С ДРОБОВИК ИЗОБРАЖАТЬ КРУТОЙ ПАРЕНЬ MDASH ПРИМА РЕДАКЦИЯ НА САМЫЙ ДЕЛО ПОЛУЧИТЬСЯ ТАКАТЬ КЛАССНЫЙ КОЛОРИТНЫЙ КАРТИНА MDASH ТО ЕСТЬ ЭТО ЧИСТЫЙ ВОД ЭКСПЕРИМЕНТ MDASH СКОРЫЙ НОВЫЙ ТВОРЧЕСКИЙ ОПЫТ К ТОМ NBSP ЖЕ Я ИМЕТЬ ВОЗМОЖНОСТЬ РАСШИРИТЬ СВОЙ РУССКИЙ СЛОВАРНЫЙ ЗАПАС MDASH ВЫ ИЗУЧАТЬ РУССКИЙ ЯЗЫК MDASH ЗНАТЬ ТОЛЬКО САМЫЙ ХОДОВОЙ ФРАЗА К СОЖАЛЕНИЕ СВОБОДНЫЙ ВЛАДЕНИЕ ПОКА ПОХВАСТАТЬСЯ НЕ МОЧЬ MDASH ВЫ NBSP ЖЕ НЕ ТОЛЬКО ГОВОРИТЬ НА РУССКИЙ НО И ЧИТАТЬ РУССКОЯЗЫЧНЫЙ КНИГА MDASH КЛАССИЧЕСКИЙ ПЬЕСА И НЕБОЛЬШОЙ РАССКАЗ ЛЕВ ТОЛСТОЙ ЕЩИЙ НЕ ОСИЛИТЬ НО ПОЖАЛУЙСТА НЕ ПРОСИТЬ МЕНЬ СЕЙЧАС ПРОИЗНЕСТИ ЧТО-НИБУДЬ НА РУССКИЙ Я ЕЩИЙ НЕ ИЗБАВИТЬСЯ ОТ АКЦЕНТ ПОКА ТОЛЬКО ПРИСЛУШИВАТЬСЯ К ВАШ ЯЗЫК ОН КРАСИВО ЗВУЧАТЬ МУЗЫКАЛЬНО В НЕМОЙ МНОГО ИНТОНАЦИЯ И СМЫСЛОВОЙ ТОНАЛЬНОСТЬ NBSP NBSP NBSP NBSP КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU NBSP MDASH КСТАТИ О ТВОРЧЕСТВО И ЯЗЫК ВЫ ВЫПУСТИТЬ УЖЕ ДВА КНИГА С СОБСТВЕННЫЙ ТЕКСТ В ТОМ ЧИСЛО СО СТИХ NBSP LAQUO ОДА СЧАСТИЕ RAQUO И LAQUO ТЕНИТЬ RAQUO ВЫПУСТИТЬ СОВМЕСТНО С ИЛЛЮСТРАТОР АЛЕКСАНДРА ГРАНТ NBSP MDASH ПРИМА РЕДАКЦИЯ ПЛАНИРОВАТЬ ИЗДАВАТЬ ТРЕТЬ MDASH ВЫ НАСТОЛЬКО ПОНРАВИТЬСЯ ДВА ПРЕДЫДУЩИЙ НЕМНОГО ЗАДУМАТЬСЯ ДЛЯ МЕНЬ ПОЭЗИЯ MDASH ЭТО НЕ БИЗНЕС И ДАЖЕ НЕ ХОББИ У МЕНЬ НАКОПИТЬСЯ МНОГО МЫСЛЬ И Я ХОТЕТЬСЯ ПОДЕЛИТЬСЯ ОНИ С МИР HELLIP КАК-ТО ТАК НЕ ЗНАТЬ ПОЧЕМУ НЕКОТОРЫЙ ПРИНЯТЬ ЭТО ЗА ТРОЛЛИНГ Я ПРОСТО БЫТЬ ЧЕСТНЫЙ РАССКАЗАТЬ О ТОМ ЧТО СПРЯТАТЬ ВНУТРИ БЫВАТЬ НЕ ТАК ЛЕГКО КАК МОЖЕТ ПОКАЗАТЬСЯ НА ПЕРВЫЙ ВЗГЛЯД Я НИКТО НИ К ЧТО НЕ ПРИЗЫВАТЬ НЕ ХОТЕТЬ ДАВАТЬ КАКОЙ-ТО ОЦЕНКА Я БЫЛО ЧТО СКАЗАТЬ MDASH И Я ЭТО СДЕЛАТЬ НЕ НУЖНЫЙ ИСКАТЬ СКРЫТЫЙ МОТИВ И СМЫСЛ ДУМАТЬ ОБ ЭТО ВОЗВРАЩАТЬСЯ К ВАШ ВОПРОС ОТВЕТИТЬ ТАК ВПОЛНЕ ВОЗМОЖНО НО ЭТО БЫТЬ ЧТО-ТО ДРУГОЙ В ДРУГ ЖАНР MDASH А Я КАК РАЗ ПОНРАВИТЬСЯ ВАШ ФИЛОСОФСКИЙ ЛИРИК ОТТЕНОК МЕЛАНХОЛИЯ MDASH ИСКРЕННЕ НЕ СОВЕТОВАТЬ В НЕЯ ПОГРУЖАТЬСЯ Я ЛЮБИТЬ ПОШУТИТЬ И ЧАСТО НЕ ПРОЧЬ ПОДУРАЧИТЬСЯ НЕ ПОНИМАТЬ ПОЧЕМУ МЕНЬ УПОРНО ПРЕПОДНОСИТЬ АУДИТОРИЯ КАК ЭКСПЕРТ ПО МРАЧНЫЙ НАСТРОЕНИЕ HELLIP MDASH ДА НО ВЫ ОДНАЖДЫ СКАЗАТЬ LAQUO ВСЕГДА НУЖНЫЙ ЗНАТЬ ЧТО КТО-ТО ЕЩИЙ ПЛОХО ЧЕМ ВЫ RAQUO MDASH И ФАКТИЧЕСКИЙ ПРИВЕСТИ ПРИМЕР ОПРЕДЕЛЕННЫЙ ВРЕМЕННЫЙ ОТРЕЗКА ИЗ СВОЙ ЖИЗНЬ ЭТО БЫЛО СМЕЛО MDASH С ТОТ ПОРА МНОГИЙ ИЗМЕНИТЬСЯ Я СТАРАТЬСЯ ЗАПОЛНЯТЬ СВОЕ ВРЕМЯ СОБЫТИЕ ИЛИ ВЕЩЬ ОТ КОТОРЫЙ ПОЛУЧАТЬ УДОВОЛЬСТВИЕ MDASH МОТОЦИКЛ MDASH ОДИН ИЗ ОНИ ДА КАЖДЫЙ РАЗ КОГДА Я САДИТЬСЯ ЗА РУЛЬ ВО Я ПРОСЫПАТЬСЯ ЧТО-ТО ОТ ЗВЕРЬ НЕОБЪЯСНИМЫЙ ДИКИЙ УДОВОЛЬСТВИЕ ЭТО СЛОЖНО ОБЪЯСНИТЬ СЛОВО ЭТО ДАЖЕ НЕ ВЫБРОС АДРЕНАЛИН А ГОЛОВОКРУЖИТЕЛЬНЫЙ ОЩУЩЕНИЕ БЕЗГРАНИЧНЫЙ СВОБОДА НЕ ПОНИМАТЬ ПОЧЕМУ МЕНЬ УПОРНО ПРЕПОДНОСИТЬ АУДИТОРИЯ КАК ЭКСПЕРТ ПО МРАЧНЫЙ НАСТРОЕНИЕ MDASH А ЕЩИЙ MDASH ОПАСНЫЙ ВОПРОС НЕ БОЯТЬСЯ MDASH А СТОИТЬ MDASH ПУСТЬ ЭТО ОСТАТЬСЯ МОЙ ТАЙНА MDASH ЕСТЬ ОЩУЩЕНИЕ ЧТО У ВЫ СЛИШКОМ МНОГО ТАЙНА НЕТ MDASH НЕ ТАК МНОГО КАК ХОТЕТЬСЯ NBSP БЫ ПУБЛИЧНОСТЬ MDASH ПЛАТ ЗА УСПЕХ MDASH ОБЫЧНО ЗДЕСЬ УПОМИНАТЬ ОДИНОЧЕСТВО HELLIP MDASH КРАСИВЫЙ СПОСОБ ВЫВЕСТИ РАЗГОВОР НА ТЕМА О ЛИЧНЫЙ ЖИЗНЬ НО Я НЕ КУПИТЬСЯ СМЕТЬСЯ MDASH ВЫ ДО СЕЙ ПОРА ПРОДОЛЖАТЬ ИСКАТЬ СВОЕ LAQUO ИДЕАЛЬНЫЙ ЧЕЛОВЕК RAQUO MDASH ДА И НЕ НАМЕРЕННЫЙ ОСТАНАВЛИВАТЬСЯ ЕСЛИ ПОВЕЗТЬ MDASH ЗНАЧИТ Я ВСЕ-ТАКИ ЧТО-ТО ЗАСЛУЖИТЬ ВЕЛИКОЕ СЧАСТИЕ MDASH ПОЧЕМУ ВЫ ДУМАТЬ ЧТО СЧАСТИЕ НУЖНЫЙ ИМЕННО ЗАСЛУЖИТЬ MDASH ЖИЗНЕННЫЙ ОПЫТ Я NBSP БЫ НЕ ХОТЕТЬ СЕЙЧАС ВЫВОДИТЬ ДЛИННЫЙ ТЕОРИЯ И ВООБЩЕ ЭТО ДОЛГИЙ И ОБСТОЯТЕЛЬНЫЙ РАЗГОВОР СФОРМУЛИРОВАТЬ КРАТКО ЕСТЬ ОПРЕДЕЛЕННЫЙ АЛГОРИТМ ВСЕЛЕННАЯ ЕГО НЕТРУДНО ВЫЧИСЛИТЬ ЕСЛИ БЫТЬ НАБЛЮДАТЕЛЬНЫЙ ВЫ ЗДОРОВО СЭКОНОМИТЬ СВОЕ ВРЕМЯ ЕСЛИ ПОВЕРИТЬ Я НА СЛОВО NBSP КАДР ИЗ ФИЛЬМ LAQUO ПРОФЕССИОНАЛ RAQUO COPY KINOPOISK RU NBSP MDASH ВАШ БЛИЗКИЙ ОКРУЖЕНИЕ НАЗЫВАТЬ ВЫ САМЫЙ ТЕРПЕЛИВЫЙ ЧЕЛОВЕК НА ПЛАНЕТ MDASH СЕРЬЕЗНЫЙ ВЫ РАЗЫГРАТЬ Я БЫВАТЬ ОЧЕНЬ РАЗДРАЖИТЕЛЬНЫЙ ПОДВЕРЖЕННЫЙ СИЛЬНЫЙ ЭМОЦИЯ И ПОРЫВ ДА ЕЩИЙ И ЖУТКО ТРЕБОВАТЕЛЬНЫЙ MDASH ТРУДНО В ЭТО ПОВЕРИТЬ MDASH ДА-Д Я ЧАСТО НЕ ХВАТАТЬ ВЫДЕРЖКА HELLIP MDASH НО МЕНЬ ВЫ ПРОЖДАТЬ ЦЕЛЫЙ ЧАС Я НЕ ЗНАТЬ НИ ОДИН ГОЛЛИВУДСКИЙ ЗНАМЕНИТОСТЬ СПОСОБНЫЙ НА ТАКОЙ ПОДВИГ ПО НЕ ЗАВИСЕТЬ ОТ КОРРЕСПОНДЕНТ LAQUO РБК СТИЛЬ RAQUO И ГЕРОЙ ПРИЧИНА ИНТЕРВЬЮ НАЧАТЬСЯ ПОЗДНИЙ ЗАПЛАНИРОВАТЬ ВРЕМЕНИТЬ КИАНА РИВЗ ОТНЕССЯ К СИТУАЦИЯ С ПОНИМАНИЕ И ПРОДОЛЖИТЬ ЖДАТЬ НАШ ЖУРНАЛИСТ В ОТЕЛЬ MDASH ПРИМА РЕДАКЦИЯ MDASH Я ВСЕГДА ДЕРЗИТЬ СЛОВО РАЗ ДОГОВОРИТЬСЯ ОБ ИНТЕРВЬЮ MDASH ОНО ДОЛЖНЫЙ БЫЛО СОСТОЯТЬСЯ ЭТО НАВЕРНОЕ ВЫГЛЯДЕТЬ СТАРОМОДНЫЙ ИЛИ НЕДОСТАТОЧНО ПО-СВЕТСКИ НО Я КАЖЕТСЯ ЭТО ПРАВИЛЬНЫЙ MDASH А КАКОЙ У ВЫ ЕЩИЙ ЖИЗНЕННЫЙ ПРАВИТЬ MDASH НЕ ЗНАТЬ Я НЕ ЗАМЕЧАТЬ ЗА СЕБЯ ЧТО ЖИТЬ ПО КАКОЙ-ТО ПРАВИЛО ВАЖНО ДЕЛАТЬ ТО ЧТО ТЫ СЧИТАТЬ РАЗУМНЫЙ И НЕОБХОДИМЫЙ БЫТЬ ПОЛЕЗНЫЙ КТО-ТО В НУЖНЫЙ ТРУДНЫЙ МОМЕНТ ОТ КОТОРЫЙ НИКТО НЕ ЗАСТРАХОВАТЬ ИДТИ ВРАЗРЕЗ С СОБСТВЕННЫЙ УБЕЖДЕНИЕ НЕЛЬЗЯ MDASH ЭТО ТОЧНО ЕЩИЙ НУЖНЫЙ БЫТЬ САМ СЕБЯ MDASH ЭТО ПОЖАЛУЙ САМЫЙ ГЛАВНОЕ ВООБЩЕ ИНТЕРЕСНЫЙ ВОПРОС Я СРАЗУ НАЧАЛО КОПАТЬСЯ В СЕБЕ HELLIP MDASH МНОГИЙ ПОСТОЯННО ГОВОРЯТ О КРИЗИС В КИНО О ТОМ ЧТО НАЙТИ КАЧЕСТВЕННЫЙ СЦЕНАРИЙ СТАНОВИТЬСЯ ВСЕ СЛОЖНЫЙ ВЫ С ЭТО СОГЛАСНЫЙ MDASH АБСОЛЮТНО НА ПОИСК НЕ ИДЕАЛЬНЫЙ НО ХОРОШИЙ СЦЕНАРИЙ УХОДИТЬ НЕ МАЛЕНЬКИЙ ПЯТИ-ШЕСТЬ МЕСЯЦ Я ОЧЕНЬ КРОПОТЛИВЫЙ ИЗУЧАТЬ ТЕКСТ И СТАРАТЬСЯ РАЗГЛЯДЕТЬ ЕСЛИ НЕ БЕЗУПРЕЧНЫЙ РАБОТА ТО ХОТЯ NBSP БЫ ПЕРСПЕКТИВНЫЙ ОСНОВА КОТОРЫЙ МОЖНО РЕДАКТИРОВАТЬ И НАПОЛНЯТЬ СЕЙЧАС РЕДКО ПОПАДАТЬСЯ ОБРАЗЦОВЫЙ ТАЛАНТЛИВЫЙ КИНО В ОСНОВНЫЙ ПЛАНКА MDASH ЭТО ПРОСТО НЕПЛОХОЙ ДРАМАТУРГИЯ УВЕРИТЬ ЧТО ЗРИТЕЛЬ ЧУВСТВОВАТЬ РАЗНИЦА MDASH КАКОЙ ФИЛЬМ С ВАШ УЧАСТИЕ ВЫ СЧИТАТЬ ГЛАВНЫЙ ДОСТИЖЕНИЕ В СВОЙ КАРЬЕР MDASH Я NBSP БЫ НАЗВАТЬ ФИЛЬМ ЗА КОТОРЫЙ Я ТОЧНО НИКОГДА НЕ БЫТЬ СТЫДНЫЙ LAQUO АДВОКАТ ДЬЯВОЛ RAQUO LAQUO НА ГРЕБЕНЬ ВОЛНА RAQUO LAQUO МАТРИЦА RAQUO LAQUO ПОМУТНЕНИЕ RAQUO ДЛЯ МЕНЬ ЕЩИЙ ОЧЕНЬ ВАЖНЫЙ ЯЗЫК КАРТИНА ДИАЛОГ МОЖЕТ БЫТЬ ЭТО ИДЕТ СО ВРЕМЕННЫЙ МОЙ ТЕАТРАЛЬНЫЙ ДЕЯТЕЛЬНОСТЬ НЕ ЗНАТЬ ЯЗЫК ВО МНОГИЙ ОПРЕДЕЛЯТЬ ИСТОРИЯ ОН ИМЕТЬ НЕ МЕНЬШИЙ ЗНАЧЕНИЕ ЧЕМ САМ СЮЖЕТ И РЕЖИССЕРСКИЙ СТИЛЬ MDASH А ЕЩИЙ LAQUO КОНСТАНТИН RAQUO И LAQUO ДРАКУЛА RAQUO КОППОЛ КСТАТИ КАК ВЫ СЧИТАТЬ MDASH В ВАШ РЕАЛЬНЫЙ ЖИЗНЬ ПРИСУТСТВОВАТЬ МИСТИК MDASH У МЕНЬ ЕСТЬ ОДИН СВЕРХСПОСОБНОСТЬ Я ОКАЗЫВАТЬСЯ В НУЖНЫЙ ВРЕМЯ В НУЖНЫЙ МЕСТО ЭТО ВЕДЬ МОЖНО НАЗВАТЬ ИНТУИЦИЯ ИЛИ ШЕСТАЯ ЧУВСТВО MDASH СКОРЫЙ ЧУТЬЕ MDASH НУ ИЛИ ТАК ДА НО В МИСТИК Я ВЕРИТЬ ЭТО ПРАВДА MDASH ВЫ СЛЕДИТЬ ЗА СВОЕ ГАРДЕРОБ ЗА СОВРЕМЕННЫЙ МОДА MDASH ДА Я ЭТО ИНТЕРЕСНО Я ОЧЕНЬ ТЩАТЕЛЬНО ПОДБИРАТЬ СЕБЕ КОСТЮМ ЛЮБИТЬ ЧЕРНЫЙ И СЕРЫЙ ЦВЕТА ПРЕДПОЧИТАТЬ ОБУВЬ В КЛАССИЧЕСКИЙ СТИЛЬ MDASH БЕЗ ЧЕГО ВЫ НЕ МЫСЛИТЬ СВОЙ ДЕТЬ MDASH БЕЗ ЯИЧНИЦА С БЕКОН ЧАШКА КОФЕ И КНИГА ХОТЯ НЕТ БЕЗ КОФЕ МОЧЬ ОБОЙТИСЬ А БЕЗ КНИГА ТОЧНО НЕТ NBSP NBSP NBSP', 0, 0),

(3, 5, 'От бритвы до автомобиля: почему мир переходит к сервисам по подписке', '1552335850.png', '', 'В 2018-м люди все чаще предпочитают не покупать какие-то вещи, а получать по подписке. Раз в месяц с карточки списывается небольшая сумма и курьер приносит домой посылку с тем, что мы вряд ли стали бы покупать специально. Почему так происходит?', '<div class="l-col-center__inner">\r\n<div class="article__overview ">\r\n<div class="article__rubric">&nbsp;</div>\r\n\r\n<div class="article__main-image">\r\n<div class="article__main-image__inner"><img alt="" class="js-rbcslider-image" itemprop="image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323404663357.png" /></div>\r\n</div>\r\n\r\n<div class="article__social js-social-likes">\r\n<div class="social-likes social-likes_notext social-likes_visible" data-counters="no" data-title="От бритвы до автомобиля: почему мир переходит к сервисам по подписке">\r\n<div class="social-likes__widget social-likes__widget_facebook" title="Поделиться ссылкой на Фейсбуке"><span class="social-likes__button social-likes__button_facebook"><span class="social-likes__icon social-likes__icon_facebook"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_twitter" data-via="ru_rbc" title="Поделиться ссылкой в Твиттере"><span class="social-likes__button social-likes__button_twitter"><span class="social-likes__icon social-likes__icon_twitter"></span></span></div>\r\n\r\n<div class="social-likes__widget social-likes__widget_vkontakte" title="Поделиться ссылкой во Вконтакте"><span class="social-likes__button social-likes__button_vkontakte"><span class="social-likes__icon social-likes__icon_vkontakte"></span></span></div>\r\n</div>\r\n</div>\r\n\r\n<div class="article__info">\r\n<div class="article__author">&nbsp;</div>\r\n\r\n<div class="article__author">Автор <span class="article__author__name"> <!--\r\n                    --><!--\r\n                        -->Сергей Король<!--\r\n                        --><!--\r\n                    --><!--\r\n                --> </span></div>\r\n\r\n<div class="article__date" content="2018-07-23T13:52:37+03:00" itemprop="datePublished">23 июля 2018</div>\r\n<meta itemprop="dateModified" content="2018-07-23T13:52:37+03:00"></div>\r\n\r\n<div class="article__subtitle">&nbsp;</div>\r\n\r\n<div class="article__subtitle">В 2018-м люди все чаще предпочитают не покупать какие-то вещи, а получать по подписке. Раз в месяц с карточки списывается небольшая сумма и курьер приносит домой посылку с тем, что мы вряд ли стали бы покупать специально. Почему так происходит?</div>\r\n</div>\r\n\r\n<div class="article__text" itemprop="articleBody">\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Хочу новый образ</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">В ближайшем будущем можно будет купить не только джинсы и мотоцикл, но и образ жизни беспечного байкера &mdash; целиком. Только представьте, вы платите, условно, миллион рублей, чтобы получить желаемый образ целиком. В вашей квартире делают ремонт и обставляют ее: кровать два на два для вечеринок, в гардеробе &mdash; кожаные куртки, черные джинсы и ботинки Red Wings. Возле дома стоит мотоцикл, в кошельке &mdash; абонемент в подпольный бар. На столе &mdash; свеча в виде черепа и дискография Nine Inch Nails. Все это образуется в жизни сразу, не нужно долго искать свой образ и копить вещи.</span></p>\r\n\r\n<p dir="ltr"><span> </span></p>\r\n\r\n<div class="row">\r\n<div class="col-md-4" style="margin-bottom: 1rem;"><img src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323371587729.jpg" /></div>\r\n\r\n<div class="col-md-4" style="margin-bottom: 1rem;"><img src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323371588843.jpg" /></div>\r\n\r\n<div class="col-md-4" style="margin-bottom: 1rem;"><img src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323371824677.jpg" /></div>\r\n</div>\r\n\r\n<div class="gallery_infinity__copyright">&copy; lot2046.com</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Похожим образом действует LOT 2046 от российского стартапера Вадика Мармеладова. Он успешно продал предыдущий проект </span><span>&mdash;&nbsp;Lapka &mdash;&nbsp;компании Airbnb и переехал в Шэньчжэнь, чтобы продавать людям жизнь по подписке. Члены клуба LOT 2046 платят по $ 100 в месяц и ежемесячно получают посылки. В них одежда, обувь, аксессуары, и даже мыло &mdash; все создано в минималистичном стиле по размеру заказчика. Недавно LOT 2046 вышел за рамки пакета с вещами &mdash; сервис предлагает подписчикам переночевать друг у друга, расположившись на специально созданном матрасе и угостившись при этом специально испеченными лепешками.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Не знаю что выбрать...</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Сервисы по подписке Coffee Box и Coffeevine доставляет по подписке кофе. С хорошим кофе возникают проблемы у многих людей: непонятно что выбрать при огромном разнообразии и сложности вкуса. Компания заявляет: мы отобрали лучшее для разных поводов, да еще и самое доступное.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Эти проекты работают в особенной&nbsp;нише&nbsp;&mdash; своеобразное&nbsp;кураторство в мире, где трудно разобраться самому. Что выбрать? Как выбрать? Как найти интересное из мира косметики? Бьюти-сервис&nbsp;Julep предлагает не выбирать, а пользоваться тем, что уже выбрали эксперты. Есть даже сервисы по доставке колбасы &mdash; Carnivore раз в месяц высылает нарезки со всего мира.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323376190015.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; thecoffeevine.com</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Не хочу об этом думать</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Расцвет современных сервисов по подписке пошел от Dollar Shave Club &mdash; небольшого стартапа, который закрывал единственную, но важную задачу: помогал мужчинам всегда бриться острыми бритвами. Ведь лезвия, как правило,&nbsp;тупятся не вовремя (например, перед свиданием или важной встречей), а купить их в магазине забываешь. Доставка бритвенных принадлежностей закрывала эту проблему насовсем. Рынок поверил в такую модель, а основатели Dollar Shave Club Марк Левин и Майкл Дубин продали компанию за </span><span>$&nbsp;1 млрд кэшем.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323385299051.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; facebook.com/DollarShaveClub</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Позже подтянулись другие подписочные сервисы, удовлетворяющие базовые потребности: одни доставляют трусы и носки, другие &mdash; презервативы. Care/Of позволяет не думать о витаминах.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Популярная и активно развивающаяся подписочная сфера в России &mdash; доставка комплектов готовой еды. Growfood, Elementaree и другие сервисы отучают людей ходить в супермаркет. Зачем покупать полкило семги и хранить ее в холодильнике неделю? Куда приятнее собрать ужин, словно из конструктора, кубики которого регулярно доставляют на дом&nbsp;свежими.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Удивите меня</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">И все же многие подписываются не из практических соображений, а просто потому что скучают по сюрпризам.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Подписка &mdash; это гарантированный подарок каждый месяц. Люди&nbsp;жадно ждут свои коробки и с нетерпением открывают их: что там на этот раз? Часто производители подыгрывают этому чувству, оформляя подписочные коробки словно сюрпризы. Некоторые даже предлагают доплатить за особую упаковку.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Vinyl Me, Please раз в месяц присылает виниловую пластинку &mdash; узнаешь какую, когда откроешь. Сервис дополнительно давит на любовь человека к коллекционированию: мол, твоя аудиотека растет сама, только успевай слушать.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Creation Crate шлет наборы для создания электронных устройств, Stickii Club &mdash; наклейки, Accio присылает совершенно случайную ерунду: от кружек до шоколадок.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323389759162.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; vinylmeplease.com</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<p>&nbsp;</p>\r\n&nbsp;\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Дорогие удовольствия</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Совершенно отдельная и пока небольшая категория подписных сервисов заменяет пользование дорогими вещами. В ней бурно развиваются сервисы аренды машин по подписке. Предложения есть у большинства брендов. Стоимость аренды бюджетного автомобиля начинается от </span><span>$&nbsp;500 в месяц.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Можно не&nbsp;покупать одну или две машины, а ездить на разных. Летом &mdash; на&nbsp;кабриолете, зимой &mdash; на внедорожнике.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="article__picture_big">\r\n<div class="article__picture_big__img-wrap"><img class="article__picture_big__image" src="//corporate.ready.in-ri.ru/images_ckeditor/images/article/755323391464705.jpg" /></div>\r\n\r\n<div class="article__picture_big__info">\r\n<div class="article__picture_big__source">&copy; bmw.ru</div>\r\n</div>\r\n</div>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p dir="ltr">&nbsp;</p>\r\n\r\n<p dir="ltr"><span>Вполне возможно, что в&nbsp;будущем пользование дорогими и условно дорогими&nbsp;вещами&nbsp;станет преимущественно&nbsp;подписочным. Сегодня мы платим за квартиру, а завтра&nbsp;начнем платить за ее наполнение мебелью и аксессуарами.</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<h3 dir="ltr"><span style="font-size:28px;"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Главное,&nbsp;вовремя остановиться</span></span></h3>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Подписочные сервисы только на первый взгляд кажутся экономным способом получать новые вещи. На самом деле в них кроется опасность.</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Платить </span><span>$ 10&ndash;20 за один сервис &mdash; приятно и не особо обременительно. Хуже, когда становится пять-шесть, а то и десять сервисов: на каждый подписываешься, забывая о предыдущих. Да подумаешь, мелочи &mdash; в итоге в какой-то момент в начале каждого месяца они разом ополовинивают только что полученную зарплату.&nbsp;</span></p>\r\n\r\n<p dir="ltr"><span id="docs-internal-guid-59e65c1e-b886-1ccb-b29f-5bdf20e541da">Еще немного &mdash; и придется заводить отдельный сервис, в котором можно управлять подписками на другие сервисы. Главное, чтобы он не распространялся </span><span>по подписке.</span>&nbsp;<span class="article__logo article__logo_no-left-margin"></span></p>\r\n\r\n<p dir="ltr">&nbsp;</p>\r\n\r\n<p dir="ltr">&nbsp;</p>\r\n</div>\r\n</div>\r\n', '', '', '', '', '', '', 'ОТ БРИТВА ДО АВТОМОБИЛЬ ПОЧЕМУ МИР ПЕРЕХОДИТЬ К СЕРВИС ПО ПОДПИСКА', 'NBSP NBSP АВТОР СЕРГЕЙ КОРОЛЬ 23 ИЮЛЬ 2018 NBSP В 2018-М ЧЕЛОВЕК ВСЕ ЧАЩА ПРЕДПОЧИТАТЬ НЕ ПОКУПАТЬ КАКОЙ-ТО ВЕЩИЙ А ПОЛУЧАТЬ ПО ПОДПИСКА РАЗ В МЕСЯЦ С КАРТОЧКА СПИСЫВАТЬСЯ НЕБОЛЬШОЙ СУММА И КУРЬЕР ПРИНОСИТЬ ДОМОЙ ПОСЫЛКА С ТЕМ ЧТО МЫ ВРЯД ЛИ СТАЛЬ БЫ ПОКУПАТЬ СПЕЦИАЛЬНО ПОЧЕМУ ТАК ПРОИСХОДИТЬ ХОТЕТЬ НОВЫЙ ОБРАЗ В БЛИЖНИЙ БУДУЩИЙ МОЖНО БЫТЬ КУПИТЬ НЕ ТОЛЬКО ДЖИНСА И МОТОЦИКЛ НО И ОБРАЗ ЖИЗНЬ БЕСПЕЧНЫЙ БАЙКЕР MDASH ЦЕЛИКОМ ТОЛЬКО ПРЕДСТАВИТЬ ВЫ ПЛАТИТЬ УСЛОВНО МИЛЛИОН РУБЛЬ ЧТОБЫ ПОЛУЧИТЬ ЖЕЛАЕМЫЙ ОБРАЗ ЦЕЛИКОМ В ВАШ КВАРТИРА ДЕЛАТЬ РЕМОНТ И ОБСТАВЛЯТЬ ЕЕ КРОВАТЬ ДВА НА ДВА ДЛЯ ВЕЧЕРИНКА В ГАРДЕРОБ MDASH КОЖАНЫЙ КУРТКА ЧЕРНЫЙ ДЖИНСА И БОТИНОК RED WINGS ВОЗЛЕ ДОМА СТОИТЬ МОТОЦИКЛ В КОШЕЛЁК MDASH АБОНЕМЕНТ В ПОДПОЛЬНЫЙ БАР НА СТОЛ MDASH СВЕЧА В ВИД ЧЕРЕП И ДИСКОГРАФИЯ NINE INCH NAILS ВСЕ ЭТО ОБРАЗОВАТЬСЯ В ЖИЗНЬ СРАЗУ НЕ НУЖНЫЙ ДОЛГО ИСКАТЬ СВОЙ ОБРАЗ И КОПИТЬ ВЕЩИЙ COPY LOT2046 COM NBSP ПОХОЖИЙ ОБРАЗ ДЕЙСТВОВАТЬ LOT 2046 ОТ РОССИЙСКИЙ СТАРТАПЕР ВАДИК МАРМЕЛАДОВЫЙ ОН УСПЕШНО ПРОДАТЬ ПРЕДЫДУЩИЙ ПРОЕКТ MDASH NBSP LAPKA MDASH NBSP КОМПАНИЯ AIRBNB И ПЕРЕЕХАТЬ В ШЭНЬЧЖЭНИЙ ЧТОБЫ ПРОДАВАТЬ ЧЕЛОВЕК ЖИЗНЬ ПО ПОДПИСКА ЧЛЕН КЛУБ LOT 2046 ПЛАТИТЬ ПО 100 В МЕСЯЦ И ЕЖЕМЕСЯЧНО ПОЛУЧАТЬ ПОСЫЛКА В ОНИ ОДЕЖДА ОБУВЬ АКСЕССУАР И ДАЖЕ МЫЛО MDASH ВСЕ СОЗДАТЬ В МИНИМАЛИСТИЧНЫЙ СТИЛЬ ПО РАЗМЕР ЗАКАЗЧИК НЕДАВНО LOT 2046 ВЫЙТИ ЗА РАМКА ПАКЕТ С ВЕЩЬ MDASH СЕРВИС ПРЕДЛАГАТЬ ПОДПИСЧИК ПЕРЕНОЧЕВАТЬ ДРУГ У ДРУГ РАСПОЛОЖИТЬСЯ НА СПЕЦИАЛЬНО СОЗДАТЬ МАТРАС И УГОСТИТЬСЯ ПРИ ЭТО СПЕЦИАЛЬНО ИСПЕЧЕННЫЙ ЛЕПЕШКА NBSP NBSP NBSP NBSP НЕ ЗНАТЬ ЧТО ВЫБРАТЬ СЕРВИС ПО ПОДПИСКА COFFEE BOX И COFFEEVINE ДОСТАВЛЯТЬ ПО ПОДПИСКА КОФЕ С ХОРОШИЙ КОФЕ ВОЗНИКАТЬ ПРОБЛЕМА У МНОГИЙ ЧЕЛОВЕК НЕПОНЯТНО ЧТО ВЫБРАТЬ ПРИ ОГРОМНЫЙ РАЗНООБРАЗИЕ И СЛОЖНОСТЬ ВКУС КОМПАНИЯ ЗАЯВЛЯТЬ МЫ ОТОБРАТЬ ХОРОШИЙ ДЛЯ РАЗНЫЙ ПОВОД ДА ЕЩИЙ И САМЫЙ ДОСТУПНЫЙ ЭТОТ ПРОЕКТ РАБОТАТЬ В ОСОБЕННЫЙ NBSP НИША NBSP MDASH СВОЕОБРАЗНЫЙ NBSP КУРАТОРСТВО В МИР ГДЕ ТРУДНО РАЗОБРАТЬСЯ САМЫЙ ЧТО ВЫБРАТЬ КАК ВЫБРАТЬ КАК НАЙТИ ИНТЕРЕСНЫЙ ИЗ МИР КОСМЕТИКА БЬЮТИ-СЕРВИС NBSP JULEP ПРЕДЛАГАТЬ НЕ ВЫБИРАТЬ А ПОЛЬЗОВАТЬСЯ ТЕМ ЧТО УЖЕ ВЫБРАТЬ ЭКСПЕРТ ЕСТЬ ДАЖЕ СЕРВИС ПО ДОСТАВКА КОЛБАСА MDASH CARNIVORE РАЗ В МЕСЯЦ ВЫСЫЛАТЬ НАРЕЗКА СО ВСЕГО МИР NBSP COPY THECOFFEEVINE COM NBSP NBSP NBSP NBSP NBSP НЕ ХОТЕТЬ ОБ ЭТО ДУМАТЬ РАСЦВЕТ СОВРЕМЕННЫЙ СЕРВИС ПО ПОДПИСКА ПОШЕЛ ОТ DOLLAR SHAVE CLUB MDASH НЕБОЛЬШОЙ СТАРТАП КОТОРЫЙ ЗАКРЫВАТЬ ЕДИНСТВЕННЫЙ НО ВАЖНЫЙ ЗАДАЧА ПОМОГАТЬ МУЖЧИНА ВСЕГДА БРИТЬСЯ ОСТРЫЙ БРИТВА ВЕДЬ ЛЕЗВИЕ КАК ПРАВИТЬ NBSP ТУПИТЬСЯ НЕ ВОВРЕМЯ НАПРИМЕР ПЕРЕД СВИДАНИЕ ИЛИ ВАЖНЫЙ ВСТРЕЧА А КУПИТЬ ИХ В МАГАЗИН ЗАБЫВАТЬ ДОСТАВКА БРИТВЕННЫЙ ПРИНАДЛЕЖНОСТЬ ЗАКРЫВАТЬ ЭТОТ ПРОБЛЕМА НАСОВСЕМ РЫНОК ПОВЕРИТЬ В ТАКОЙ МОДЕЛЬ А ОСНОВАТЕЛЬ DOLLAR SHAVE CLUB МАРК ЛЕВИН И МАЙКЛ ДУБИНА ПРОДАТЬ КОМПАНИЯ ЗА NBSP 1 МИЛЛИАРД КЭШ NBSP COPY FACEBOOK COM DOLLARSHAVECLUB NBSP ПОЗДНИЙ ПОДТЯНУТЬСЯ ДРУГОЙ ПОДПИСОЧНЫЙ СЕРВИС УДОВЛЕТВОРЯТЬ БАЗОВЫЙ ПОТРЕБНОСТЬ ОДИН ДОСТАВЛЯТЬ ТРУС И НОСКА ДРУГОЙ MDASH ПРЕЗЕРВАТИВ CARE OF ПОЗВОЛЯТЬ НЕ ДУМАТЬ О ВИТАМИН ПОПУЛЯРНЫЙ И АКТИВНО РАЗВИВАЮЩИЙСЯ ПОДПИСОЧНЫЙ СФЕРА В РОССИЯ MDASH ДОСТАВКА КОМПЛЕКТ ГОТОВЫЙ ЕДА GROWFOOD ELEMENTAREE И ДРУГОЙ СЕРВИС ОТУЧАТЬ ЧЕЛОВЕК ХОДИТЬ В СУПЕРМАРКЕТ ЗАЧЕМ ПОКУПАТЬ ПОЛКИЛО СЕМГ И ХРАНИТЬ ЕЕ В ХОЛОДИЛЬНИК НЕДЕЛЯ КУДА ПРИЯТНЫЙ СОБРАТЬ УЖИНЫЙ СЛОВНО ИЗ КОНСТРУКТОР КУБИК КОТОРЫЙ РЕГУЛЯРНО ДОСТАВЛЯТЬ НА ДОМ NBSP СВЕЖИЙ NBSP NBSP NBSP NBSP УДИВИТЬ МЕНЬ И ВСЕ ЖЕ МНОГИЙ ПОДПИСЫВАТЬСЯ НЕ ИЗ ПРАКТИЧЕСКИЙ СООБРАЖЕНИЕ А ПРОСТО ПОТОМУ ЧТО СКУЧАТЬ ПО СЮРПРИЗ ПОДПИСКА MDASH ЭТО ГАРАНТИРОВАТЬ ПОДАРОК КАЖДЫЙ МЕСЯЦ ЧЕЛОВЕК NBSP ЖАДНО ЖДАТЬ СВОЕ КОРОБКА И С НЕТЕРПЕНИЕ ОТКРЫВАТЬ ИХ ЧТО ТАМ НА ЭТОТ РАЗ ЧАСТО ПРОИЗВОДИТЕЛЬ ПОДЫГРЫВАТЬ ЭТО ЧУВСТВО ОФОРМЛЯТЬ ПОДПИСОЧНЫЙ КОРОБКА СЛОВНО СЮРПРИЗ НЕКОТОРЫЙ ДАЖЕ ПРЕДЛАГАТЬ ДОПЛАТИТЬ ЗА ОСОБЫЙ УПАКОВКА VINYL ME PLEASE РАЗ В МЕСЯЦ ПРИСЫЛАТЬ ВИНИЛОВЫЙ ПЛАСТИНКА MDASH УЗНАТЬ КАКОЙ КОГДА ОТКРЫТЬ СЕРВИС ДОПОЛНИТЕЛЬНО ДАВИТЬ НА ЛЮБОВЬ ЧЕЛОВЕК К КОЛЛЕКЦИОНИРОВАНИЕ МОЛ ТВОЙ АУДИОТЕКА РАСТИ САМ ТОЛЬКО УСПЕВАТЬ СЛУШАТЬ CREATION CRATE ШЛЕТ НАБОР ДЛЯ СОЗДАНИЕ ЭЛЕКТРОННЫЙ УСТРОЙСТВО STICKII CLUB MDASH НАКЛЕЙКА ACCIO ПРИСЫЛАТЬ СОВЕРШЕННО СЛУЧАЙНЫЙ ЕРУНДА ОТ КРУЖКА ДО ШОКОЛАДКА NBSP COPY VINYLMEPLEASE COM NBSP NBSP NBSP NBSP NBSP ДОРОГОЙ УДОВОЛЬСТВИЕ СОВЕРШЕННО ОТДЕЛЬНЫЙ И ПОКА НЕБОЛЬШОЙ КАТЕГОРИЯ ПОДПИСНОЙ СЕРВИС ЗАМЕНЯТЬ ПОЛЬЗОВАНИЕ ДОРОГОЙ ВЕЩЬ В ОНА БУРНО РАЗВИВАТЬСЯ СЕРВИС АРЕНДА МАШИН ПО ПОДПИСКА ПРЕДЛОЖЕНИЕ ЕСТЬ У БОЛЬШИНСТВО БРЕНД СТОИМОСТЬ АРЕНДА БЮДЖЕТНЫЙ АВТОМОБИЛЬ НАЧИНАТЬСЯ ОТ NBSP 500 В МЕСЯЦ МОЖНО НЕ NBSP ПОКУПАТЬ ОДИН ИЛИ ДВА МАШИН А ЕЗДИТЬ НА РАЗНЫЙ ЛЕТОМ MDASH НА NBSP КАБРИОЛЕТ ЗИМОЙ MDASH НА ВНЕДОРОЖНИК NBSP COPY BMW RU NBSP NBSP ВПОЛНЕ ВОЗМОЖНО ЧТО В NBSP БУДУЩИЙ ПОЛЬЗОВАНИЕ ДОРОГОЙ И УСЛОВНО ДОРОГОЙ NBSP ВЕЩЬ NBSP СТАТЬ ПРЕИМУЩЕСТВЕННО NBSP ПОДПИСОЧНЫЙ СЕГОДНЯ МЫ ПЛАТИТЬ ЗА КВАРТИРА А ЗАВТРА NBSP НАЧЕНЬ ПЛАТИТЬ ЗА ЕЕ НАПОЛНЕНИЕ МЕБЕЛЬ И АКСЕССУАР NBSP ГЛАВНОЕ NBSP ВОВРЕМЯ ОСТАНОВИТЬСЯ ПОДПИСОЧНЫЙ СЕРВИС ТОЛЬКО НА ПЕРВЫЙ ВЗГЛЯД КАЗАТЬСЯ ЭКОНОМНЫЙ СПОСОБ ПОЛУЧАТЬ НОВЫЙ ВЕЩИЙ НА САМЫЙ ДЕЛО В ОНИ КРЫТЬСЯ ОПАСНОСТЬ ПЛАТИТЬ 10 NDASH 20 ЗА ОДИН СЕРВИС MDASH ПРИЯТНО И НЕ ОСОБО ОБРЕМЕНИТЕЛЬНЫЙ ПЛОХО КОГДА СТАНОВИТЬСЯ ПЯТЬ-ШЕСТЬ А ТО И ДЕСЯТЬ СЕРВИС НА КАЖДЫЙ ПОДПИСЫВАТЬСЯ ЗАБЫВАТЬ О ПРЕДЫДУЩИЙ ДА ПОДУМАТЬ МЕЛОЧЬ MDASH В ИТОГ В КАКОЙ-ТО МОМЕНТ В НАЧАЛО КАЖДЫЙ МЕСЯЦ ОНИ РАЗОМ ОПОЛОВИНИВАТЬ ТОЛЬКО ЧТО ПОЛУЧИТЬ ЗАРПЛАТА NBSP ЕЩИЙ НЕМНОГО MDASH И ПРИСТИСЬ ЗАВОДИТЬ ОТДЕЛЬНЫЙ СЕРВИС В КОТОР МОЖНО УПРАВЛЯТЬ ПОДПИСКА НА ДРУГОЙ СЕРВИС ГЛАВНОЕ ЧТОБЫ ОН НЕ РАСПРОСТРАНЯТЬСЯ ПО ПОДПИСКА NBSP NBSP NBSP', 0, 0),

(4, 0, 'Политика организации по обработке персональных данных', '', '', '', '<strong>1. Общие положения</strong>\r\n<p>&nbsp;</p>\r\n\r\n<p>1.1. Настоящая Политика в отношении обработки персональных данных</p>\r\n\r\n<p>(далее &ndash; Политика) составлена в соответствии с пунктом 2 статьи 18.1 Федерального закона &laquo;О персональных данных&raquo; № 152-ФЗ от 27 июля 2006 г., а также иными нормативными правовыми актами Российской Федерации в области защиты и обработки персональных данных и действует в отношении всех персональных данных (далее &ndash; данные), которые Организация (далее &ndash; Оператор, Общество) может получить от субъекта персональных данных, являющегося стороной по гражданско-правовому договору, от пользователя сети Интернет (далее &ndash; Пользователь) во время использования им любого из сайтов, сервисов, служб, программ, продуктов или услуг ООО &laquo;&raquo;, а также от субъекта персональных данных, состоящего с Оператором в отношениях, регулируемых трудовым законодательством (далее &ndash; Работник).</p>\r\n\r\n<p>1.2. Оператор обеспечивает защиту обрабатываемых персональных данных от несанкционированного доступа и разглашения, неправомерного использования или утраты в соответствии с требованиями Федерального закона от 27 июля 2006 г. № 152-ФЗ &laquo;О персональных данных&raquo;.</p>\r\n\r\n<p>1.3. Оператор вправе вносить изменения в настоящую Политику. При внесении изменений в заголовке Политики указывается дата последнего обновления редакции. Новая редакция Политики вступает в силу с момента ее размещения на сайте, если иное не предусмотрено новой редакцией Политики.</p>\r\n\r\n<p><strong>2. Термины и принятые сокращения</strong></p>\r\n\r\n<p>Персональные данные &ndash; любая информация, относящаяся к прямо или косвенно определенному или определяемому физическому лицу (субъекту персональных данных).</p>\r\n\r\n<p>Обработка персональных данных &ndash; любое действие (операция) или совокупность действий (операций), совершаемых с использованием средств автоматизации или без использования таких средств с персональными данными, включая сбор, запись, систематизацию, накопление, хранение, уточнение (обновление, изменение), извлечение, использование, передачу (распространение, предоставление, доступ), обезличивание, блокирование, удаление, уничтожение персональных данных. Автоматизированная обработка персональных данных &ndash; обработка персональных данных с помощью средств вычислительной техники. Информационная система персональных данных (ИСПД) &ndash; совокупность содержащихся в базах данных персональных данных и обеспечивающих их обработку информационных технологий и технических средств.</p>\r\n\r\n<p>Персональные данные, сделанные общедоступными субъектом персональных данных, &ndash; персональные данные, доступ неограниченного круга лиц к которым предоставлен субъектом персональных данных либо по его просьбе. Блокирование персональных данных &ndash; временное прекращение обработки персональных данных (за исключением случаев, если обработка необходима для уточнения персональных данных).</p>\r\n\r\n<p>Уничтожение персональных данных &ndash; действия, в результате которых становится невозможным восстановить содержание персональных данных в информационной системе персональных данных и (или) в результате которых уничтожаются материальные носители персональных данных.</p>\r\n\r\n<p>Оператор &ndash; организация, самостоятельно или совместно с другими лицами организующая обработку персональных данных, а также определяющая цели обработки персональных данных, подлежащих обработке, действия (операции), совершаемые с персональными данными. Оператором является ООО &laquo;&raquo;, расположенное по адресу:</p>\r\n\r\n<p><strong>3. Обработка персональных данных</strong></p>\r\n\r\n<p>3.1. Получение персональных данных.</p>\r\n\r\n<p>3.1.1. Все персональные данные следует получать от самого субъекта. Если персональные данные субъекта можно получить только у третьей стороны, то субъект должен быть уведомлен об этом или от него должно быть получено согласие.</p>\r\n\r\n<p>3.1.2. Оператор должен сообщить субъекту о целях, предполагаемых источниках и способах получения персональных данных, характере подлежащих получению персональных данных, перечне действий с персональными данными, сроке, в течение которого действует согласие, и порядке его отзыва, а также о последствиях отказа субъекта дать письменное согласие на их получение.</p>\r\n\r\n<p>3.1.3. Документы, содержащие персональные данные, создаются путем:</p>\r\n\r\n<p>&ndash; копирования оригиналов документов (паспорт, документ об образовании, свидетельство ИНН, пенсионное свидетельство и др.);</p>\r\n\r\n<p>&ndash; внесения сведений в учетные формы;</p>\r\n\r\n<p>&ndash; получения оригиналов необходимых документов (трудовая книжка, медицинское заключение, характеристика и др.).</p>\r\n\r\n<p>3.2. Обработка персональных данных.</p>\r\n\r\n<p>3.2.1. Обработка персональных данных осуществляется:</p>\r\n\r\n<p>&ndash; с согласия субъекта персональных данных на обработку его персональных данных;</p>\r\n\r\n<p>&ndash; в случаях, когда обработка персональных данных необходима для осуществления и выполнения возложенных законодательством РФ функций, полномочий и обязанностей;</p>\r\n\r\n<p>&ndash; в случаях, когда осуществляется обработка персональных данных, доступ неограниченного круга лиц к которым предоставлен субъектом персональных данных либо по его просьбе (далее &ndash; персональные данные, сделанные общедоступными субъектом персональных данных).</p>\r\n\r\n<p>3.2.2. Цели обработки персональных данных:</p>\r\n\r\n<p>&ndash; осуществление трудовых отношений;</p>\r\n\r\n<p>&ndash; осуществление гражданско-правовых отношений;</p>\r\n\r\n<p>&ndash; для идентификации пользователей (посетителей) сайта, для связи с пользователем, в том числе направление уведомлений, запросов и информации, касающихся использования услуг компании, исполнения соглашений и договоров, а также обработки запросов и заявок от пользователя.</p>\r\n\r\n<p>- обезличивания персональных данных для получения обезличенных статистических данных, которые передаются третьему лицу для проведения исследований, выполнения работ или оказания услуг по поручению организации.</p>\r\n\r\n<p>3.2.3. Категории субъектов персональных данных.</p>\r\n\r\n<p>Обрабатываются персональные данные следующих субъектов персональных</p>\r\n\r\n<p>данных:</p>\r\n\r\n<p>&ndash; физические лица, состоящие с Обществом в трудовых отношениях;</p>\r\n\r\n<p>&ndash; физические лица, уволившиеся из Общества;</p>\r\n\r\n<p>&ndash; физические лица, являющиеся кандидатами на работу;</p>\r\n\r\n<p>&ndash; физические лица, состоящие с Обществом в гражданско-правовых</p>\r\n\r\n<p>отношениях;</p>\r\n\r\n<p>&ndash; физические лица, являющиеся пользователями сайта организации.</p>\r\n\r\n<p>3.2.4. Персональные данные, обрабатываемые Оператором:</p>\r\n\r\n<p>&ndash; данные, полученные при осуществлении трудовых отношений;</p>\r\n\r\n<p>&ndash; данные, полученные для осуществления отбора кандидатов на работу;</p>\r\n\r\n<p>&ndash; данные, полученные при осуществлении гражданско-правовых отношений;</p>\r\n\r\n<p>&ndash; данные, полученные от пользователей сайта организации.</p>\r\n\r\n<p>3.2.5. Обработка персональных данных ведется:</p>\r\n\r\n<p>&ndash; с использованием средств автоматизации;</p>\r\n\r\n<p>&ndash; без использования средств автоматизации.</p>\r\n\r\n<p>3.3. Хранение персональных данных.</p>\r\n\r\n<p>3.3.1. Персональные данные субъектов могут быть получены, проходить дальнейшую обработку и передаваться на хранение как на бумажных носителях, так и в электронном виде.</p>\r\n\r\n<p>3.3.2. Персональные данные, зафиксированные на бумажных носителях, хранятся в запираемых шкафах либо в запираемых помещениях с ограниченным правом доступа.</p>\r\n\r\n<p>3.3.3. Персональные данные субъектов, обрабатываемые с использованием средств автоматизации в разных целях, хранятся в разных папках.</p>\r\n\r\n<p>3.3.4. Не допускается хранение и размещение документов, содержащих персональных данных, в открытых электронных каталогах (файлообменниках) в ИСПД.</p>\r\n\r\n<p>3.3.5. Хранение персональных данных в форме, позволяющей определить субъекта персональных данных, осуществляется не дольше, чем этого требуют цели их обработки, и они подлежат уничтожению по достижении целей обработки или в случае утраты необходимости в их достижении.</p>\r\n\r\n<p>3.4. Уничтожение персональных данных.</p>\r\n\r\n<p>3.4.1. Уничтожение документов (носителей), содержащих персональных данных, производится путем сожжения, дробления (измельчения), химического разложения, превращения в бесформенную массу или порошок. Для уничтожения бумажных документов допускается применение шредера.</p>\r\n\r\n<p>3.4.2. Персональные данные на электронных носителях уничтожаются путем стирания или форматирования носителя.</p>\r\n\r\n<p>3.4.3. Факт уничтожения персональных данных подтверждается документально актом об уничтожении носителей.</p>\r\n\r\n<p>3.5. Передача персональных данных.</p>\r\n\r\n<p>3.5.1. Оператор передает персональные данные третьим лицам в следующих случаях:</p>\r\n\r\n<p>&ndash; субъект выразил свое согласие на такие действия;</p>\r\n\r\n<p>&ndash; передача предусмотрена российским или иным применимым законодательством в рамках установленной законодательством процедуры.</p>\r\n\r\n<p>3.5.2. Перечень лиц, которым передаются персональные данные.</p>\r\n\r\n<p>&ndash; Пенсионный фонд РФ для учета (на законных основаниях);</p>\r\n\r\n<p>&ndash; налоговые органы РФ (на законных основаниях);</p>\r\n\r\n<p>&ndash; Фонд социального страхования РФ (на законных основаниях);</p>\r\n\r\n<p>&ndash; территориальный фонд обязательного медицинского страхования (на законных основаниях);</p>\r\n\r\n<p>&ndash; страховые медицинские организации по обязательному и добровольному медицинскому страхованию (на законных основаниях);</p>\r\n\r\n<p>&ndash; банки для начисления заработной платы (на основании договора);</p>\r\n\r\n<p>&ndash; органы МВД России в случаях, установленных законодательством;</p>\r\n\r\n<p>&ndash; обезличенные персональные данные Пользователей сайта организации передаются контрагентам организации.</p>\r\n\r\n<p><strong>4. Защита персональных данных</strong></p>\r\n\r\n<p>4.1. В соответствии с требованиями нормативных документов Оператором создана система защиты персональных данных (СЗПД), состоящая из подсистем правовой, организационной и технической защиты.</p>\r\n\r\n<p>4.2. Подсистема правовой защиты представляет собой комплекс правовых, организационно-распорядительных и нормативных документов, обеспечивающих создание, функционирование и совершенствование СЗПД.</p>\r\n\r\n<p>4.3. Подсистема организационной защиты включает в себя организацию структуры управления СЗПД, разрешительной системы, защиты информации при работе с сотрудниками, партнерами и сторонними лицами.</p>\r\n\r\n<p>4.4. Подсистема технической защиты включает в себя комплекс технических, программных, программно-аппаратных средств, обеспечивающих защиту персональных данных.</p>\r\n\r\n<p>4.4. Основными мерами защиты персональных данных, используемыми Оператором, являются:</p>\r\n\r\n<p>4.5.1. Назначение лица, ответственного за обработку персональных данных, которое осуществляет организацию обработки персональных данных, обучение и инструктаж, внутренний контроль за соблюдением учреждением и его работниками требований к защите персональных данных.</p>\r\n\r\n<p>4.5.2. Определение актуальных угроз безопасности персональных данных при их обработке в ИСПД и разработка мер и мероприятий по защите персональных данных.</p>\r\n\r\n<p>4.5.3. Разработка политики в отношении обработки персональных данных.</p>\r\n\r\n<p>4.5.4. Установление правил доступа к персональных данных, обрабатываемым в ИСПД, а также обеспечение регистрации и учета всех действий, совершаемых с персональными данными в ИСПД.</p>\r\n\r\n<p>4.5.5. Установление индивидуальных паролей доступа сотрудников в информационную систему в соответствии с их производственными обязанностями.</p>\r\n\r\n<p>4.5.6. Применение прошедших в установленном порядке процедуру оценки соответствия средств защиты информации.</p>\r\n\r\n<p>4.5.7. Сертифицированное антивирусное программное обеспечение с регулярно обновляемыми базами.</p>\r\n\r\n<p>4.5.8. Соблюдение условий, обеспечивающих сохранность персональных данных и исключающих несанкционированный к ним доступ.</p>\r\n\r\n<p>4.5.9. Обнаружение фактов несанкционированного доступа к персональным данным и принятие мер.</p>\r\n\r\n<p>4.5.10. Восстановление персональных данных, модифицированных или уничтоженных вследствие несанкционированного доступа к ним.</p>\r\n\r\n<p>4.5.11. Обучение работников Оператора, непосредственно осуществляющих обработку персональных данных, положениям законодательства РФ о персональных данных, в том числе требованиям к защите персональных данных, документам, определяющим политику Оператора в отношении обработки персональных данных, локальным актам по вопросам обработки персональных данных.</p>\r\n\r\n<p>4.5.12. Осуществление внутреннего контроля и аудита.</p>\r\n\r\n<p><strong>5. Основные права субъекта персональных данных и обязанности</strong></p>\r\n\r\n<p><strong>Оператора</strong></p>\r\n\r\n<p>5.1. Основные права субъекта персональных данных.</p>\r\n\r\n<p>Субъект имеет право на доступ к его персональным данным и следующим сведениям:</p>\r\n\r\n<p>&ndash; подтверждение факта обработки персональных данных Оператором;</p>\r\n\r\n<p>&ndash; правовые основания и цели обработки персональных данных;</p>\r\n\r\n<p>&ndash; цели и применяемые Оператором способы обработки персональных данных;</p>\r\n\r\n<p>&ndash; наименование и место нахождения Оператора, сведения о лицах (за исключением работников Оператора), которые имеют доступ к персональных данных или которым могут быть раскрыты персональные данные на основании договора с Оператором или на основании федерального закона;</p>\r\n\r\n<p>&ndash; сроки обработки персональных данных, в том числе сроки их хранения;</p>\r\n\r\n<p>&ndash; порядок осуществления субъектом персональных данных прав, предусмотренных Федеральным законом;</p>\r\n\r\n<p>&ndash; наименование или фамилия, имя, отчество и адрес лица, осуществляющего обработку персональных данных по поручению Оператора, если обработка поручена или будет поручена такому лицу;</p>\r\n\r\n<p>&ndash; обращение к Оператору и направление ему запросов;</p>\r\n\r\n<p>&ndash; обжалование действий или бездействия Оператора.</p>\r\n\r\n<p>5.2. Обязанности Оператора.</p>\r\n\r\n<p>Оператор обязан:</p>\r\n\r\n<p>&ndash; при сборе персональных данных предоставить информацию об обработке персональных данных;</p>\r\n\r\n<p>&ndash; в случаях если персональные данные были получены не от субъекта персональных данных, уведомить субъекта;</p>\r\n\r\n<p>&ndash; при отказе в предоставлении персональных данных субъекту разъясняются последствия такого отказа;</p>\r\n\r\n<p>&ndash; опубликовать или иным образом обеспечить неограниченный доступ к документу, определяющему его политику в отношении обработки персональных данных, к сведениям о реализуемых требованиях к защите персональных данных;</p>\r\n\r\n<p>&ndash; принимать необходимые правовые, организационные и технические меры или обеспечивать их принятие для защиты персональных данных от неправомерного или случайного доступа к ним, уничтожения, изменения, блокирования, копирования, предоставления, распространения персональных данных, а также от иных неправомерных действий в отношении персональных</p>\r\n\r\n<p>данных;</p>\r\n\r\n<p>&ndash; давать ответы на запросы и обращения субъектов персональных данных, их представителей и уполномоченного органа по защите прав субъектов персональных данных.</p>\r\n', '', '', '', '', '', '', 'ПОЛИТИК ОРГАНИЗАЦИЯ ПО ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ', '1 ОБЩИЙ ПОЛОЖЕНИЕ NBSP 1 1 НАСТОЯЩИЙ ПОЛИТИК В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДАЛЕЕ NDASH ПОЛИТИК СОСТАВИТЬ В СООТВЕТСТВИЕ С ПУНКТ 2 СТАТЬЯ 18 1 ФЕДЕРАЛЬНЫЙ ЗАКОН LAQUO О ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO ?? 152-ФЗ ОТ 27 ИЮЛЬ 2006 Г А ТАКЖЕ ИНОЙ НОРМАТИВНЫЙ ПРАВОВОЙ АКТ РОССИЙСКИЙ ФЕДЕРАЦИЯ В ОБЛАСТЬ ЗАЩИТА И ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ДЕЙСТВОВАТЬ В ОТНОШЕНИЕ ВЕСЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДАЛЕЕ NDASH ДАННЫЙ КОТОРЫЙ ОРГАНИЗАЦИЯ ДАЛЕЕ NDASH ОПЕРАТОР ОБЩЕСТВО МОЖЕТ ПОЛУЧИТЬ ОТ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЯВЛЯТЬСЯ СТОРОНОЙ ПО ГРАЖДАНСКО-ПРАВОВОЙ ДОГОВОР ОТ ПОЛЬЗОВАТЕЛЬ СЕТЬ ИНТЕРНЕТ ДАЛЕЕ NDASH ПОЛЬЗОВАТЕЛЬ ВО ВРЕМЯ ИСПОЛЬЗОВАНИЕ ИМЯ ЛЮБОЙ ИЗ САЙТ СЕРВИС СЛУЖБА ПРОГРАММА ПРОДУКТ ИЛИ УСЛУГА ООО LAQUO RAQUO А ТАКЖЕ ОТ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СОСТОЯТЬ С ОПЕРАТОР В ОТНОШЕНИЕ РЕГУЛИРУЕМЫЙ ТРУДОВОЙ ЗАКОНОДАТЕЛЬСТВО ДАЛЕЕ NDASH РАБОТНИК 1 2 ОПЕРАТОР ОБЕСПЕЧИВАТЬ ЗАЩИТА ОБРАБАТЫВАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОТ НЕСАНКЦИОНИРОВАННЫЙ ДОСТУП И РАЗГЛАШЕНИЕ НЕПРАВОМЕРНЫЙ ИСПОЛЬЗОВАНИЕ ИЛИ УТРАТА В СООТВЕТСТВИЕ С ТРЕБОВАНИЕ ФЕДЕРАЛЬНЫЙ ЗАКОН ОТ 27 ИЮЛЬ 2006 Г ?? 152-ФЗ LAQUO О ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO 1 3 ОПЕРАТОР ВПРАВЕ ВНОСИТЬ ИЗМЕНЕНИЕ В НАСТОЯЩИЙ ПОЛИТИК ПРИ ВНЕСЕНИЕ ИЗМЕНЕНИЕ В ЗАГОЛОВОК ПОЛИТИК УКАЗЫВАТЬСЯ ДАТА ПОСЛЕДНИЙ ОБНОВЛЕНИЕ РЕДАКЦИЯ НОВЫЙ РЕДАКЦИЯ ПОЛИТИК ВСТУПАТЬ В СИЛА С МОМЕНТ ЕЕ РАЗМЕЩЕНИЕ НА САЙТ ЕСЛИ ИНОЙ НЕ ПРЕДУСМОТРЕТЬ НОВЫЙ РЕДАКЦИЯ ПОЛИТИК 2 ТЕРМИН И ПРИНЯТЬ СОКРАЩЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ЛЮБОЙ ИНФОРМАЦИЯ ОТНОСИТЬСЯ К ПРЯМО ИЛИ КОСВЕННО ОПРЕДЕЛЕННЫЙ ИЛИ ОПРЕДЕЛЯТЬ ФИЗИЧЕСКИЙ ЛИЦО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ЛЮБОЙ ДЕЙСТВИЕ ОПЕРАЦИЯ ИЛИ СОВОКУПНОСТЬ ДЕЙСТВИЕ ОПЕРАЦИЯ СОВЕРШАТЬ С ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ ИЛИ БЕЗ ИСПОЛЬЗОВАНИЕ ТАКОЙ СРЕДСТВО С ПЕРСОНАЛЬНЫЙ ДАННЫЙ ВКЛЮЧАЯ СБОР ЗАПИСЬ СИСТЕМАТИЗАЦИЯ НАКОПЛЕНИЕ ХРАНЕНИЕ УТОЧНЕНИЕ ОБНОВЛЕНИЕ ИЗМЕНЕНИЕ ИЗВЛЕЧЕНИЕ ИСПОЛЬЗОВАНИЕ ПЕРЕДАЧА РАСПРОСТРАНЕНИЕ ПРЕДОСТАВЛЕНИЕ ДОСТУП ОБЕЗЛИЧИВАНИЕ БЛОКИРОВАНИЕ УДАЛЕНИЕ УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ АВТОМАТИЗИРОВАННЫЙ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ С ПОМОЩЬ СРЕДСТВО ВЫЧИСЛИТЕЛЬНЫЙ ТЕХНИК ИНФОРМАЦИОННЫЙ СИСТЕМА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИСПД NDASH СОВОКУПНОСТЬ СОДЕРЖАТЬСЯ В БАЗА ДАННЫЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ОБЕСПЕЧИВАТЬ ИХ ОБРАБОТКА ИНФОРМАЦИОННЫЙ ТЕХНОЛОГИЯ И ТЕХНИЧЕСКИЙ СРЕДСТВО ПЕРСОНАЛЬНЫЙ ДАННЫЙ СДЕЛАТЬ ОБЩЕДОСТУПНЫЙ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОСТУП НЕОГРАНИЧЕННЫЙ КРУГ ЛИЦО К КОТОРЫЙ ПРЕДОСТАВИТЬ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЛИБО ПО ЕГО ПРОСЬБА БЛОКИРОВАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ВРЕМЕННЫЙ ПРЕКРАЩЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЗА ИСКЛЮЧЕНИЕ СЛУЧАЙ ЕСЛИ ОБРАБОТКА НЕОБХОДИМЫЙ ДЛЯ УТОЧНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ДЕЙСТВИЕ В РЕЗУЛЬТАТ КОТОРЫЙ СТАНОВИТЬСЯ НЕВОЗМОЖНЫЙ ВОССТАНОВИТЬ СОДЕРЖАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ИНФОРМАЦИОННЫЙ СИСТЕМА ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ИЛИ В РЕЗУЛЬТАТ КОТОРЫЙ УНИЧТОЖАТЬСЯ МАТЕРИАЛЬНЫЙ НОСИТЕЛЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОПЕРАТОР NDASH ОРГАНИЗАЦИЯ САМОСТОЯТЕЛЬНО ИЛИ СОВМЕСТНО С ДРУГОЙ ЛИЦО ОРГАНИЗОВАТЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ А ТАКЖЕ ОПРЕДЕЛЯТЬ ЦЕЛЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОДЛЕЖАЩЕЕ ОБРАБОТКА ДЕЙСТВИЕ ОПЕРАЦИЯ СОВЕРШАТЬ С ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОПЕРАТОР ЯВЛЯТЬСЯ ООО LAQUO RAQUO РАСПОЛОЖИТЬ ПО АДРЕС 3 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 1 ПОЛУЧЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 1 1 ВСЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЛЕДОВАТЬ ПОЛУЧАТЬ ОТ САМЫЙ СУБЪЕКТ ЕСЛИ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ МОЖНО ПОЛУЧИТЬ ТОЛЬКО У ТРЕТИЙ СТОРОНА ТО СУБЪЕКТ ДОЛЖНЫЙ БЫТЬ УВЕДОМИТЬ ОБ ЭТО ИЛИ ОТ ОН ДОЛЖНЫЙ БЫТЬ ПОЛУЧИТЬ СОГЛАСИЕ 3 1 2 ОПЕРАТОР ДОЛЖНЫЙ СООБЩИТЬ СУБЪЕКТ О ЦЕЛЬ ПРЕДПОЛАГАТЬ ИСТОЧНИК И СПОСОБ ПОЛУЧЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ХАРАКТЕР ПОДЛЕЖАЩЕЕ ПОЛУЧЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПЕРЕЧЕНЬ ДЕЙСТВИЕ С ПЕРСОНАЛЬНЫЙ ДАННЫЙ СРОК В ТЕЧЕНИЕ КОТОРЫЙ ДЕЙСТВОВАТЬ СОГЛАСИЕ И ПОРЯДОК ЕГО ОТЗЫВ А ТАКЖЕ О ПОСЛЕДСТВИЕ ОТКАЗ СУБЪЕКТ ДАТЬ ПИСЬМЕННЫЙ СОГЛАСИЕ НА ИХ ПОЛУЧЕНИЕ 3 1 3 ДОКУМЕНТ СОДЕРЖАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СОЗДАВАТЬСЯ ПУТЬ NDASH КОПИРОВАНИЕ ОРИГИНАЛ ДОКУМЕНТ ПАСПОРТ ДОКУМЕНТ ОБ ОБРАЗОВАНИЕ СВИДЕТЕЛЬСТВО ИННА ПЕНСИОННЫЙ СВИДЕТЕЛЬСТВО И ДР NDASH ВНЕСЕНИЕ СВЕДЕНИЕ В УЧЕТНЫЙ ФОРМА NDASH ПОЛУЧЕНИЕ ОРИГИНАЛ НЕОБХОДИМЫЙ ДОКУМЕНТ ТРУДОВОЙ КНИЖКА МЕДИЦИНСКИЙ ЗАКЛЮЧЕНИЕ ХАРАКТЕРИСТИКА И ДР 3 2 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 2 1 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОСУЩЕСТВЛЯТЬСЯ NDASH С СОГЛАСИЕ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ НА ОБРАБОТКА ЕГО ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH В СЛУЧАЙ КОГДА ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ НЕОБХОДИМЫЙ ДЛЯ ОСУЩЕСТВЛЕНИЕ И ВЫПОЛНЕНИЕ ВОЗЛОЖИТЬ ЗАКОНОДАТЕЛЬСТВО РФ ФУНКЦИЯ ПОЛНОМОЧИЕ И ОБЯЗАННОСТЬ NDASH В СЛУЧАЙ КОГДА ОСУЩЕСТВЛЯТЬСЯ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОСТУП НЕОГРАНИЧЕННЫЙ КРУГ ЛИЦО К КОТОРЫЙ ПРЕДОСТАВИТЬ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЛИБО ПО ЕГО ПРОСЬБА ДАЛЕЕ NDASH ПЕРСОНАЛЬНЫЙ ДАННЫЙ СДЕЛАТЬ ОБЩЕДОСТУПНЫЙ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 2 2 ЦЕЛЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ОСУЩЕСТВЛЕНИЕ ТРУДОВОЙ ОТНОШЕНИЕ NDASH ОСУЩЕСТВЛЕНИЕ ГРАЖДАНСКО-ПРАВОВОЙ ОТНОШЕНИЕ NDASH ДЛЯ ИДЕНТИФИКАЦИЯ ПОЛЬЗОВАТЕЛЬ ПОСЕТИТЕЛЬ САЙТ ДЛЯ СВЯЗЬ С ПОЛЬЗОВАТЕЛЬ В ТОМ ЧИСЛО НАПРАВЛЕНИЕ УВЕДОМЛЕНИЕ ЗАПРОС И ИНФОРМАЦИЯ КАСАТЬСЯ ИСПОЛЬЗОВАНИЕ УСЛУГА КОМПАНИЯ ИСПОЛНЕНИЕ СОГЛАШЕНИЕ И ДОГОВОР А ТАКЖЕ ОБРАБОТКА ЗАПРОС И ЗАЯВКА ОТ ПОЛЬЗОВАТЕЛЬ - ОБЕЗЛИЧИВАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДЛЯ ПОЛУЧЕНИЕ ОБЕЗЛИЧИТЬ СТАТИСТИЧЕСКИЙ ДАННЫЙ КОТОРЫЙ ПЕРЕДАВАТЬСЯ ТРЕТИЙ ЛИЦО ДЛЯ ПРОВЕДЕНИЕ ИССЛЕДОВАНИЕ ВЫПОЛНЕНИЕ РАБОТА ИЛИ ОКАЗАНИЕ УСЛУГА ПО ПОРУЧЕНИЕ ОРГАНИЗАЦИЯ 3 2 3 КАТЕГОРИЯ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБАТЫВАТЬСЯ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЛЕДУЮЩИЙ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ФИЗИЧЕСКИЙ ЛИЦО СОСТОЯТЬ С ОБЩЕСТВО В ТРУДОВОЙ ОТНОШЕНИЕ NDASH ФИЗИЧЕСКИЙ ЛИЦО УВОЛИТЬСЯ ИЗ ОБЩЕСТВО NDASH ФИЗИЧЕСКИЙ ЛИЦО ЯВЛЯТЬСЯ КАНДИДАТ НА РАБОТА NDASH ФИЗИЧЕСКИЙ ЛИЦО СОСТОЯТЬ С ОБЩЕСТВО В ГРАЖДАНСКО-ПРАВОВОЙ ОТНОШЕНИЕ NDASH ФИЗИЧЕСКИЙ ЛИЦО ЯВЛЯТЬСЯ ПОЛЬЗОВАТЕЛЬ САЙТ ОРГАНИЗАЦИЯ 3 2 4 ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБАТЫВАТЬ ОПЕРАТОР NDASH ДАННЫЙ ПОЛУЧИТЬ ПРИ ОСУЩЕСТВЛЕНИЕ ТРУДОВОЙ ОТНОШЕНИЕ NDASH ДАННЫЙ ПОЛУЧИТЬ ДЛЯ ОСУЩЕСТВЛЕНИЕ ОТБОР КАНДИДАТ НА РАБОТА NDASH ДАННЫЙ ПОЛУЧИТЬ ПРИ ОСУЩЕСТВЛЕНИЕ ГРАЖДАНСКО-ПРАВОВОЙ ОТНОШЕНИЕ NDASH ДАННЫЙ ПОЛУЧИТЬ ОТ ПОЛЬЗОВАТЕЛЬ САЙТ ОРГАНИЗАЦИЯ 3 2 5 ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ВЕСТИСЬ NDASH С ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ NDASH БЕЗ ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ 3 3 ХРАНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 3 1 ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ МОЧЬ БЫТЬ ПОЛУЧИТЬ ПРОХОДИТЬ ДАЛЬНЕЙШИЙ ОБРАБОТКА И ПЕРЕДАВАТЬСЯ НА ХРАНЕНИЕ КАК НА БУМАЖНЫЙ НОСИТЕЛЬ ТАК И В ЭЛЕКТРОННЫЙ ВИД 3 3 2 ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЗАФИКСИРОВАТЬ НА БУМАЖНЫЙ НОСИТЕЛЬ ХРАНИТЬСЯ В ЗАПИРАТЬ ШКАФ ЛИБО В ЗАПИРАТЬ ПОМЕЩЕНИЕ С ОГРАНИЧЕННЫЙ ПРАВО ДОСТУП 3 3 3 ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ ОБРАБАТЫВАТЬ С ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ В РАЗНЫЙ ЦЕЛЬ ХРАНИТЬСЯ В РАЗНЫЙ ПАПКА 3 3 4 НЕ ДОПУСКАТЬСЯ ХРАНЕНИЕ И РАЗМЕЩЕНИЕ ДОКУМЕНТ СОДЕРЖАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ОТКРЫТЫЙ ЭЛЕКТРОННЫЙ КАТАЛОГ ФАЙЛООБМЕННИКА В ИСПД 3 3 5 ХРАНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ФОРМА ПОЗВОЛЯТЬ ОПРЕДЕЛИТЬ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОСУЩЕСТВЛЯТЬСЯ НЕ ДОЛГИЙ ЧЕМ ЭТО ТРЕБОВАТЬ ЦЕЛЬ ИХ ОБРАБОТКА И ОНИ ПОДЛЕЖАТЬ УНИЧТОЖЕНИЕ ПО ДОСТИЖЕНИЕ ЦЕЛЬ ОБРАБОТКА ИЛИ В СЛУЧАЙ УТРАТА НЕОБХОДИМОСТЬ В ИХ ДОСТИЖЕНИЕ 3 4 УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 4 1 УНИЧТОЖЕНИЕ ДОКУМЕНТ НОСИТЕЛЬ СОДЕРЖАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРОИЗВОДИТЬСЯ ПУТЬ СОЖЖЕНИЕ ДРОБЛЕНИЕ ИЗМЕЛЬЧЕНИЕ ХИМИЧЕСКИЙ РАЗЛОЖЕНИЕ ПРЕВРАЩЕНИЕ В БЕСФОРМЕННЫЙ МАССА ИЛИ ПОРОШОК ДЛЯ УНИЧТОЖЕНИЕ БУМАЖНЫЙ ДОКУМЕНТ ДОПУСКАТЬСЯ ПРИМЕНЕНИЕ ШРЕДЕР 3 4 2 ПЕРСОНАЛЬНЫЙ ДАННЫЙ НА ЭЛЕКТРОННЫЙ НОСИТЕЛЬ УНИЧТОЖАТЬСЯ ПУТЬ СТИРАНИЕ ИЛИ ФОРМАТИРОВАНИЕ НОСИТЕЛЬ 3 4 3 ФАКТ УНИЧТОЖЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОДТВЕРЖДАТЬСЯ ДОКУМЕНТАЛЬНЫЙ АКТ ОБ УНИЧТОЖЕНИЕ НОСИТЕЛЬ 3 5 ПЕРЕДАЧА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 3 5 1 ОПЕРАТОР ПЕРЕДАТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ТРЕТИЙ ЛИЦО В СЛЕДУЮЩИЙ СЛУЧАЙ NDASH СУБЪЕКТ ВЫРАЗИТЬ СВОЕ СОГЛАСИЕ НА ТАКОЙ ДЕЙСТВИЕ NDASH ПЕРЕДАЧА ПРЕДУСМОТРЕТЬ РОССИЙСКИЙ ИЛИ ИНОЙ ПРИМЕНИМЫЙ ЗАКОНОДАТЕЛЬСТВО В РАМКА УСТАНОВЛЕННЫЙ ЗАКОНОДАТЕЛЬСТВО ПРОЦЕДУРА 3 5 2 ПЕРЕЧЕНЬ ЛИЦО КОТОРЫЙ ПЕРЕДАВАТЬСЯ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ПЕНСИОННЫЙ ФОНД РФ ДЛЯ УЧЕТ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH НАЛОГОВЫЙ ОРГАН РФ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH ФОНД СОЦИАЛЬНЫЙ СТРАХОВАНИЕ РФ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH ТЕРРИТОРИАЛЬНЫЙ ФОНД ОБЯЗАТЕЛЬНЫЙ МЕДИЦИНСКИЙ СТРАХОВАНИЕ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH СТРАХОВОЙ МЕДИЦИНСКИЙ ОРГАНИЗАЦИЯ ПО ОБЯЗАТЕЛЬНЫЙ И ДОБРОВОЛЬНЫЙ МЕДИЦИНСКИЙ СТРАХОВАНИЕ НА ЗАКОННЫЙ ОСНОВАНИЕ NDASH БАНКА ДЛЯ НАЧИСЛЕНИЕ ЗАРАБОТНЫЙ ПЛАТ НА ОСНОВАНИЕ ДОГОВОР NDASH ОРГАН МВД РОССИЯ В СЛУЧАЙ УСТАНОВЛЕННЫЙ ЗАКОНОДАТЕЛЬСТВО NDASH ОБЕЗЛИЧИТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОЛЬЗОВАТЕЛЬ САЙТ ОРГАНИЗАЦИЯ ПЕРЕДАВАТЬСЯ КОНТРАГЕНТ ОРГАНИЗАЦИЯ 4 ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 1 В СООТВЕТСТВИЕ С ТРЕБОВАНИЕ НОРМАТИВНЫЙ ДОКУМЕНТ ОПЕРАТОР СОЗДАТЬ СИСТЕМА ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЗПД СОСТОЯТЬ ИЗ ПОДСИСТЕМА ПРАВОВОЙ ОРГАНИЗАЦИОННЫЙ И ТЕХНИЧЕСКИЙ ЗАЩИТА 4 2 ПОДСИСТЕМА ПРАВОВОЙ ЗАЩИТА ПРЕДСТАВЛЯТЬ СЕБЯ КОМПЛЕКС ПРАВОВОЙ ОРГАНИЗАЦИОННО-РАСПОРЯДИТЕЛЬНЫЙ И НОРМАТИВНЫЙ ДОКУМЕНТ ОБЕСПЕЧИВАТЬ СОЗДАНИЕ ФУНКЦИОНИРОВАНИЕ И СОВЕРШЕНСТВОВАНИЕ СЗПД 4 3 ПОДСИСТЕМА ОРГАНИЗАЦИОННЫЙ ЗАЩИТА ВКЛЮЧАТЬ В СЕБЯ ОРГАНИЗАЦИЯ СТРУКТУРА УПРАВЛЕНИЕ СЗПД РАЗРЕШИТЕЛЬНЫЙ СИСТЕМА ЗАЩИТА ИНФОРМАЦИЯ ПРИ РАБОТА С СОТРУДНИК ПАРТНЕР И СТОРОННИЙ ЛИЦО 4 4 ПОДСИСТЕМА ТЕХНИЧЕСКИЙ ЗАЩИТА ВКЛЮЧАТЬ В СЕБЯ КОМПЛЕКС ТЕХНИЧЕСКИЙ ПРОГРАММНЫЙ ПРОГРАММНО-АППАРАТНЫЙ СРЕДСТВО ОБЕСПЕЧИВАТЬ ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 4 ОСНОВНЫЙ МЕРА ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИСПОЛЬЗОВАТЬ ОПЕРАТОР ЯВЛЯТЬСЯ 4 5 1 НАЗНАЧЕНИЕ ЛИЦО ОТВЕТСТВЕННЫЙ ЗА ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ КОТОРЫЙ ОСУЩЕСТВЛЯТЬ ОРГАНИЗАЦИЯ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБУЧЕНИЕ И ИНСТРУКТАЖ ВНУТРЕННИЙ КОНТРОЛЬ ЗА СОБЛЮДЕНИЕ УЧРЕЖДЕНИЕ И ЕГО РАБОТНИК ТРЕБОВАНИЕ К ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 2 ОПРЕДЕЛЕНИЕ АКТУАЛЬНЫЙ УГРОЗА БЕЗОПАСНОСТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРИ ИХ ОБРАБОТКА В ИСПД И РАЗРАБОТКА МЕРА И МЕРОПРИЯТИЕ ПО ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 3 РАЗРАБОТКА ПОЛИТИК В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 4 УСТАНОВЛЕНИЕ ПРАВИТЬ ДОСТУП К ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОБРАБАТЫВАТЬ В ИСПД А ТАКЖЕ ОБЕСПЕЧЕНИЕ РЕГИСТРАЦИЯ И УЧЕТ ВЕСЬ ДЕЙСТВИЕ СОВЕРШАТЬ С ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ИСПД 4 5 5 УСТАНОВЛЕНИЕ ИНДИВИДУАЛЬНЫЙ ПАРОЛЬ ДОСТУП СОТРУДНИК В ИНФОРМАЦИОННЫЙ СИСТЕМА В СООТВЕТСТВИЕ С ИХ ПРОИЗВОДСТВЕННЫЙ ОБЯЗАННОСТЬ 4 5 6 ПРИМЕНЕНИЕ ПРОШЕДШЕЕ В УСТАНОВЛЕННЫЙ ПОРЯДОК ПРОЦЕДУРА ОЦЕНКА СООТВЕТСТВИЕ СРЕДСТВО ЗАЩИТА ИНФОРМАЦИЯ 4 5 7 СЕРТИФИЦИРОВАННЫЙ АНТИВИРУСНЫЙ ПРОГРАММНЫЙ ОБЕСПЕЧЕНИЕ С РЕГУЛЯРНО ОБНОВЛЯТЬ БАЗА 4 5 8 СОБЛЮДЕНИЕ УСЛОВИЕ ОБЕСПЕЧИВАТЬ СОХРАННОСТЬ ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ИСКЛЮЧАТЬ НЕСАНКЦИОНИРОВАННЫЙ К ОН ДОСТУП 4 5 9 ОБНАРУЖЕНИЕ ФАКТ НЕСАНКЦИОНИРОВАННЫЙ ДОСТУП К ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ПРИНЯТИЕ МЕРА 4 5 10 ВОССТАНОВЛЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ МОДИФИЦИРОВАННЫЙ ИЛИ УНИЧТОЖИТЬ ВСЛЕДСТВИЕ НЕСАНКЦИОНИРОВАННЫЙ ДОСТУП К ОН 4 5 11 ОБУЧЕНИЕ РАБОТНИК ОПЕРАТОР НЕПОСРЕДСТВЕННО ОСУЩЕСТВЛЯТЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПОЛОЖЕНИЕ ЗАКОНОДАТЕЛЬСТВО РФ О ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ТОМ ЧИСЛО ТРЕБОВАНИЕ К ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОКУМЕНТ ОПРЕДЕЛЯТЬ ПОЛИТИК ОПЕРАТОР В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ЛОКАЛЬНЫЙ АКТАМ ПО ВОПРОС ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ 4 5 12 ОСУЩЕСТВЛЕНИЕ ВНУТРЕННИЙ КОНТРОЛЬ И АУДИТ 5 ОСНОВНЫЙ ПРАВО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ И ОБЯЗАННОСТЬ ОПЕРАТОР 5 1 ОСНОВНЫЙ ПРАВО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ ИМЕТЬ ПРАВО НА ДОСТУП К ЕГО ПЕРСОНАЛЬНЫЙ ДАННЫЙ И СЛЕДУЮЩИЙ СВЕДЕНИЕ NDASH ПОДТВЕРЖДЕНИЕ ФАКТ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОПЕРАТОР NDASH ПРАВОВОЙ ОСНОВАНИЕ И ЦЕЛЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ЦЕЛЬ И ПРИМЕНЯТЬ ОПЕРАТОР СПОСОБ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH НАИМЕНОВАНИЕ И МЕСТО НАХОЖДЕНИЕ ОПЕРАТОР СВЕДЕНИЕ О ЛИЦО ЗА ИСКЛЮЧЕНИЕ РАБОТНИК ОПЕРАТОР КОТОРЫЙ ИМЕТЬ ДОСТУП К ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИЛИ КОТОРЫЙ МОЧЬ БЫТЬ РАСКРЫТЫЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ НА ОСНОВАНИЕ ДОГОВОР С ОПЕРАТОР ИЛИ НА ОСНОВАНИЕ ФЕДЕРАЛЬНЫЙ ЗАКОН NDASH СРОК ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ В ТОМ ЧИСЛО СРОК ИХ ХРАНЕНИЕ NDASH ПОРЯДОК ОСУЩЕСТВЛЕНИЕ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРАВО ПРЕДУСМОТРЕННЫЙ ФЕДЕРАЛЬНЫЙ ЗАКОН NDASH НАИМЕНОВАНИЕ ИЛИ ФАМИЛИЯ ИМЯ ОТЧЕСТВО И АДРЕС ЛИЦО ОСУЩЕСТВЛЯТЬ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПО ПОРУЧЕНИЕ ОПЕРАТОР ЕСЛИ ОБРАБОТКА ПОРУЧИТЬ ИЛИ БЫТЬ ПОРУЧИТЬ ТАКОЙ ЛИЦО NDASH ОБРАЩЕНИЕ К ОПЕРАТОР И НАПРАВЛЕНИЕ ОН ЗАПРОС NDASH ОБЖАЛОВАНИЕ ДЕЙСТВИЕ ИЛИ БЕЗДЕЙСТВИЕ ОПЕРАТОР 5 2 ОБЯЗАННОСТЬ ОПЕРАТОР ОПЕРАТОР ОБЯЗАТЬ NDASH ПРИ СБОР ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПРЕДОСТАВИТЬ ИНФОРМАЦИЯ ОБ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH В СЛУЧАЙ ЕСЛИ ПЕРСОНАЛЬНЫЙ ДАННЫЙ БЫЛЬ ПОЛУЧИТЬ НЕ ОТ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ УВЕДОМИТЬ СУБЪЕКТ NDASH ПРИ ОТКАЗ В ПРЕДОСТАВЛЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СУБЪЕКТ РАЗЪЯСНЯТЬСЯ ПОСЛЕДСТВИЕ ТАКОЙ ОТКАЗ NDASH ОПУБЛИКОВАТЬ ИЛИ ИНОЙ ОБРАЗ ОБЕСПЕЧИТЬ НЕОГРАНИЧЕННЫЙ ДОСТУП К ДОКУМЕНТ ОПРЕДЕЛЯТЬ ЕГО ПОЛИТИК В ОТНОШЕНИЕ ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ К СВЕДЕНИЕ О РЕАЛИЗОВАТЬ ТРЕБОВАНИЕ К ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ПРИНИМАТЬ НЕОБХОДИМЫЙ ПРАВОВОЙ ОРГАНИЗАЦИОННЫЙ И ТЕХНИЧЕСКИЙ МЕРА ИЛИ ОБЕСПЕЧИВАТЬ ИХ ПРИНЯТИЕ ДЛЯ ЗАЩИТА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ОТ НЕПРАВОМЕРНЫЙ ИЛИ СЛУЧАЙНЫЙ ДОСТУП К ОН УНИЧТОЖЕНИЕ ИЗМЕНЕНИЕ БЛОКИРОВАНИЕ КОПИРОВАНИЕ ПРЕДОСТАВЛЕНИЕ РАСПРОСТРАНЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ А ТАКЖЕ ОТ ИНОЙ НЕПРАВОМЕРНЫЙ ДЕЙСТВИЕ В ОТНОШЕНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ NDASH ДАВАТЬ ОТВЕТ НА ЗАПРОС И ОБРАЩЕНИЕ СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ИХ ПРЕДСТАВИТЕЛЬ И УПОЛНОМОЧЕННЫЙ ОРГАН ПО ЗАЩИТА ПРАВО СУБЪЕКТ ПЕРСОНАЛЬНЫЙ ДАННЫЙ', 0, 0),

(5, 0, 'Согласие посетителя сайта на обработку персональных данных', '', '', '', '<p>1. Выражаю ООО &laquo;&raquo; и его представителям, свое согласие на автоматизированную и неавтоматизированную обработку моих персональных данных, включая сбор, систематизацию, накопление, хранение, уточнение (обновление, изменение), использование, распространение (в том числе передачу), обезличивание, блокирование, уничтожение следующих моих персональных данных, переданных мною лично ООО &laquo;&laquo;&raquo;&raquo;, в т.ч. с использованием интернет-сервисов</p>\r\n\r\n<p>Google analytics, Яндекс.Метрика:</p>\r\n\r\n<p>1.1.фамилия, имя, отчество,</p>\r\n\r\n<p>1.2.номер телефона (рабочий/личный сотовый/домашний);</p>\r\n\r\n<p>1.3. условия заказа в случае его оформления;</p>\r\n\r\n<p>1.4. адрес доставки в случае его указания при оформлении заказа;</p>\r\n\r\n<p>1.5. источник захода на сайт dimmtrans.ru (далее &ndash; сайт) и информация поискового или рекламного запроса;</p>\r\n\r\n<p>1.6. данные о пользовательском устройстве (среди которых разрешение, версия и другие атрибуты, характеризуемые пользовательское устройство, IP-адрес);</p>\r\n\r\n<p>1.7. информация о браузере пользователя (или иной программе, с помощью которой осуществляется доступ в сеть Интернет), технические характеристики оборудования и программного обеспечения, используемых пользователем;</p>\r\n\r\n<p>1.8. дата и время доступа;</p>\r\n\r\n<p>1.9. пользовательские клики, просмотры страниц, заполнения полей, показы и просмотры баннеров и видео;</p>\r\n\r\n<p>1.10. данные, характеризующие аудиторные сегменты;</p>\r\n\r\n<p>1.11. параметры сессии;</p>\r\n\r\n<p>1.12. данные о времени посещения;</p>\r\n\r\n<p>1.13. идентификатор пользователя, хранимый в cookie.</p>\r\n\r\n<p>2. ООО &laquo;&laquo;&raquo;&raquo; может обрабатывать мои персональные данные сроком 1 год в следующих целях:</p>\r\n\r\n<p>2.1.идентификации пользователей (посетителей) сайта ООО &laquo;&laquo;&raquo;&raquo;;</p>\r\n\r\n<p>2.2. связи со мной, в том числе направление уведомлений, запросов и информации, касающихся использования сайта, исполнения соглашений и договоров, а также обработки запросов и заявок от меня.</p>\r\n\r\n<p>2.3.обезличивания персональных данных для получения обезличенных статистических данных, которые передаются третьему лицу для проведения исследований, выполнения работ или оказания услуг по поручению ООО &laquo;&laquo;&raquo;&raquo;.</p>\r\n\r\n<p>3. Обработка (на бумажных носителях; в информационных системах персональных данных и без использования средств автоматизации, а также смешанным способом) ООО &laquo;&laquo;&raquo;&raquo; моих персональных данных должна осуществляться в соответствии с требованиями Федерального закона от 27.07.2006г. № 152-ФЗ &laquo;О персональных данных&raquo;, &laquo;Политикой защиты и обработки персональных данных&raquo; ООО &laquo;&laquo;&raquo;&raquo;.</p>\r\n\r\n<p>4. Также даю свое согласие на предоставление ООО &laquo;&laquo;&raquo;&raquo; моих персональных данных как посетителя сайта агентствам, с которыми он сотрудничает. ООО &laquo;&laquo;&raquo;&raquo; вправе осуществлять обработку моих персональных данных следующими способами: сбор, запись, систематизация, накопление, хранение, обновление, изменение, использование, передача (распространение, предоставление, доступ).</p>\r\n\r\n<p>5. Настоящее согласие, в том числе на передачу персональных данных третьей стороне в соответствии с п. 4 настоящего согласия, вступает в силу с момента моего перехода на сайт ООО &laquo;&laquo;&raquo;&raquo; и действует до дня отзыва в письменной форме.</p>\r\n\r\n<p>6. Настоящее согласие может быть отозвано письменным заявлением на e-mail.</p>\r\n', '', '', '', '', '', '', 'СОГЛАСИЕ ПОСЕТИТЕЛЬ САЙТ НА ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ', '1 ВЫРАЖАТЬ ООО LAQUO RAQUO И ЕГО ПРЕДСТАВИТЕЛЬ СВОЕ СОГЛАСИЕ НА АВТОМАТИЗИРОВАННЫЙ И НЕАВТОМАТИЗИРОВАННЫЙ ОБРАБОТКА МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ВКЛЮЧАЯ СБОР СИСТЕМАТИЗАЦИЯ НАКОПЛЕНИЕ ХРАНЕНИЕ УТОЧНЕНИЕ ОБНОВЛЕНИЕ ИЗМЕНЕНИЕ ИСПОЛЬЗОВАНИЕ РАСПРОСТРАНЕНИЕ В ТОМ ЧИСЛО ПЕРЕДАЧА ОБЕЗЛИЧИВАНИЕ БЛОКИРОВАНИЕ УНИЧТОЖЕНИЕ СЛЕДУЮЩИЙ МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ПЕРЕДАТЬ Я ЛИЧНО ООО LAQUO LAQUO RAQUO RAQUO В Т Ч С ИСПОЛЬЗОВАНИЕ ИНТЕРНЕТ-СЕРВИС GOOGLE ANALYTICS ЯНДЕКС МЕТРИКА 1 1 ФАМИЛИЯ ИМЯ ОТЧЕСТВО 1 2 НОМЕР ТЕЛЕФОН РАБОЧИЙ ЛИЧНЫЙ СОТОВЫЙ ДОМАШНИЙ 1 3 УСЛОВИЕ ЗАКАЗ В СЛУЧАЙ ЕГО ОФОРМЛЕНИЕ 1 4 АДРЕС ДОСТАВКА В СЛУЧАЙ ЕГО УКАЗАНИЕ ПРИ ОФОРМЛЕНИЕ ЗАКАЗ 1 5 ИСТОЧНИК ЗАХОД НА САЙТ DIMMTRANS RU ДАЛЕЕ NDASH САЙТ И ИНФОРМАЦИЯ ПОИСКОВЫЙ ИЛИ РЕКЛАМНЫЙ ЗАПРОС 1 6 ДАННЫЙ О ПОЛЬЗОВАТЕЛЬСКИЙ УСТРОЙСТВО СРЕДИ КОТОРЫЙ РАЗРЕШЕНИЕ ВЕРСИЯ И ДРУГОЙ АТРИБУТ ХАРАКТЕРИЗОВАТЬ ПОЛЬЗОВАТЕЛЬСКИЙ УСТРОЙСТВО IP-АДРЕС 1 7 ИНФОРМАЦИЯ О БРАУЗЕР ПОЛЬЗОВАТЕЛЬ ИЛИ ИНОЙ ПРОГРАММА С ПОМОЩЬ КОТОРЫЙ ОСУЩЕСТВЛЯТЬСЯ ДОСТУП В СЕТЬ ИНТЕРНЕТ ТЕХНИЧЕСКИЙ ХАРАКТЕРИСТИКА ОБОРУДОВАНИЕ И ПРОГРАММНЫЙ ОБЕСПЕЧЕНИЕ ИСПОЛЬЗОВАТЬ ПОЛЬЗОВАТЕЛЬ 1 8 ДАТА И ВРЕМЯ ДОСТУП 1 9 ПОЛЬЗОВАТЕЛЬСКИЙ КЛИК ПРОСМОТР СТРАНИЦА ЗАПОЛНЕНИЕ ПОЛЫЙ ПОКАЗ И ПРОСМОТР БАННЕР И ВИДЕО 1 10 ДАННЫЙ ХАРАКТЕРИЗОВАТЬ АУДИТОРНЫЙ СЕГМЕНТ 1 11 ПАРАМЕТР СЕССИЯ 1 12 ДАННЫЙ О ВРЕМЕНИТЬ ПОСЕЩЕНИЕ 1 13 ИДЕНТИФИКАТОР ПОЛЬЗОВАТЕЛЬ ХРАНИТЬ В COOKIE 2 ООО LAQUO LAQUO RAQUO RAQUO МОЖЕТ ОБРАБАТЫВАТЬ МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СРОК 1 ГОД В СЛЕДУЮЩИЙ ЦЕЛЬ 2 1 ИДЕНТИФИКАЦИЯ ПОЛЬЗОВАТЕЛЬ ПОСЕТИТЕЛЬ САЙТ ООО LAQUO LAQUO RAQUO RAQUO 2 2 СВЯЗЬ СО Я В ТОМ ЧИСЛО НАПРАВЛЕНИЕ УВЕДОМЛЕНИЕ ЗАПРОС И ИНФОРМАЦИЯ КАСАТЬСЯ ИСПОЛЬЗОВАНИЕ САЙТ ИСПОЛНЕНИЕ СОГЛАШЕНИЕ И ДОГОВОР А ТАКЖЕ ОБРАБОТКА ЗАПРОС И ЗАЯВКА ОТ МЕНЬ 2 3 ОБЕЗЛИЧИВАНИЕ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДЛЯ ПОЛУЧЕНИЕ ОБЕЗЛИЧИТЬ СТАТИСТИЧЕСКИЙ ДАННЫЙ КОТОРЫЙ ПЕРЕДАВАТЬСЯ ТРЕТИЙ ЛИЦО ДЛЯ ПРОВЕДЕНИЕ ИССЛЕДОВАНИЕ ВЫПОЛНЕНИЕ РАБОТА ИЛИ ОКАЗАНИЕ УСЛУГА ПО ПОРУЧЕНИЕ ООО LAQUO LAQUO RAQUO RAQUO 3 ОБРАБОТКА НА БУМАЖНЫЙ НОСИТЕЛЬ В ИНФОРМАЦИОННЫЙ СИСТЕМА ПЕРСОНАЛЬНЫЙ ДАННЫЙ И БЕЗ ИСПОЛЬЗОВАНИЕ СРЕДСТВО АВТОМАТИЗАЦИЯ А ТАКЖЕ СМЕШАННЫЙ СПОСОБ ООО LAQUO LAQUO RAQUO RAQUO МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ ДОЛЖНЫЙ ОСУЩЕСТВЛЯТЬСЯ В СООТВЕТСТВИЕ С ТРЕБОВАНИЕ ФЕДЕРАЛЬНЫЙ ЗАКОН ОТ 27 07 2006Г ?? 152-ФЗ LAQUO О ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO LAQUO ПОЛИТИКА ЗАЩИТА И ОБРАБОТКА ПЕРСОНАЛЬНЫЙ ДАННЫЙ RAQUO ООО LAQUO LAQUO RAQUO RAQUO 4 ТАКЖЕ ДАЯ СВОЕ СОГЛАСИЕ НА ПРЕДОСТАВЛЕНИЕ ООО LAQUO LAQUO RAQUO RAQUO МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ КАК ПОСЕТИТЕЛЬ САЙТ АГЕНТСТВО С КОТОРЫЙ ОН СОТРУДНИЧАТЬ ООО LAQUO LAQUO RAQUO RAQUO ВПРАВЕ ОСУЩЕСТВЛЯТЬ ОБРАБОТКА МОЙ ПЕРСОНАЛЬНЫЙ ДАННЫЙ СЛЕДУЮЩИЙ СПОСОБ СБОР ЗАПИСЬ СИСТЕМАТИЗАЦИЯ НАКОПЛЕНИЕ ХРАНЕНИЕ ОБНОВЛЕНИЕ ИЗМЕНЕНИЕ ИСПОЛЬЗОВАНИЕ ПЕРЕДАЧА РАСПРОСТРАНЕНИЕ ПРЕДОСТАВЛЕНИЕ ДОСТУП 5 НАСТОЯЩИЙ СОГЛАСИЕ В ТОМ ЧИСЛО НА ПЕРЕДАЧА ПЕРСОНАЛЬНЫЙ ДАННЫЙ ТРЕТИЙ СТОРОНА В СООТВЕТСТВИЕ С П 4 НАСТОЯЩИЙ СОГЛАСИЕ ВСТУПАТЬ В СИЛА С МОМЕНТ МОЙ ПЕРЕХОД НА САЙТ ООО LAQUO LAQUO RAQUO RAQUO И ДЕЙСТВОВАТЬ ДО ДЕНЬ ОТЗЫВ В ПИСЬМЕННЫЙ ФОРМА 6 НАСТОЯЩИЙ СОГЛАСИЕ МОЖЕТ БЫТЬ ОТОЗВАТЬ ПИСЬМЕННЫЙ ЗАЯВЛЕНИЕ НА E-MAIL', 0, 0);
HTML;
    }
    
    $sql_cat = "
      CREATE TABLE IF NOT EXISTS `$table_cat` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) DEFAULT '0',
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `link` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_cat_insert = "
      INSERT INTO `$table_cat` (`id`, `parent_id`, `title`, `img`, `link`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `hide`, `ord`) VALUES  ";
    {
    $sql_cat_insert .=<<<HTML
(1, 0, 'Главное меню', '', '', '', '', '', '', '', '', '', '', 'ГЛАВНОЕ МЕНЮ', '', 0, 0),
(2, 1, 'Главная', '', '/', '', '', '', '', '', '', '', '', 'ГЛАВНЫЙ', '', 0, 0),
(3, 1, 'О компании', '', '', '', '<h3>Пример текста &laquo;О компании&raquo;, как его написать &ndash; &laquo;пикап&raquo; потенциального клиента</h3>\r\n\r\n<p>Вы на рынке с 2002 года? Мне всё равно. &laquo;АвтоВАЗ&raquo; на рынке дольше вас&hellip; У вас хорошая динамика развития и молодой, дружный коллектив? Отлично, то есть, опыта у сотрудников маловато&hellip; Закупили дорогое немецкое оборудование (в кредит?), когда рубль балансировал на ободке унитаза? Значит, теперь мне за это расплачиваться? Прощайте!</p>\r\n\r\n<p>Как написать текст на страницу &laquo;О компании&raquo; и выбросить из словосочетания &laquo;потенциальный клиент&raquo; первое слово? Сделать это в среде &laquo;неправильных&raquo; клиентов. Ведь они, как девушка, которую постоянно атакуют пикаперы: интерес проявляет, но в машину не садится.</p>\r\n\r\n<p>Проблема в том, что у вас все как у всех: низкие цены, надежное оборудование и специалисты &ndash; профессионалы своего дела, у которых клиентоориенитрованность на нуле.</p>\r\n\r\n<p>Если не знаете, как написать текст о компании для сайта, и нужны примеры, то эта работа для <a href="//in-ri.ru" target="_blank">нас</a>.</p>\r\n', '', '', '', '', '', '', 'О КОМПАНИЯ', 'ПРИМЕР ТЕКСТ LAQUO О КОМПАНИЯ RAQUO КАК ЕГО НАПИСАТЬ NDASH LAQUO ПИКАП RAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ ВЫ НА РЫНОК С 2002 ГОД Я ВЕСЬ РАВНО LAQUO АВТОВАЗ RAQUO НА РЫНОК ДОЛГИЙ ВЫ HELLIP У ВЫ ХОРОШИЙ ДИНАМИК РАЗВИТИЕ И МОЛОДАЯ ДРУЖНЫЙ КОЛЛЕКТИВ ОТЛИЧНО ТО ЕСТЬ ОПЫТ У СОТРУДНИК МАЛОВАТЫЙ HELLIP ЗАКУПИТЬ ДОРОГОЙ НЕМЕЦКИЙ ОБОРУДОВАНИЕ В КРЕДИТ КОГДА РУБЛЬ БАЛАНСИРОВАТЬ НА ОБОДОК УНИТАЗ ЗНАЧИТ ТЕПЕРЬ Я ЗА ЭТО РАСПЛАЧИВАТЬСЯ ПРОЩАТЬ КАК НАПИСАТЬ ТЕКСТ НА СТРАНИЦА LAQUO О КОМПАНИЯ RAQUO И ВЫБРОСИТЬ ИЗ СЛОВОСОЧЕТАНИЕ LAQUO ПОТЕНЦИАЛЬНЫЙ КЛИЕНТ RAQUO ПЕРВЫЙ СЛОВО СДЕЛАТЬ ЭТО В СРЕДА LAQUO НЕПРАВИЛЬНЫЙ RAQUO КЛИЕНТ ВЕДЬ ОНИ КАК ДЕВУШКА КОТОРЫЙ ПОСТОЯННО АТАКОВАТЬ ПИКАПЕР ИНТЕРЕС ПРОЯВЛЯТЬ НО В МАШИН НЕ САДИТЬСЯ ПРОБЛЕМА В ТОМ ЧТО У ВЫ ВСЕ КАК У ВЕСЬ НИЗКИЙ ЦЕНА НАДЕЖНЫЙ ОБОРУДОВАНИЕ И СПЕЦИАЛИСТ NDASH ПРОФЕССИОНАЛ СВОЕ ДЕТЬ У КОТОРЫЙ КЛИЕНТООРИЕНИТРОВАННОСТЬ НА НУЛЬ ЕСЛИ НЕ ЗНАТЬ КАК НАПИСАТЬ ТЕКСТ О КОМПАНИЯ ДЛЯ САЙТ И НУЖНЫЙ ПРИМЕР ТО ЭТОТ РАБОТА ДЛЯ МЫ', 0, 4),
(4, 1, 'Контакты', '', '', '', '<p><b>Директор: Ощепков Илья Александрович</b></p>\r\n\r\n<p><b>E-mail</b>: <a href="mailto:1@in-ri.ru">1@in-ri.ru</a></p>\r\n\r\n<p><b>Телефоны в Екатеринбурге:</b></p>\r\n\r\n<p><b>Сот. <a href="tel:+79058010809">+7 (905) 801-08-09</a></b></p>\r\n\r\n<p><b>Адрес:</b> 620100, г. Екатеринбург, ул Сибирский тракт 12/2, офис 404</p>\r\n\r\n<p><b>Время работы:</b> пн-пт 10:00-19:00, сб-вс 11:00-18:00<br />\r\n&nbsp;</p>\r\n<script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Abbe514650299016758f03759051467d1a8adc3faf2e49e2556ec55580a030390&amp;width=100%25&amp;height=500&amp;lang=ru_RU&amp;scroll=false"></script>\r\n\r\n<p>&nbsp;</p>\r\n', '', '', '', '', '', '', 'КОНТАКТ', 'ДИРЕКТОР ОЩЕПОК ИЛЬЯ АЛЕКСАНДР E-MAIL 1 IN-RI RU ТЕЛЕФОН В ЕКАТЕРИНБУРГ СОТЫ 7 905 801-08-09 АДРЕС 620100 Г ЕКАТЕРИНБУРГ УТЬ СИБИРСКИЙ ТРАКТ 12 2 ОФИС 404 ВРЕМЯ РАБОТА ПН-ПТ 10 00-19 00 СБ-ВС 11 00-18 00 NBSP NBSP', 0, 5),
(5, 6, 'Статьи', '', '', '', '', '', '', '', '', '', '', 'СТАТЬЯ', '', 0, 0),
(6, 1, 'Контент', '', '', '', '', '', '', '', '', '', '', 'КОНТЕНТ', '', 0, 2),
(7, 6, 'Фотогалерея', '', '', '', '', '', '', '', '', '', '', 'ФОТОГАЛЕРЕЯ', '', 0, 1),
(8, 6, 'Документы', '', '', '', '', '', '', '', '', '', '', 'ДОКУМЕНТ', '', 0, 0),
(9, 1, 'Новости', '', '/news', '', '', '', '', '', '', '', '', 'НОВОСТЬ', '', 0, 3),
(10, 1, 'Каталог товаров', '', '/katalog-tovarov', '', '', '', '', '', '', '', '', 'КАТАЛОГ ТОВАР', '', 0, 1);
HTML;
    }
    $output = '';
    
    $this->create_cat_img_dir( $name );
    
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_ONLINESHOP );
    
    $output .= $this->setup_database_table($title_cat, $table_cat, $sql_cat, $sql_cat_insert, $script_name  );
    $output .= $this->copy_img_module_cat( $name, SOURCE_SITE_ONLINESHOP );
    
    return $output;
  }
  
  function insert_def_module_all_images_onlineshop( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(60, '1531733498_0.jpg', 'inri_articles_cat', 7, '1533298966_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 1, 0),
(61, '1531733498_1.jpg', 'inri_articles_cat', 7, '1533298966_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 2, 0),
(62, '1531733498_2.jpg', 'inri_articles_cat', 7, '1533298966_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 3, 0),
(63, '1531733498_3.jpg', 'inri_articles_cat', 7, '1533298966_3.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 4, 0),
(64, '1531733498_4.jpg', 'inri_articles_cat', 7, '1533298966_4.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 5, 0),
(65, '1531733498_5.jpg', 'inri_articles_cat', 7, '1533298966_5.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 6, 0),
(66, '1531733498_6.jpg', 'inri_articles_cat', 7, '1533298966_6.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 7, 0),
(67, '1531733498_7.jpg', 'inri_articles_cat', 7, '1533298966_7.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 8, 0),
(68, '1531733498_8.jpg', 'inri_articles_cat', 7, '1533298966_8.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 9, 0),
(69, '1531733498_9.jpg', 'inri_articles_cat', 7, '1533298966_9.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 10, 0),
(70, '1531733498_10.jpg', 'inri_articles_cat', 7, '1533298966_10.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 11, 0),
(71, '1531733498_11.jpg', 'inri_articles_cat', 7, '1533298966_11.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 12, 0),
(72, '1531733498_12.jpg', 'inri_articles_cat', 7, '1533298966_12.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 13, 0),
(NULL, '', 'inri_goods', 1, '1554890879_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 5),
(NULL, '', 'inri_goods', 1, '1554890881_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 4),
(NULL, '', 'inri_goods', 1, '1554890882_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 3),
(NULL, '', 'inri_goods', 1, '1554890883_3.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 2),
(NULL, '', 'inri_goods', 1, '1554890883_4.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 1),
(NULL, '', 'inri_goods', 1, '1554890883_5.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(NULL, '', 'inri_goods', 2, '1554894802_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 2),
(NULL, '', 'inri_goods', 3, '1554895247_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 1),
(NULL, '', 'inri_goods', 2, '1554894804_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 1),
(NULL, '', 'inri_goods', 2, '1554894804_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(NULL, '', 'inri_goods', 3, '1554895247_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(NULL, '', 'inri_goods', 4, '1554896224_0.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 1),
(NULL, '', 'inri_goods', 4, '1554896224_1.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(NULL, '', 'inri_goods', 5, '1554896647_0.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 1),
(NULL, '', 'inri_goods', 5, '1554896647_1.png', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 0),
(NULL, '', 'inri_goods', 5, '1554896837_0.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 6),
(NULL, '', 'inri_goods', 5, '1554896837_1.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 5),
(NULL, '', 'inri_goods', 5, '1554896837_2.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 4),
(NULL, '', 'inri_goods', 5, '1554896837_3.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 3),
(NULL, '', 'inri_goods', 5, '1554896837_4.jpg', NULL, NULL, NULL, NULL, NULL, '', '', 0, 0, 2);
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_ONLINESHOP );
    
    return $output;
  }
  
  function insert_def_module_all_files_onlineshop( $title, $name ){
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `module`, `module_id`, `img`, `file`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`, `module_ord`) VALUES ";
    $sql_insert .=<<<HTML
(6, '1972.pdf', 'inri_articles_cat', 8, '', '1533299057_0.pdf', NULL, NULL, NULL, 0, 0, 0),
(7, '1973.pdf', 'inri_articles_cat', 8, '', '1533299057_1.pdf', NULL, NULL, NULL, 0, 0, 0),
(8, '1974.pdf', 'inri_articles_cat', 8, '', '1533299057_2.pdf', NULL, NULL, NULL, 0, 0, 0),
(9, '1975.pdf', 'inri_articles_cat', 8, '', '1533299057_3.pdf', NULL, NULL, NULL, 0, 0, 0),
(10, '1976.pdf', 'inri_articles_cat', 8, '', '1533299057_4.pdf', NULL, NULL, NULL, 0, 0, 0),
(11, '1977.pdf', 'inri_articles_cat', 8, '', '1533299057_5.pdf', NULL, NULL, NULL, 0, 0, 0),
(12, '1978.pdf', 'inri_articles_cat', 8, '', '1533299057_6.pdf', NULL, NULL, NULL, 0, 0, 0),
(13, '1979.pdf', 'inri_articles_cat', 8, '', '1533299057_7.pdf', NULL, NULL, NULL, 0, 0, 0),
(14, '1980.pdf', 'inri_articles_cat', 8, '', '1533299057_8.pdf', NULL, NULL, NULL, 0, 0, 0),
(15, '1981.pdf', 'inri_articles_cat', 8, '', '1533299057_9.pdf', NULL, NULL, NULL, 0, 0, 0),
(16, '1982.pdf', 'inri_articles_cat', 8, '', '1533299057_10.pdf', NULL, NULL, NULL, 0, 0, 0),
(17, '1983.pdf', 'inri_articles_cat', 8, '', '1533299057_11.pdf', NULL, NULL, NULL, 0, 0, 0),
(18, '1984.pdf', 'inri_articles_cat', 8, '', '1533299057_12.pdf', NULL, NULL, NULL, 0, 0, 0),
(19, '1985.pdf', 'inri_articles_cat', 8, '', '1533299057_13.pdf', NULL, NULL, NULL, 0, 0, 0),
(20, '1986.pdf', 'inri_articles_cat', 8, '', '1533299057_14.pdf', NULL, NULL, NULL, 0, 0, 0),
(21, '1987.pdf', 'inri_articles_cat', 8, '', '1533299057_15.pdf', NULL, NULL, NULL, 0, 0, 0),
(22, '1988.pdf', 'inri_articles_cat', 8, '', '1533299057_16.pdf', NULL, NULL, NULL, 0, 0, 0),
(23, '1989.pdf', 'inri_articles_cat', 8, '', '1533299057_17.pdf', NULL, NULL, NULL, 0, 0, 0),
(24, '1990.pdf', 'inri_articles_cat', 8, '', '1533299057_18.pdf', NULL, NULL, NULL, 0, 0, 0),
(25, '1991.pdf', 'inri_articles_cat', 8, '', '1533299057_19.pdf', NULL, NULL, NULL, 0, 0, 0),
(26, 'Руководство пользователя Ninja ZX-10RR', 'inri_goods', 3, '', '1554895482_0.pdf', NULL, NULL, NULL, 0, 0, 0),
(27, 'Руководство пользователя Z900RS', 'inri_goods', 4, '', '1554896241_0.pdf', NULL, NULL, NULL, 0, 0, 0);
HTML;
    
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    $output .= $this->copy_img_module( $name, SOURCE_SITE_ONLINESHOP );
    $output .= $this->copy_file_module( $name, SOURCE_SITE_ONLINESHOP );
    
    return $output;
  }
  
  function insert_def_module_seo_onlineshop( $title, $name ){
    
    $output = '';
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `type`, `view`, `ord`, `hide`, `value`, `comment`) VALUES ";
    $sql_insert .=<<<HTML
      (1, 'Title главой', 'mine_title', NULL, 10, 0, 'Интернет магазин', NULL),
      (2, 'Description Главной', 'mine_description', 1, 20, 0, 'Интернет магазин', NULL),
      (3, 'Keywords главной', 'mine_keywords', NULL, 21, 0, '', NULL),
      (4, 'Title текстовой статьи', 'lib_text_title', NULL, 110, 0, '*h1* ', NULL),
      (5, 'Title текстового раздела', 'lib_cat_title', NULL, 100, 0, '*h1*', NULL),
      (6, 'Title новости', 'news_title', NULL, 90, 0, '*h1*', NULL),
      (7, 'Title каталога товаров', 'goods_cat_title', NULL, 100, 0, '*h1*', NULL),
      (8, 'Title товара', 'goods_title', NULL, 110, 0, '*h1*', NULL),
      (9, 'Alt изображений товара', 'img_alt', NULL, 130, 0, '*h1*', NULL);
HTML;
    $output .= $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name ); 
    
    return $output;
  }
  
  function insert_def_module_url_onlineshop( $title, $name ){
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `url`, `module`, `module_id`, `hide`, `ord`) VALUES ";
    $sql_insert .=<<<HTML
      (8, 'Блок 1', 'blok', 'inri_mine_block', 2, 0, 6),
      (9, 'Блок 2', 'blok-74548', 'inri_mine_block', 3, 0, 7),
      (10, 'Блок 3', 'blok-86291', 'inri_mine_block', 4, 0, 8),
      (11, 'Слайдер на главной', 'slayder-na-glavnoy', 'inri_mine_block', 5, 0, 9),
      (13, 'Шапка сайта', 'shapka-sayta', 'inri_mine_block', 6, 0, 11),
      (14, 'Главное меню', 'glavnoe-menyu', 'inri_mine_block', 7, 0, 12),
      (15, 'Контент на внутренних страницах', 'kontent-na-vnutrennih-stranicah', 'inri_mine_block', 8, 0, 13),
      (16, 'robots.txt', 'robots_txt', 'robots_txt', 0, 0, 14),
      (17, 'Главное меню', 'glavnoe-menyu-65489', 'inri_articles_cat', 1, 0, 15),
      (18, 'Главная', 'glavnaya', 'inri_articles_cat', 2, 0, 16),
      (19, 'О компании', 'kompanii', 'inri_articles_cat', 3, 0, 17),
      (20, 'Контакты', 'kontakty', 'inri_articles_cat', 4, 0, 18),
      (21, 'Статьи', 'stati', 'inri_articles_cat', 5, 0, 19),
      (22, 'Основатель диджитал-студии Finch — о веб-дизайне в России и США', 'osnovatel-didzhitalstudii-finch-o', 'inri_articles', 1, 0, 20),
      (23, 'Киану Ривз: «Россия ассоциируется у меня с моральной силой»', 'kianu-rivz-rossiya-associiruetsya', 'inri_articles', 2, 0, 21),
      (24, 'От бритвы до автомобиля: почему мир переходит к сервисам по подписке', 'britvy-do-avtomobilya-pochemu-mir', 'inri_articles', 3, 0, 22),
      (25, 'Контент', 'kontent', 'inri_articles_cat', 6, 0, 23),
      (26, 'Фотогалерея', 'fotogalereya', 'inri_articles_cat', 7, 0, 24),
      (27, 'Документы', 'dokumenty', 'inri_articles_cat', 8, 0, 25),
      (28, 'Новости', 'news', 'inri_news', 0, 0, 26), 
      (30, 'Новости', 'novosti', 'inri_articles_cat', 9, 0, 28),
      (31, 'Петросян отреагировал на сообщения о разделе имущества со Степаненко', 'petrosyan-otreagiroval-na-soobscheniya-o', 'inri_news', 1, 0, 29),
      (32, 'Ученые назвали причину исчезновения кораблей в Бермудском треугольнике', 'uchenye-nazvali-prichinu-ischeznoveniya', 'inri_news', 2, 0, 30),
      (33, 'Марафон «Европа-Азия» соберёт более шести тысяч участников', 'marafon-evropaaziya-soberyot-bolee', 'inri_news', 3, 0, 31),
      
      (NULL, 'Каталог товаров', 'katalog-tovarov', 'inri_goods_cat', 1, 0, 0),
      (NULL, 'Мотоциклы', 'motocikly', 'inri_goods_cat', 3, 0, 0),
      (NULL, 'Каталог товаров', 'katalog-tovarov-91466', 'inri_articles_cat', 10, 0, 0),
      (NULL, 'Корзина товаров', 'basket', 'basket', 0, 0, 0),
      (NULL, 'xlsorder', 'xlsorder', 'xlsorder', 0, 0, 0),
      (NULL, 'Поиск по сайту', 'poisk-po-saytu', 'inri_mine_block', 9, 0, 0),
      (NULL, 'Поиск', 'search', 'search', 0, 0, 0),
      (NULL, 'Политика организации по обработке персональных данных', 'politikoy-organizacii-po-obrabotke-personalnyh', 'inri_articles', 4, 0, 0),
      (NULL, 'Согласие посетителя сайта на обработку персональных данных', 'soglasie-posetitelya-sayta', 'inri_articles', 5, 0, 0),
      (NULL, 'Дорожные', 'dorozhnye', 'inri_goods_cat', 5, 0, 0),
      (NULL, 'Супер спорт', 'sport', 'inri_goods_cat', 4, 0, 0),
      (NULL, 'Товары на главной', 'glavnoy', 'inri_mine_block', 10, 0, 0),
      (NULL, 'Спортивно-туристические', 'sportivnoturisticheskie', 'inri_goods_cat', 6, 0, 0),
      (NULL, 'Круизеры', 'kruizery', 'inri_goods_cat', 7, 0, 0),
      (NULL, 'Универсальные', 'universalnye', 'inri_goods_cat', 8, 0, 0),
      (NULL, 'Кросс / эндуро', 'enduro', 'inri_goods_cat', 9, 0, 0),
      (NULL, 'Квадроциклы', 'kvadrocikly', 'inri_goods_cat', 10, 0, 0),
      (NULL, 'Гидроциклы', 'gidrocikly', 'inri_goods_cat', 11, 0, 0),
      (NULL, 'Экипировка', 'ekipirovka', 'inri_goods_cat', 12, 0, 0),
      (NULL, 'Аксессуары', 'aksessuary', 'inri_goods_cat', 13, 0, 0),
      (NULL, 'Запчасти', 'zapchasti', 'inri_goods_cat', 14, 0, 0),
      (NULL, 'Kawasaki Ninja ZX-10R SE Черный 2018', 'kawasaki-ninja-zx10r-se-chernyy-2018', 'inri_goods', 1, 0, 0),
      (NULL, 'Kawasaki Ninja H2R Серый 2019', 'kawasaki-ninja-h2r-seryy-2019', 'inri_goods', 2, 0, 0),
      (NULL, 'Kawasaki Ninja ZX-10RR Зелёный 2019', 'kawasaki-ninja-zx10rr-zelyonyy-2019', 'inri_goods', 3, 0, 0),
      (NULL, 'Kawasaki Z900RS Коричневый 2019', 'kawasaki-z900rs-korichnevyy-2019', 'inri_goods', 4, 0, 0),
      (NULL, 'Kawasaki Z1000 R Edition Черный 2019', 'kawasaki-z1000-r-edition-chernyy-2019', 'inri_goods', 5, 0, 0),
      (NULL, 'Каталог товаров', 'katalog-tovarov-70606', 'inri_cat_goods', 1, 0, 0);
HTML;
        
    return $this->sql_def_insert_database_table( $title, $table, $sql_insert, $script_name );
  }
  
  function setup_module_availability_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `hide`, `ord`) VALUES 
        (1, 'в наличии', '', 0, 0),
        (2, 'в наличии на складе', '', 0, 1),
        (3, 'временно отсутствует', '', 0, 2),
        (4, 'под заказ (3 дня)', '', 0, 4),
        (5, 'под заказ (7-14 дней)', '', 0, 5),
        (6, 'под заказ', '', 0, 3),
        (7, 'узнавайте у менеджеров', '', 0, 6) "; 
    
    return $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
  }
  
  function setup_module_country_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `longtxt1` text,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES 
        (1, 'Россия', '', '', '', '', 0, 0) "; 
    
    $output .= $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_brand_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `link` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `link`, `longtxt1`, `longtxt2`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `hide`, `ord`) VALUES 
        (1, 'Apple', '', '', '', '', '', '', '', '', '', '', 0, 0) ";  
    
    $output .= $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_units_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `reduction` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `reduction`, `longtxt1`, `img_alt`, `img_title`, `hide`, `ord`) VALUES 
        (1, 'Штуки', '', 'шт.', '', '', '', 0, 0) ";  
    
    $output .= $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    #$output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_search_log_onlineshop( $title, $name ){
    
    $table = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `datetime` varchar(10) DEFAULT NULL,
        `ip` varchar(255) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
      
    $sql_insert = "
      INSERT INTO `$table` (`id`, `title`, `img`, `datetime`, `ip`, `longtxt1`, `longtxt2`, `hide`, `ord`) VALUES 
        (1, 'Z0UC0009M', '', '2018-08-14', '176.215.11.77', NULL, NULL, 0, 0) ";  
    
    #$output .= $this->create_img_dir( $name );
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    #$output .= $this->copy_img_module( $name, SOURCE_SITE_CUTAWAY );
    
    return $output;
  }
  
  function setup_module_goods_onlineshop( $title, $name ){
    
    $table       = DB_PFX.$name;
    $script_name = $name.'.php';
    
    $title_cat   = $title.' категории';
    $table_cat   = DB_PFX.$name.'_cat';
    
    $sql = "
      CREATE TABLE IF NOT EXISTS `$table` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cat_id` int(11) DEFAULT '0',
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `article` varchar(255) NOT NULL,
        `article_provider` varchar(255) NOT NULL,
        `old_price` int(11) DEFAULT NULL,
        `price` int(11) DEFAULT NULL,
        `amount` int(11) DEFAULT NULL,
        `availability_id` int(11) DEFAULT '1',
        `country_id` int(11) DEFAULT NULL,
        `brand_id` int(11) DEFAULT NULL,
        `units_id` int(11) DEFAULT '1',
        `longtxt1` text,
        `longtxt2` text,
        `longtxt3` text,
        `is_hit` tinyint(1) DEFAULT NULL,
        `is_new` tinyint(1) DEFAULT NULL,
        `is_sale` tinyint(1) DEFAULT NULL,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `fl_show_mine` tinyint(1) NOT NULL DEFAULT '0',
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; "; 
    $sql_insert = "
      INSERT INTO `$table` (`id`, `cat_id`, `title`, `img`, `article`, `article_provider`, `old_price`, `price`, `amount`, `availability_id`, `country_id`, `brand_id`, `units_id`, `longtxt1`, `longtxt2`, `longtxt3`, `is_hit`, `is_new`, `is_sale`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `fl_show_mine`, `hide`, `ord`) VALUES  ";
    {
    $sql_insert .=<<<HTML
(1, 4, 'Kawasaki Ninja ZX-10R SE Черный 2018', '1554890844.png', '', '', 0, 1694000, 1, 1, 0, 0, 1, '<p><strong>Стань ближе</strong></p>\r\n\r\n<p>В дополнение к оригинальному Ninja ZX-10R и трек-ориентированному Ninja ZX-10RR, Кавасаки с гордостью<br />\r\nпредставляет новый высококачественный мотоцикл в линейку супер-спортов литрового класса на 2018 год.<br />\r\nNinja ZX-10R SE предлагает новую высокотехнологичную электронную подвеску Kawasaki.<br />\r\nКак в городе, так и на трассе превосходное демпфирование подвески,<br />\r\nпредлагает райдеру новый уровень комфорта езды, который почти заставляет вас забыть,<br />\r\nчто вы едете на мотоцикле с гоночной подвеской. Тогда как<br />\r\nотличная реакция на демпфирование, обеспечивает непревзайденное ощущение посадки.<br />\r\nЭто обеспечивает уверенность гонщику и<br />\r\nспособствует легкости езды, предлагаемой Ninja ZX-10R SE.</p>\r\n', '<table class="table table-striped table-hover table-sm">\r\n	<tbody>\r\n		<tr>\r\n			<th colspan="2">Двигатель</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип двигателя</td>\r\n			<td>Рядный</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество цилиндров</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем двигателя <i class="fas fa-question-circle" data-original-title="\r\n            Рабочий объем двигателя мотоцикла, измеряемый в куб. см.\r\n			В зависимости от модели, объем цилиндров двигателя мотоцикла и, как следствие, его мощность могут существенно различаться.\r\n			От объема двигателя зависит также расход топлива. Не рекомендуется приобретать мотоцикл с двигателем слишком малого объема, т. к. его мощности и рабочего ресурса может оказаться недостаточно. В этом случае велика вероятность преждевременного выхода мотора из строя." data-placement="bottom" data-toggle="tooltip"><b>&nbsp;</b></i></td>\r\n			<td>998</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Лошадиные силы</td>\r\n			<td>200</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество тактов <i class="fas fa-question-circle" data-original-title="\r\n            Двигатели бывают двухтактными и четырехтактными. \r\n			Двухтактный двигатель имеет простую и недорогую конструкцию, однако для его эксплуатации необходимы специальные масло и топливо. Допустимо также использование обычного 92-ого или 95-ого бензина, смешанного с маслом. Однако износ деталей при этом может возрасти, т. к. угадать нужные пропорции при разведении топлива маслом достаточно сложно. Из-за некачественного топлива двигатель работает неустойчиво, повышается его износ и может скапливаться нагар.\r\n            Четырехтактный двигатель имеет более сложную конструкцию, и, следовательно, его производство обходится дороже. К его достоинствам можно отнести экономичность, менее ощутимые, чем у двухтактного двигателя, шум и вибрацию, более чистый выхлоп, а также отсутствие необходимости постоянного контроля уровня масла." data-placement="bottom" data-toggle="tooltip"><b>&nbsp;</b></i></td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальная мощность <i class="fas fa-question-circle" data-original-title="\r\n            Мощность двигателя мотоцикла, измеряемая в лошадиных силах и кВТ.\r\n            В зависимости от модели, объем цилиндров двигателя мотоцикла и, как следствие, его мощность могут существенно различаться.\r\n			От мощности двигателя зависит скорость мотоцикла.\r\n			Рекомендуется приобретать модель с запасом мощности, т. к. это обеспечит запас механической прочности и надежности." data-placement="bottom" data-toggle="tooltip"><b>&nbsp;</b></i></td>\r\n			<td>147,1 кВт {200 л.с.} / 13.000 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальный крутящий момент</td>\r\n			<td>113,5 Нм {11,6кгс&bull;м} / 11.500 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Система запуска</td>\r\n			<td>Электростартер</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип охлаждения <i class="fas fa-question-circle" data-original-title="\r\n           Существует два типа охлаждения двигателя мотоцикла: воздушное и жидкостное.\r\n			При воздушном типе охлаждения тепло отводится от двигателя посредством воздушного обдува.\r\n			Мотоциклы с воздушным охлаждением имеют на стенках двигателя характерные радиаторные ребра. Воздушное охлаждение может быть естественным или принудительным. Разница заключается в том, что при принудительном воздушном охлаждении на мотоцикл устанавливается вентилятор, который обдувает двигатель в случае, когда естественного обдува недостаточно, например в пробке. Однако это существенно повышает уровень шума мотоцикла. При естественном охлаждении мотоцикл легко может перегреться при простое. В таких ситуациях приходится глушить мотор и ждать, пока он остынет.\r\n			Жидкостное охлаждение имеет более сложную конструкцию: цилиндры омываются жидкостью, прокачиваемой насосом по контуру системы охлаждения, затем горячая жидкость попадает в радиатор, где охлаждается при помощи вентилятора. Такая конструкция значительно надежнее и стабильнее охлаждает двигатель, однако имеет существенно более высокую стоимость." data-placement="bottom" data-toggle="tooltip"><b>&nbsp;</b></i></td>\r\n			<td>Жидкостное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Трансмиссия</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Трансмиссия</td>\r\n			<td>6</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Сцепление</td>\r\n			<td>Мокрое многодисковое, ручное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Общие</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Код техники</td>\r\n			<td>ZX1002C</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Модельный год</td>\r\n			<td>2018</td>\r\n		</tr>\r\n		<tr>\r\n			<td>ABS</td>\r\n			<td>ABS + KTRC</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем бака</td>\r\n			<td>17</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Длина</td>\r\n			<td>2085</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Ширина</td>\r\n			<td>740</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Высота</td>\r\n			<td>1145</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Снаряженная масса</td>\r\n			<td>208</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;"><iframe allowfullscreen="" frameborder="0" height="360" src="//www.youtube.com/embed/MSsNPj5tTE0" style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="640"></iframe></div>\r\n\r\n<p>&nbsp;</p>\r\n', '', 0, 0, 0, '', '', '', '', '', '', 'KAWASAKI NINJA ZX-10R SE ЧЕРНЫЙ 2018', 'ДВИГАТЕЛЬ ТИП ДВИГАТЕЛЬ РЯДНЫЙ КОЛИЧЕСТВО ЦИЛИНДР 4 ОБЪЕСТЬ ДВИГАТЕЛЬ NBSP 998 ЛОШАДИНЫЙ СИЛА 200 КОЛИЧЕСТВО ТАКТОВЫЙ NBSP 4 МАКСИМАЛЬНЫЙ МОЩНОСТЬ NBSP 147 1 КВТ 200 Л С 13 000 ОБ МИНА МАКСИМАЛЬНЫЙ КРУТИТЬ МОМЕНТ 113 5 НМ 11 6КГС BULL М 11 500 ОБ МИНА СИСТЕМА ЗАПУСК ЭЛЕКТРОСТАРТЕР ТИП ОХЛАЖДЕНИЕ NBSP ЖИДКОСТНЫЙ ТРАНСМИССИЯ ТРАНСМИССИЯ 6 СЦЕПЛЕНИЕ МОКРЫЙ МНОГОДИСКОВЫЙ РУЧНОЙ ОБЩИЙ КОД ТЕХНИК ZX1002C МОДЕЛЬНЫЙ ГОД 2018 ABS ABS KTRC ОБЪЕСТЬ БАК 17 ДЛИНА 2085 ШИРИНА 740 ВЫСОТА 1145 СНАРЯДИТЬ МАССА 208 NBSP NBSP', 1, 0, 0),

(3, 4, 'Kawasaki Ninja ZX-10RR Зелёный 2019', '1554895272.jpg', '', '', 0, 2083000, 0, 1, 0, 0, 1, '<p><strong>СТАНЬ БЛИЖЕ</strong></p>\r\n\r\n<p>Это - единственный достойный соперник. Оборудованный легковесными литыми дисками MARCHESINI и<br />\r\nтормозами Brembo M50, новый Ninja ZX-10RR не берёт пленных. Стань ближе и испытай<br />\r\nмодифицированные головку блока цилиндров и картера, а также новый квикшифтер. В<br />\r\n2019 году по всему миру будет продано только 500 мотоциклов, оснащенных<br />\r\nтитановыми шатунами, благодаря которым пиковая мощность увеличена до<br />\r\n204 л.с. Трек призывает к победам - и новый Ninja ZX-10RR готов<br />\r\nответить на зов!</p>\r\n\r\n<p>Для данной модели так же доступна комплектация Performance.&nbsp;</p>\r\n\r\n<p>&quot;Performance&quot; включает в себя:&nbsp;<br />\r\nКарбоновые глушитель Akrapovic</p>\r\n\r\n<p>Наличие и цены уточняйте у наших менеджеров!</p>\r\n', '<table class="table table-striped table-hover table-sm">\r\n	<tbody>\r\n		<tr>\r\n			<th colspan="2">Двигатель</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип двигателя</td>\r\n			<td>Рядный</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество цилиндров</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем двигателя</td>\r\n			<td>998</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Лошадиные силы</td>\r\n			<td>204</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество тактов</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальная мощность</td>\r\n			<td>150 кВт {204 л.с.} / 13.500 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальный крутящий момент</td>\r\n			<td>115,7 Нм {11,7 кгс&bull;м} / 11.200 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Система запуска</td>\r\n			<td>Электростартер</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип охлаждения</td>\r\n			<td>Жидкостное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Трансмиссия</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Трансмиссия</td>\r\n			<td>6</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Сцепление</td>\r\n			<td>Мокрое многодисковое, ручное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Общие</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Код техники</td>\r\n			<td>ZX1002G</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Модельный год</td>\r\n			<td>2019</td>\r\n		</tr>\r\n		<tr>\r\n			<td>ABS</td>\r\n			<td>ABS + KTRC</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип рамы</td>\r\n			<td>Диагональная Twin spar, литой алюминий</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем бака</td>\r\n			<td>17</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Длина</td>\r\n			<td>2085</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Ширина</td>\r\n			<td>740</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Высота</td>\r\n			<td>1145</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Снаряженная масса</td>\r\n			<td>206</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;"><iframe allowfullscreen="" frameborder="0" height="360" src="//www.youtube.com/embed/qNC5J3hIUM8" style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="640"></iframe></div>\r\n\r\n<p>&nbsp;</p>\r\n', '', 0, 0, 0, '', '', '', '', '', '', 'KAWASAKI NINJA ZX-10RR ЗЕЛЁНЫЙ 2019', 'ДВИГАТЕЛЬ ТИП ДВИГАТЕЛЬ РЯДНЫЙ КОЛИЧЕСТВО ЦИЛИНДР 4 ОБЪЕСТЬ ДВИГАТЕЛЬ 998 ЛОШАДИНЫЙ СИЛА 204 КОЛИЧЕСТВО ТАКТОВЫЙ 4 МАКСИМАЛЬНЫЙ МОЩНОСТЬ 150 КВТ 204 Л С 13 500 ОБ МИНА МАКСИМАЛЬНЫЙ КРУТИТЬ МОМЕНТ 115 7 НМ 11 7 КГС BULL М 11 200 ОБ МИНА СИСТЕМА ЗАПУСК ЭЛЕКТРОСТАРТЕР ТИП ОХЛАЖДЕНИЕ ЖИДКОСТНЫЙ ТРАНСМИССИЯ ТРАНСМИССИЯ 6 СЦЕПЛЕНИЕ МОКРЫЙ МНОГОДИСКОВЫЙ РУЧНОЙ ОБЩИЙ КОД ТЕХНИК ZX1002G МОДЕЛЬНЫЙ ГОД 2019 ABS ABS KTRC ТИП РАМ ДИАГОНАЛЬНЫЙ TWIN SPAR ЛИТА АЛЮМИНИЙ ОБЪЕСТЬ БАК 17 ДЛИНА 2085 ШИРИНА 740 ВЫСОТА 1145 СНАРЯДИТЬ МАССА 206 NBSP NBSP', 1, 0, 0),

(2, 4, 'Kawasaki Ninja H2R Серый 2019', '1554894806.jpg', '', '', 0, 4416000, 0, 1, 0, 0, 1, '<p><strong>ТЕХНОЛОГИИ ОПЕРЕЖАЮЩИЕ ВРЕМЯ</strong></p>\r\n\r\n<p>Немногие мотоциклы могут прорваться за пределы технических рамок современности, и лишь единицы<br />\r\nимеют право на звание легенды. Созданный при содействии всех подразделений концерна&nbsp;<br />\r\nKawasaki Heavy Industries, Ninja H2R требует уважения и избирателен к владельцам -<br />\r\nобуздать его мощь под силу только избранным. Среди достоинств мотоцикла:<br />\r\nзадняя подвеска Ohlins, двусторонний квикшифтер, аэродинамические<br />\r\nобтекатели, система контроля прохождения поворотов и индикатор<br />\r\nугла наклона. Ninja H2R - займи свое место в истории.<br />\r\nТолько для эксплуатации на треках.</p>\r\n\r\n<p>&nbsp;</p>\r\n', '<table class="table table-striped table-hover table-sm">\r\n	<tbody>\r\n		<tr>\r\n			<th colspan="2">Двигатель</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип двигателя</td>\r\n			<td>Рядный</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество цилиндров</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем двигателя</td>\r\n			<td>998</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Лошадиные силы</td>\r\n			<td>310</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество тактов</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальная мощность</td>\r\n			<td>228 кВт {310 л.с.} / 14.000 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальный крутящий момент</td>\r\n			<td>165 Нм {16,8кгс&bull;м} / 12.500 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Система запуска</td>\r\n			<td>Электро</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип охлаждения</td>\r\n			<td>Жидкостное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Трансмиссия</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Трансмиссия</td>\r\n			<td>6</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Сцепление</td>\r\n			<td>Мокрое многодисковое, ручное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Общие</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Код техники</td>\r\n			<td>ZX1000Y</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Модельный год</td>\r\n			<td>2019</td>\r\n		</tr>\r\n		<tr>\r\n			<td>ABS</td>\r\n			<td>ABS + KTRC</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип рамы</td>\r\n			<td>Типа Trellis, высокопрочная сталь с площадкой крепления маятника</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем бака</td>\r\n			<td>17</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Длина</td>\r\n			<td>2070</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Ширина</td>\r\n			<td>850</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Высота</td>\r\n			<td>1160</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Снаряженная масса</td>\r\n			<td>216</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;"><iframe allowfullscreen="" frameborder="0" height="360" src="//www.youtube.com/embed/nxX6R8oV5Ko" style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="640"></iframe></div>\r\n\r\n<p>&nbsp;</p>\r\n', '', 0, 0, 0, '', '', '', '', '', '', 'KAWASAKI NINJA H2R СЕРЫЙ 2019', 'ДВИГАТЕЛЬ ТИП ДВИГАТЕЛЬ РЯДНЫЙ КОЛИЧЕСТВО ЦИЛИНДР 4 ОБЪЕСТЬ ДВИГАТЕЛЬ 998 ЛОШАДИНЫЙ СИЛА 310 КОЛИЧЕСТВО ТАКТОВЫЙ 4 МАКСИМАЛЬНЫЙ МОЩНОСТЬ 228 КВТ 310 Л С 14 000 ОБ МИНА МАКСИМАЛЬНЫЙ КРУТИТЬ МОМЕНТ 165 НМ 16 8КГС BULL М 12 500 ОБ МИНА СИСТЕМА ЗАПУСК ЭЛЕКТРО ТИП ОХЛАЖДЕНИЕ ЖИДКОСТНЫЙ ТРАНСМИССИЯ ТРАНСМИССИЯ 6 СЦЕПЛЕНИЕ МОКРЫЙ МНОГОДИСКОВЫЙ РУЧНОЙ ОБЩИЙ КОД ТЕХНИК ZX1000Y МОДЕЛЬНЫЙ ГОД 2019 ABS ABS KTRC ТИП РАМ ТИП TRELLIS ВЫСОКОПРОЧНЫЙ СТАЛЬ С ПЛОЩАДКА КРЕПЛЕНИЕ МАЯТНИК ОБЪЕСТЬ БАК 17 ДЛИНА 2070 ШИРИНА 850 ВЫСОТА 1160 СНАРЯДИТЬ МАССА 216 NBSP NBSP', 1, 0, 0),

(4, 5, 'Kawasaki Z900RS Коричневый 2019', '1554896232.png', '', '', 0, 952000, 0, 1, 0, 0, 1, '<p><strong>Истинный Дух</strong></p>\r\n\r\n<p>Здесь нет места для суррогатов и подделок. Только у Kawasaki есть поистине великое наследие, чтобы предоставить ценности прошлого гонщикам настоящего, сохранив истинный облик классического мотоцикла. Дух легенды 1970-х годов, Z1, нашел свое современное воплощение в Z900RS.</p>\r\n\r\n<p>Для данной&nbsp;модели так же доступна&nbsp;комплектация&nbsp;Performance.</p>\r\n\r\n<p>&quot;Performance&quot; включает в себя:<br />\r\nНакладка на топливный бак&nbsp;<br />\r\nСлайдеры двигателя&nbsp;<br />\r\nКарбоновый глушитель Akrapovic</p>\r\n\r\n<p>Наличие и цены уточняйте у наших менеджеров!</p>\r\n', '<table class="table table-striped table-hover table-sm">\r\n	<tbody>\r\n		<tr>\r\n			<th colspan="2">Двигатель</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип двигателя</td>\r\n			<td>Рядный</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество цилиндров</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем двигателя</td>\r\n			<td>948</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Лошадиные силы</td>\r\n			<td>111</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество тактов</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальная мощность</td>\r\n			<td>82 кВт {111 л.с.} / 8.500 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальный крутящий момент</td>\r\n			<td>98,5 Нм {10 кгс&bull;м} / 6.500 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Система запуска</td>\r\n			<td>Электростартер</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип охлаждения</td>\r\n			<td>Жидкостное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Трансмиссия</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Трансмиссия</td>\r\n			<td>6</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Сцепление</td>\r\n			<td>Мокрое многодисковое, ручное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Общие</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Код техники</td>\r\n			<td>ZR900CB</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Модельный год</td>\r\n			<td>2019</td>\r\n		</tr>\r\n		<tr>\r\n			<td>ABS</td>\r\n			<td>ABS + KTRC</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип рамы</td>\r\n			<td>&quot;Птичья клетка&quot; (Trellis), высокопрочная сталь</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем бака</td>\r\n			<td>17</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Длина</td>\r\n			<td>2100</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Ширина</td>\r\n			<td>865</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Высота</td>\r\n			<td>1150</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Снаряженная масса</td>\r\n			<td>215</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;"><iframe allowfullscreen="" frameborder="0" height="360" src="//www.youtube.com/embed/T5E-OuwLYys" style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="640"></iframe></div>\r\n\r\n<p>&nbsp;</p>\r\n', '', 0, 0, 0, '', '', '', '', '', '', 'KAWASAKI Z900RS КОРИЧНЕВЫЙ 2019', 'ДВИГАТЕЛЬ ТИП ДВИГАТЕЛЬ РЯДНЫЙ КОЛИЧЕСТВО ЦИЛИНДР 4 ОБЪЕСТЬ ДВИГАТЕЛЬ 948 ЛОШАДИНЫЙ СИЛА 111 КОЛИЧЕСТВО ТАКТОВЫЙ 4 МАКСИМАЛЬНЫЙ МОЩНОСТЬ 82 КВТ 111 Л С 8 500 ОБ МИНА МАКСИМАЛЬНЫЙ КРУТИТЬ МОМЕНТ 98 5 НМ 10 КГС BULL М 6 500 ОБ МИНА СИСТЕМА ЗАПУСК ЭЛЕКТРОСТАРТЕР ТИП ОХЛАЖДЕНИЕ ЖИДКОСТНЫЙ ТРАНСМИССИЯ ТРАНСМИССИЯ 6 СЦЕПЛЕНИЕ МОКРЫЙ МНОГОДИСКОВЫЙ РУЧНОЙ ОБЩИЙ КОД ТЕХНИК ZR900CB МОДЕЛЬНЫЙ ГОД 2019 ABS ABS KTRC ТИП РАМ QUOT ПТИЧИЙ КЛЕТКА QUOT TRELLIS ВЫСОКОПРОЧНЫЙ СТАЛЬ ОБЪЕСТЬ БАК 17 ДЛИНА 2100 ШИРИНА 865 ВЫСОТА 1150 СНАРЯДИТЬ МАССА 215 NBSP NBSP', 1, 0, 0),

(5, 5, 'Kawasaki Z1000 R Edition Черный 2019', '1554896651.jpg', '', '', 0, 1118000, 0, 1, 0, 0, 1, '<p><strong>REFINED RAW</strong></p>\r\n\r\n<p>Наследник невероятного Sugomi, в 2017 году серию пополнил Z1000 R Edition. Полностью соответствующий стандартам Euro4, Z1000 R Edition имеет яркую цветовую схему с зелеными элементами, а так же тормоза Brembo M50 и задний амортизатор &Ouml;hlins . Покоривший сердца еще до премьеры, Z1000 R Edition придется по душе поклонникам класса &quot;Naked&quot; и любителям прециозной, но агрессивной езды.<br />\r\nТак же данная модель широко известна под названиями: зедка, ZR1000J.&nbsp;</p>\r\n\r\n<p>Для данной модели так же доступна комплектация Performance.&quot;Performance&quot; включает в себя:<br />\r\nКарбоновый тепловой экран глушителя&nbsp;<br />\r\nКарбоновый глушитель&nbsp;Akrapovic</p>\r\n\r\n<p>Наличие и цены уточняйте у наших менеджеров!</p>\r\n', '<table class="table table-striped table-hover table-sm">\r\n	<tbody>\r\n		<tr>\r\n			<th colspan="2">Двигатель</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип двигателя</td>\r\n			<td>Рядный</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество цилиндров</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем двигателя</td>\r\n			<td>1043</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Лошадиные силы</td>\r\n			<td>142</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Количество тактов</td>\r\n			<td>4</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальная мощность</td>\r\n			<td>104,5 кВт {142 л.с.} / 10.000 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Максимальный крутящий момент</td>\r\n			<td>111 Нм {11,3кгс&bull;м} / 7.300 об/мин</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Система запуска</td>\r\n			<td>Электростартер</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип охлаждения</td>\r\n			<td>Жидкостное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Трансмиссия</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Трансмиссия</td>\r\n			<td>6</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Сцепление</td>\r\n			<td>Мокрое многодисковое, ручное</td>\r\n		</tr>\r\n		<tr>\r\n			<th colspan="2">Общие</th>\r\n		</tr>\r\n		<tr>\r\n			<td>Код техники</td>\r\n			<td>ZR1000J</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Модельный год</td>\r\n			<td>2019</td>\r\n		</tr>\r\n		<tr>\r\n			<td>ABS</td>\r\n			<td>Да</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Тип рамы</td>\r\n			<td>Дуплексная, алюминий</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Объем бака</td>\r\n			<td>17</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Длина</td>\r\n			<td>2045</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Ширина</td>\r\n			<td>790</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Высота</td>\r\n			<td>1055</td>\r\n		</tr>\r\n		<tr>\r\n			<td>Снаряженная масса</td>\r\n			<td>221</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden;"><iframe allowfullscreen="" frameborder="0" height="360" src="//www.youtube.com/embed/1jNiQn40eRQ" style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;" width="640"></iframe></div>\r\n\r\n<p>&nbsp;</p>\r\n', '', 0, 0, 0, '', '', '', '', '', '', 'KAWASAKI Z1000 R EDITION ЧЕРНЫЙ 2019', 'ДВИГАТЕЛЬ ТИП ДВИГАТЕЛЬ РЯДНЫЙ КОЛИЧЕСТВО ЦИЛИНДР 4 ОБЪЕСТЬ ДВИГАТЕЛЬ 1043 ЛОШАДИНЫЙ СИЛА 142 КОЛИЧЕСТВО ТАКТОВЫЙ 4 МАКСИМАЛЬНЫЙ МОЩНОСТЬ 104 5 КВТ 142 Л С 10 000 ОБ МИНА МАКСИМАЛЬНЫЙ КРУТИТЬ МОМЕНТ 111 НМ 11 3КГС BULL М 7 300 ОБ МИНА СИСТЕМА ЗАПУСК ЭЛЕКТРОСТАРТЕР ТИП ОХЛАЖДЕНИЕ ЖИДКОСТНЫЙ ТРАНСМИССИЯ ТРАНСМИССИЯ 6 СЦЕПЛЕНИЕ МОКРЫЙ МНОГОДИСКОВЫЙ РУЧНОЙ ОБЩИЙ КОД ТЕХНИК ZR1000J МОДЕЛЬНЫЙ ГОД 2019 ABS ДА ТИП РАМ ДУПЛЕКСНЫЙ АЛЮМИНИЙ ОБЪЕСТЬ БАК 17 ДЛИНА 2045 ШИРИНА 790 ВЫСОТА 1055 СНАРЯДИТЬ МАССА 221 NBSP NBSP', 1, 0, 0);
HTML;
    }
    
    $sql_cat = "
      CREATE TABLE IF NOT EXISTS `$table_cat` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) DEFAULT '0',
        `title` varchar(255) NOT NULL,
        `img` varchar(255) NOT NULL,
        `country_id` int(11) DEFAULT NULL,
        `longtxt1` text,
        `longtxt2` text,
        `longtxt3` text,
        `seo_h1` varchar(255) DEFAULT NULL,
        `seo_title` varchar(255) DEFAULT NULL,
        `seo_description` varchar(255) DEFAULT NULL,
        `seo_keywords` varchar(255) DEFAULT NULL,
        `img_alt` varchar(255) DEFAULT NULL,
        `img_title` varchar(255) DEFAULT NULL,
        `orm_search_name` varchar(255) DEFAULT NULL,
        `orm_search` text,
        `fl_show_mine` tinyint(1) NOT NULL DEFAULT '0',
        `hide` tinyint(1) NOT NULL DEFAULT '0',
        `ord` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0; ";
    $sql_cat_insert = "
      INSERT INTO `$table_cat` (`id`, `parent_id`, `title`, `img`, `country_id`, `longtxt1`, `longtxt2`, `longtxt3`, `seo_h1`, `seo_title`, `seo_description`, `seo_keywords`, `img_alt`, `img_title`, `orm_search_name`, `orm_search`, `fl_show_mine`, `hide`, `ord`) VALUES   ";
    {
    $sql_cat_insert .=<<<HTML
(1, 0, 'Каталог товаров', '', NULL, '', '', '', '', '', '', '', '', '', 'КАТАЛОГ ТОВАР', '', 0, 0, 0),
(3, 1, 'Мотоциклы', '1554891570.jpg', NULL, '', '', '', '', '', '', '', '', '', 'МОТОЦИКЛ', '', 0, 0, 0),
(4, 3, 'Супер спорт', '1554891586.jpg', NULL, '', '', '', '', '', '', '', '', '', 'СУПЕР СПОРТ', '', 1, 0, 0),
(5, 3, 'Дорожные', '1554891755.png', NULL, '', '', '', '', '', '', '', '', '', 'ДОРОЖНЫЙ', '', 1, 0, 0),
(6, 3, 'Спортивно-туристические', '1554891845.jpg', NULL, '', '', '', '', '', '', '', '', '', 'СПОРТИВНО-ТУРИСТИЧЕСКИЙ', '', 1, 0, 0),
(7, 3, 'Круизеры', '1554891895.jpg', NULL, '', '', '', '', '', '', '', '', '', 'КРУИЗЕР', '', 1, 0, 0),
(8, 3, 'Универсальные', '1554892005.jpg', NULL, '', '', '', '', '', '', '', '', '', 'УНИВЕРСАЛЬНЫЙ', '', 1, 0, 0),
(9, 1, 'Кросс / эндуро', '1554892219.jpg', NULL, '', '', '', '', '', '', '', '', '', 'КРОСС ЭНДУРО', '', 0, 0, 0),
(10, 1, 'Квадроциклы', '1554892350.jpg', NULL, '', '', '', '', '', '', '', '', '', 'КВАДРОЦИКЛ', '', 0, 0, 0),
(11, 1, 'Гидроциклы', '1554892468.jpg', NULL, '', '', '', '', '', '', '', '', '', 'ГИДРОЦИКЛ', '', 0, 0, 0),
(12, 1, 'Экипировка', '1554893002.png', NULL, '', '', '', '', '', '', '', '', '', 'ЭКИПИРОВКА', '', 0, 0, 0),
(13, 1, 'Аксессуары', '1554892770.jpg', NULL, '', '', '', '', '', '', '', '', '', 'АКСЕССУАР', '', 0, 0, 0),
(14, 1, 'Запчасти', '1554892924.png', NULL, '', '', '', '', '', '', '', '', '', 'ЗАПЧАСТЬ', '', 0, 0, 0);
HTML;
    }
    $output = '';
    
    $this->create_cat_img_dir( $name );
    
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    $output .= $this->copy_img_module( $name, SOURCE_SITE_ONLINESHOP );
    
    $output .= $this->setup_database_table($title_cat, $table_cat, $sql_cat, $sql_cat_insert, $script_name  );
    $output .= $this->copy_img_module_cat( $name, SOURCE_SITE_ONLINESHOP );
    
    return $output;
  }
  
  function setup_module_basket_onlineshop( $title, $name ){
    $output = '';
    $sql_insert = null;
    $script_name = 'orders.php';
    
    $table  = DB_PFX.'basket';
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$table.'` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `state` int(11) NOT NULL DEFAULT "1",
        `last_time` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
    ';
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    
    $table  = DB_PFX.'basket_items';
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$table.'` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `basket_id` int(11) NOT NULL,
        `item_id` int(11) NOT NULL,
        `amount` int(11) NOT NULL,
        `siteprice` int(11) NOT NULL,
        `sitename` varchar(255) NOT NULL,
        `sitecatname` varchar(255) NOT NULL,
        `size` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `basket_id` (`basket_id`),
        KEY `item_id` (`item_id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';    
    
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    
    $table  = DB_PFX.'basket_orders';
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$table.'` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `basket_id` int(11) NOT NULL,
        `date_time` int(11) NOT NULL,
        `message` text NOT NULL,
        `status` int(11) NOT NULL DEFAULT "1",
        `phone` varchar(128) NOT NULL,
        `address` text NOT NULL,
        `fio` varchar(256) NOT NULL,
        `comment_cust` text NOT NULL,
        `comment_manager` text NOT NULL,
        `name` varchar(256) NOT NULL,
        `dost` int(11) NOT NULL,
        `date_dost` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `sum` int(11) NOT NULL,
        `manager_id` int(11) NOT NULL DEFAULT "0",
        `personal_accaunt_id` int(11) NOT NULL,
        `tovar` varchar(255) NOT NULL,
        `shown` tinyint(4) NOT NULL DEFAULT "0",
        `ip` varchar(32) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `date_time` (`date_time`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    
    $table  = DB_PFX.'basket_user';
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$table.'` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(256) NOT NULL,
        `password` varchar(32) NOT NULL,
        `fio` varchar(256) NOT NULL,
        `date_reg` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    
    $table  = DB_PFX.'basket_user_contacts';
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$table.'` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `address` varchar(256) NOT NULL,
        `phone` varchar(64) NOT NULL,
        `org` varchar(256) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name  );
    
    $table  = DB_PFX.'basket_order_status';
    $sql = '
      CREATE TABLE IF NOT EXISTS `'.$table.'` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(128) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ';
    $sql_insert = '
      INSERT INTO `'.$table.'` (`id`, `title`) VALUES';
    $sql_insert .= <<<HTML
        (1, 'новый'),
        (2, 'Не дозвонились'),
        (3, 'Сам приедет'),
        (4, 'Сам приедет, заказ собран'),
        (5, 'Нужно собрать заказ'),
        (6, 'Заказ собран ждет курьера'),
        (7, 'У курьера на доставке'),
        (8, 'Курьер доставил'),
        (9, 'Завершен'),
        (10, 'Отменен');
HTML;
    $output .= $this->setup_database_table($title, $table, $sql, $sql_insert, $script_name   );
    
    return $output;
  }
  
  function setup_database_module_required(){
    
    $this->add_content( $this->wrap_block(  "<h2>Базовые настройки</h2>" ));
      
    $this->add_content( $this->wrap_block(  # Параметры
                                            $this->setup_module_config( 'Параметры', 'config' )  ));
                                            
    $this->add_content( $this->wrap_block(  # СЕО
                                            $this->setup_module_seo( 'СЕО', 'seo' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Оформление сайта
                                            $this->setup_module_design( 'Оформление сайта', 'design' )  ));
    
    $this->add_content( $this->wrap_block(  # Администрирование
                                            $this->setup_module_accounts( 'Администрирование', 'accounts' )  ));
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->setup_module_url( 'Человеко-понятные адреса', 'url')  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->setup_module_mine_block( 'Блоки на главной странице', 'mine_block' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->setup_module_carusel( 'Слайдер', 'carusel' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Заявки
                                            $this->setup_module_reservations( 'Заявки', 'reservations' )  ));                                        
    $this->add_content( $this->wrap_block(  # Логи входа в админку
                                            $this->setup_module_admin_logs( 'Логи входа в админку', 'admin_logs' )  ));
    
    $this->add_content( $this->wrap_block(  # Логи редактирования контента
                                            $this->setup_module_all_log( 'Логи редактирования контента', 'all_log' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->setup_module_all_images( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->setup_module_all_files( 'Дополнительныe файлы', 'all_files' )  ));
  }
  
  function setup_database_module_cutaway(){ # Сайт визитка
    
    $this->add_content( $this->wrap_block(  "<h2>Сайт визитка</h2>" ));
                                              
    $this->add_content( $this->wrap_block(  # Содержание сайта
                                            $this->setup_module_smpl_article_cutaway( 'Содержание сайта', 'smpl_article' )  ));
                                              
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->insert_def_module_carusel_cutaway('Слайдер', 'carusel')  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->insert_def_module_mine_block_cutaway( 'Блоки на главной странице', 'mine_block')  ));
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->insert_def_module_url_cutaway( 'Человеко-понятные адреса', 'url' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->insert_def_module_all_images_cutaway( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->insert_def_module_all_files_cutaway( 'Дополнительныe файлы', 'all_files' )  ));
                                            
    $this->add_content( $this->wrap_block(  # SEO Настройки
                                            $this->insert_def_module_seo_cutaway( 'SEO Настройки', 'seo' )  ));
  }
  
  function setup_database_module_corporate(){ # Корпаротивный/новостной сайт
    
    $this->add_content( $this->wrap_block(  "<h2>Корпаротивный/новостной сайт</h2>" ));
    
    $this->add_content( $this->wrap_block(  # Каталог статей
                                            $this->setup_module_articles_corporate( 'Каталог статей', 'articles' )  ));
    
    $this->add_content( $this->wrap_block(  # Новости
                                            $this->setup_module_news_corporate( 'Новости', 'news' )  ));
    
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->insert_def_module_carusel_corporate('Слайдер', 'carusel')  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->insert_def_module_mine_block_corporate( 'Блоки на главной странице', 'mine_block')  ));
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->insert_def_module_url_corporate( 'Человеко-понятные адреса', 'url' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->insert_def_module_all_images_corporate( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->insert_def_module_all_files_corporate( 'Дополнительныe файлы', 'all_files' )  ));
                                            
    $this->add_content( $this->wrap_block(  # SEO Настройки
                                            $this->insert_def_module_seo_corporate( 'SEO Настройки', 'seo' )  )); 
  }
  
  function setup_database_module_onlineshop(){ # Интернет магазин
    
    $this->add_content( $this->wrap_block(  "<h2>Корпаротивный/новостной сайт</h2>" ));
    
    $this->add_content( $this->wrap_block(  # Курсы валют
                                            $this->setup_module_currency_onlineshop( 'Курсы валют', 'currency' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Блоки на главной странице
                                            $this->insert_def_module_mine_block_onlineshop( 'Блоки на главной странице', 'mine_block')  ));
    
    $this->add_content( $this->wrap_block(  # Слайдер
                                            $this->insert_def_module_carusel_onlineshop('Слайдер', 'carusel')  ));
    
    $this->add_content( $this->wrap_block(  # Каталог статей
                                            $this->setup_module_articles_onlineshop( 'Каталог статей', 'articles' )  ));
    
    $this->add_content( $this->wrap_block(  # Новости
                                            $this->setup_module_news_onlineshop( 'Новости', 'news' )  )); 
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe изображения
                                            $this->insert_def_module_all_images_onlineshop( 'Дополнительныe изображения', 'all_images' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Дополнительныe файлы
                                            $this->insert_def_module_all_files_onlineshop( 'Дополнительныe файлы', 'all_files' )  ));
                                            
    $this->add_content( $this->wrap_block(  # SEO Настройки
                                            $this->insert_def_module_seo_onlineshop( 'SEO Настройки', 'seo' )  )); 
                                            
    $this->add_content( $this->wrap_block(  # ЧПУ
                                            $this->insert_def_module_url_onlineshop( 'Человеко-понятные адреса', 'url' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Варианты наличия
                                            $this->setup_module_availability_onlineshop( 'Варианты наличия', 'availability' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Страны
                                            $this->setup_module_country_onlineshop( 'Страны', 'country' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Бренд
                                            $this->setup_module_brand_onlineshop( 'Бренд', 'brand' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Ед. измерения
                                            $this->setup_module_units_onlineshop( 'Ед. измерения', 'units' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Логи поиска
                                            $this->setup_module_search_log_onlineshop( 'Логи поиска', 'search_log' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Каталог товаров
                                            $this->setup_module_goods_onlineshop( 'Каталог товаров', 'goods' )  ));
                                            
    $this->add_content( $this->wrap_block(  # Корзина товаров
                                            $this->setup_module_basket_onlineshop( 'Корзина товаров', 'basket' )  ));
  }
  
  
  function setup_database_module_all(){
    
    $this->setup_database_module_required();
    
  }
  
  function setup_database_module(){
    $output = '';
    if( $this->is_database_access() ){
      
      $this->setup_database_module_all();
      
    }else{
      $this->add_content( $this->wrap_block( $this->test_database_access() ));
    }
    
    return $output; 
  }
  
  function delete_database_module(){
    $output = ''; 
    if( $this->is_database_access() ){
      
      $this->add_content( $this->wrap_block(  $this->delete_table()  ));
    
    }else{
      $this->add_content( $this->wrap_block( $this->test_database_access() ));
    }
    
    return $output; 
  }
  
  function delete_all(){
    $output = ''; 
    if( $this->is_database_access() ){
      
      $this->add_content( $this->wrap_block(  $this->delete_all_table()  ));
      
      $this->add_content( $this->wrap_block(  $this->delete_all_images()  ));
      
      
    }else{
      $this->add_content( $this->wrap_block( $this->test_database_access() ));
    }
    
    return $output; 
  }

}
