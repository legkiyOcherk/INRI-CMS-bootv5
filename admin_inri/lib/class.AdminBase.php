<?
/**
* style admin
* array (
*   AdminLTE
* )
*/

class BaseAdmin{
  var $style_admin = 'AdminLTE';
  
  var $prefix; 
  var $sitelink;
  
  var $pdo;
  
  var $adminDoctype = '<!DOCTYPE html>';
  var $adminCharset = '<meta http-equiv="Content-Type" content="text/html" charset="utf-8">';
  var $adminTitle;
  var $adminDescription = '';
  var $adminKeywords = '';
  var $adminHeaderScripts = '';
  var $adminFooterScripts = '';
  
  var $access_error;
  var $acc_info;
  
  var $current_rights;
  var $user;
  var $mainmenu;
  var $mainmenu_add_data;
  var $content;
  
  var $is_admin_navigation = false;
  var $header = '';
  var $title = '';
  var $bread = '';
  var $search_query = '';
  var $cont_footer = '';
  
  
  // конструктор
  function __construct () {
    $this->adminTitle = $this->prefix.'admin v4.0';
    
    if (!session_id()) session_start();
    
    require_once(__DIR__.'/../../define.php');
    require_once(__DIR__.'/../config.inc.php');
    require_once(__DIR__.'/mysql.lib.php');
    require_once(__DIR__.'/global.lib.php');
    require_once(__DIR__.'/class.db.php');
    require_once(__DIR__.'/auth.lib.php');
    
    $this->prefix   = DB_PFX; 
    $this->sitelink = SITE_NAME;
    
    AllFunction::validate_post_vars();
    
    $this->pdo = db_open();
    $this->auth();
    
    if (isset($_SERVER["HTTP_REFERER"])){
    	$_SESSION["HTTP_REFERER"] = $_SERVER["HTTP_REFERER"];
    }else{
    	$_SESSION["HTTP_REFERER"] = FALSE;
    }
    
    $this->initHeaderScripts();
    $this->initFooterScripts();
    $this->initSearchQuery();
    
    $this->getMineMenuListArr();
    
    if ( isset($_SESSION["WA_USER"]['is_admin']) && $_SESSION["WA_USER"]['is_admin']) {
      $this->mainmenu['accounts.php'] = 'Пользователи';
    }
    
  }
  
  function getMineMenuListArr(){
    $this->mainmenu = array(	
      "index.php" => ADM_DIR,
      "mine_block.php"=>"Главная страница",
      #"smpl_article.php"=>"Содержание сайта",
      "articles.php?c_id=root"=>"Содержание сайта",     
      "goods.php?c_id=1"=>"Каталог товаров", 
      "goods.php?view_tree"=>"Дерево всех категорий", 
      "news.php"=>"Новости",
      
      /*--------------------------------------------*/
      "delimiter1" => "Блоки на главной странице",
      /*--------------------------------------------*/
      "carusel.php" => "Слайдер на главной",
      /*--------------------------------------------*/
      "delimiter2" => "Справочники",
      /*--------------------------------------------*/
      "availability.php"=>"Варианты наличия",
      "country.php"=>"Страны",
      "brand.php"=>"Бренд",
      "units.php"=>"Ед. измерения",
      /*--------------------------------------------*/
      "delimiter3" => "Администрирование",
      /*--------------------------------------------*/
      "orders.php"=>"Корзина",
      "reservations.php"=>"Заявки",
      "search_log.php"=>"Логи поиска",
      "config.php"=>"Блок контакты",
      "seo.php"=>"SEO Настройки",
      "design.php"=>"Оформление сайта",
      
      /*--------------------------------------------*/
      "delimiter4" => "Система",
      /*--------------------------------------------*/
      "all_files.php"=>"Файлы",
      "all_images.php"=>"Изображения",
      "all_log.php"=>"Логи",
      "url.php"=>"ЧПУ",
      
      /*""=>"---- --- --- ----",
      "search_log.php"=>"Лог поиска",
      */
          
		);
  }
  
  function setMineMenuAddData(){
    foreach ($this->mainmenu as $k=>$v){
      switch ($v) {
          case 'Главная':
          case ADM_DIR:
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fas fa-tachometer-alt"></i>',
                  'short_decr' => 'Начальный экран'
                ); break;
                
          case 'Статьи':
          case 'Содержание сайта':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-list-ul" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Новости':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-list-ul" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Слайдер на главной':
          case 'Слайдер Гостиница':
          case 'Слайдер Кафе':
          case 'Изображения':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-picture-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Каталог':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-folder-open-o"></i>',
                  'short_decr' => ''
                ); break;
          
          case 'Полный каталог':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-sitemap" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;      
                
          case 'Отзывы':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-comment-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
               
               
          case 'Партнеры':
          case 'Блок контакты':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-handshake-o" aria-hidden="true"></i> ',
                  'short_decr' => ''
                ); break;
                
          case 'Оформление сайта':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-play" aria-hidden="true"></i> ',
                  'short_decr' => ''
                ); break;
                
          case 'Файлы':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-file-text-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
              
          case 'SEO Настройки':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-sliders" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Настройки':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-gear fa-fw"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Логи':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-archive" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
              
          case 'Заявки':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-bell" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Пользователи':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-user fa-fw"></i>',
                  'short_decr' => ''
                ); break;
              
              
          default:
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-table"></i>',
                ); break;
      }
    }
  }
  
  function setForName($name, $value){
    $this->$name = $value;
  }
  
  function getForName($name){
    return $this->$name;
  }  
  
  function getHeadCustom(){
    $output = '';
    
    switch($this->style_admin){
        
      case 'AdminLTE':
        $output .= '
        <link href="'.IA_URL.'admin_style/a_lte/dist/css/AdminLTE.css" rel="stylesheet">
          <!-- AdminLTE Skins. Choose a skin from the css/skins
          folder instead of downloading all of them to reduce the load. -->
          <link rel="stylesheet" href="'.IA_URL.'admin_style/a_lte/dist/css/skins/_all-skins.min.css">';
        break;
        
    }
    
    return $output;
  }
  
  function getFooterCustom(){
    
    switch($this->style_admin){
        
      case 'AdminLTE':
        $this->adminFooterScripts .= '
        <!-- SlimScroll -->
        <script src="'.IA_URL.'admin_style/vendor/slimScroll/jquery.slimscroll.min.js"></script>
        <!-- FastClick -->
        <script src="'.IA_URL.'admin_style/vendor/fastclick/fastclick.js"></script>
        <!-- AdminLTE App -->
        <script src="'.IA_URL.'admin_style/a_lte/dist/js/app.min.js"></script>
        ';
        /*
        <!-- AdminLTE for demo purposes -->
        <script src="'.IA_URL.'admin_style/a_lte/dist/js/demo.js"></script>
        <script>
          $(document).ready(function () {
            $(".sidebar-menu").tree();
          })
        </script>';*/
        break;
    }
  }
  
  function getHead($output = ''){
    
    $output .= '
        <!-- Bootstrap Core CSS -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">';
        #<link href="'.IA_URL.'admin_style/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    $output .= '
        <!-- Custom Fonts -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.0/css/all.css" >';
        #<link href="'.IA_URL.'admin_style/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        
    $output .= '
        <!-- Custom Theme JavaScript -->';
        
    $output .= $this->getHeadCustom();
    
    $output .= '
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        
        <link href="primary.css" rel="stylesheet" type="text/css" media="screen">
        <link href="print.css" rel="stylesheet" type="text/css" media="print">
        <link rel="stylesheet" href="'.IA_URL.'js/ui/css/ui-lightness/jquery-ui-1.10.4.min.css">
        
        <!-- iCheck for checkboxes and radio inputs -->
        <link rel="stylesheet" href="'.IA_URL.'admin_style/vendor/iCheck/skins/all.css">';
        
    #$output .= '
    #    <script src="'.IA_URL.'js/ui/js/jquery-1.10.2.js"></script>
    #    <script src="'.IA_URL.'js/ui/js/jquery-ui-1.10.4.min.js"></script> 
    #    <script src="'.IA_URL.'js/ui/js/jquery-migrate-1.2.0.js"></script>';
    
    $output .= '
        <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
        <script src="https://code.jquery.com/jquery-migrate-1.4.1.js"></script>';
        
    $output .= '
        <script type="text/javascript" src="'.IA_URL.'js/json2.js"></script>
        
        <link rel="icon" type="image/png" href="'.ADMIN_FAVICON.'" />';

    
    return $output;
    
  }
  
  function initHeaderScripts(){
    
    $this->adminHeaderScripts .= '
     
    ';
    
  }
  
  function initFooterScripts(){

    $this->adminFooterScripts .= '
      
      <!-- Bootstrap Core JavaScript -->
      <script src="'.IA_URL.'admin_style/vendor/bootstrap/js/bootstrap.min.js"></script>

      <!-- Metis Menu Plugin JavaScript -->
      <script src="'.IA_URL.'admin_style/vendor/metisMenu/metisMenu.min.js"></script>
      
      <!-- iCheck 1.0.1 -->
      <script src="'.IA_URL.'admin_style/vendor/iCheck/icheck.min.js"></script>
      <script>
      $(document).ready(function(){
        $(".group_checkbox").iCheck({
          checkboxClass: "icheckbox_flat-red",
          radioClass: "iradio_flat-red"
        });
      });
      </script>
      
      <script type="text/javascript" src="'.IA_URL.'ckeditor/ckeditor.js"></script>
      <!-- ckeditor/plugins/glyphiconspt -->
      <script>$(document).ready(function() {CKEDITOR.dtd.$removeEmpty[\'span\'] = false;});</script>
      ';
      
    /*$this->adminFooterScripts .= <<<HTML
      <script>
      $(document).ready(function() {
        CKEDITOR.replace( 'longtxt1', {
                                        'language' : 'ru'
                                      } );
      });
      </script>
HTML;
*/


    $this->getFooterCustom();
    
    /*<!-- Morris Charts JavaScript -->
      <script src="'.IA_URL.'/admin_style/vendor/raphael/raphael.min.js"></script>
      <script src="'.IA_URL.'/admin_style/vendor/morrisjs/morris.min.js"></script>
      <script src="'.IA_URL.'/admin_style/sb-admin-2/data/morris-data.js"></script> */
  }
 
  function initSearchQuery(){
    
    if(!isset($_SESSION['search_admin_q'])){
      $_SESSION['search_admin_q'] = '';
    }#pri($_POST);
    
    if(isset($_POST['search_admin_q'])){
      $search_quer = $_POST['search_admin_q'];
      $search_quer = strip_tags($search_quer);
      $search_quer = trim(preg_replace('#[^a-zA-Z0-9а-яёйА-ЯЁЙ\.\_\-\+\*\=\:\/]+#ui', ' ', $search_quer));
      #pri($search_quer);
  
      if($search_quer){
        #session_destroy();
        if($search_quer != $_SESSION['search_admin_q']){
          #echo "ne sovpadaet";
          $_SESSION['search_admin_q'] = $search_quer;
        }
      }
    }
    $this->search_query = $_SESSION['search_admin_q'];
  }
  
  function setContent($cont){
    $this->content = $cont;
  }
  
  function auth(){
    if (!isset($_SESSION["WA_USER"])){
    	if (isset($_GET["auth"]) && isset($_POST["login"]) && isset($_POST["password"])){
    		$login = addslashes($_POST["login"]);
    		
        $query = $this->pdo->query("SELECT * FROM `".DB_PFX."accounts` WHERE `login` = '$login'");
        
    		if ($query->rowCount()){
          $this->acc_info = $query->fetch();
          
    			if (adm_password_hash($_POST["password"], $this->acc_info["key"]) == $this->acc_info["hash"]){
    				$_SESSION["WA_USER"] = $this->acc_info;
    			}
    			else 
    			{
    				$this->access_error=true;
    				$user_id=$this->acc_info["id"];
    			}
    		} 
    		else 
    		{
    			$this->access_error=true;
    			$user_id=0;
    		}
    	}
    }
    if (isset($_GET["logout"])){
    	if (isset($_SESSION["WA_USER"])) unset($_SESSION["WA_USER"]);
    }
  }
  
  function getTopMenu(){
    $output = '';
    
    $output .= '
      <ul class="nav navbar-top-links navbar-right">';
    
  $output .= '
        <li class="dropdown" style="float: right;">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#">
              <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
          </a>
          <ul class="dropdown-menu dropdown-user">
            <li><a href="'.IA_URL.'currency.php"><i class="fa fa-money fa-fw"></i> Курсы валют</a></li>';
  if ($_SESSION["WA_USER"]['is_admin']) {
    $output .= '
             <li><a href="'.IA_URL.'accounts.php"><i class="fa fa-user fa-fw"></i> Пользователи</a></li>';
  }
  $output .= '
            <li><a href="'.IA_URL.'config.php"><i class="fa fa-gear fa-fw"></i> Настройки</a></li> 
            <li class="divider"></li>
            <li><a href="'.IA_URL.'?logout"><i class="fas fa-sign-out-alt"></i> Выход</a></li>
          </ul>
          <!-- /.dropdown-user -->
        </li>
        <!-- /.dropdown -->
      </ul>
      <!-- /.navbar-top-links -->
    ';
    
    return $output;
  }
  
  function getLeftMenu(){
    $output = '';
    
    foreach ($this->mainmenu as $k=>$v)
    {
    	if ($this->user["is_admin"]) $output .= "<li><a href='$k'>$v</a></li>";
    	else
    	{
    		if (array_key_exists($k,$this->scripts)) 
    		{
    		$ok=false;
    			foreach ($this->current_rights as $this->val)
    			{
    				if (in_array($val,$scripts[$k])) $ok=true;
    			}
    		}
    		if ($ok){
          $output .= '<li><a href="$k"><i class="fa fa-table fa-fw"></i> $v</a></li>';
          //$output .= "<li><a href='$k'></a></li>";
        }
    	}
    }
    
    $output_example = '
            <li><a href="'.IA_URL.'/admin_style/pages/index.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            
            <li>
              <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Charts<span class="fa arrow"></span></a>
              <ul class="nav nav-second-level">
                <li><a href="'.IA_URL.'/admin_style/pages/flot.html">Flot Charts</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/morris.html">Morris.js Charts</a></li>
              </ul>
              <!-- /.nav-second-level -->
            </li>
            
            <li><a href="'.IA_URL.'/admin_style/pages/tables.html"><i class="fa fa-table fa-fw"></i> Tables</a></li>
            
            <li><a href="'.IA_URL.'/admin_style/pages/forms.html"><i class="fa fa-edit fa-fw"></i> Forms</a></li>
            
            <li>
              <a href="#"><i class="fa fa-wrench fa-fw"></i> UI Elements<span class="fa arrow"></span></a>                                <ul class="nav nav-second-level">
                <li><a href="'.IA_URL.'/admin_style/pages/panels-wells.html">Panels and Wells</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/buttons.html">Buttons</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/notifications.html">Notifications</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/typography.html">Typography</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/icons.html"> Icons</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/grid.html">Grid</a></li>
              </ul>
              <!-- /.nav-second-level -->
            </li>
            
            <li>
              <a href="#"><i class="fa fa-sitemap fa-fw"></i> Multi-Level Dropdown<span class="fa arrow"></span></a>
              <ul class="nav nav-second-level">
                <li><a href="#">Second Level Item</a></li>
                <li><a href="#">Second Level Item</a></li>
                <li>
                  <a href="#">Third Level <span class="fa arrow"></span></a>
                  <ul class="nav nav-third-level">
                    <li><a href="#">Third Level Item</a></li>
                    <li><a href="#">Third Level Item</a></li>
                    <li><a href="#">Third Level Item</a></li>
                    <li><a href="#">Third Level Item</a></li>
                  </ul>
                  <!-- /.nav-third-level -->
                </li>
              </ul>
              <!-- /.nav-second-level -->
            </li>
            
            <li class="active">
              <a href="#"><i class="fa fa-files-o fa-fw"></i> Sample Pages<span class="fa arrow"></span></a>
              <ul class="nav nav-second-level">
                <li><a class="active" href="'.IA_URL.'/admin_style/pages/blank.html">Blank Page</a></li>
                <li><a href="'.IA_URL.'/admin_style/pages/login.html">Login Page</a></li>
              </ul>
              <!-- /.nav-second-level -->
            </li>
    ';
    //$output .= $output_example;
    
    return $output;
  }
  
  function getMineMenu($output = ''){
    
    $output .= '
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
    
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
    ';
    #   <a class="navbar-brand" href="'.IA_URL.'">'.$this->adminTitle.'</a>
    $output .= '
        <a class="navbar-brand" href="/"> <div class = "admin_logo"> </div> &nbsp; '.$this->adminTitle.'</a>
      </div>
      <!-- /.navbar-header -->
    ';
    
    $output .= $this->getTopMenu();
    
    $output .= '
      <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
          <ul class="nav" id="side-menu">
            
            <li class="sidebar-search">
              <!-- Поиск -->
              <div class="input-group custom-search-form">
                <input type="text" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                  <button class="btn btn-default" type="button">
                    <i class="fa fa-search"></i>
                  </button>
                </span>
              </div>
              <!-- /input-group -->
            </li>
            

            
    ';
    $output .= $this->getLeftMenu();
    $output .= '        
          </ul>
        </div>
        <!-- /.sidebar-collapse -->
      </div>
      <!-- /.navbar-static-side -->
    ';
    $output .= '
    </nav>
    ';

    return $output;
  }  
  
  function checkRights(){
    
    $user = array();
    if(isset($_SESSION["WA_USER"]))
      $user = $_SESSION["WA_USER"];
    
    $this->scripts=array(	
			"index.php"         => array("iscontent","is_programmer","ismanag","iscatalog","isjournalist"),
      "search.php"        => array("iscontent","is_programmer","ismanag","iscatalog","isjournalist"),
      "smpl_article.php"  => array("ismanag", "iscontent"),
      "mine_block.php"    => array("ismanag", "iscontent"),
      "carusel.php"       => array("ismanag", "iscontent"),
      "reservations.php"  => array("ismanag"),
      "seo.php"           => array("ismanag"),
      "config.php"        => array("ismanag"),
      "all_files.php"     => array("ismanag", "iscontent"),
      "all_images.php"    => array("ismanag", "iscontent"),
      "url.php"           => array("ismanag"),
      
      "design.php"        => array("is_programmer"),
      "all_log.php"       => array("is_programmer"),
      
      /*"rubric.php"=>array("ismanag"),
      "store.php"=>array("ismanag"),
      "info_blok.php"=>array("ismanag"),
      "articles.php?c_id=root"=>array("ismanag"),
      "news.php"=>array("ismanag"),
      "renter.php"=>array(),
      "feedback.php"=>array("ismanag"),*/
      
      
      
      
		);

  	if (isset($user["iscontent"])    && $user["iscontent"])	    $this->current_rights[]="iscontent";
    if (isset($user["is_programmer"])&& $user["is_programmer"])	$this->current_rights[]="is_programmer";
  	if (isset($user["ismanag"])      && $user["ismanag"])	      $this->current_rights[]="ismanag";
  	if (isset($user["iscatalog"])    && $user["iscatalog"])	    $this->current_rights[]="iscatalog";
  	if (isset($user["isjournalist"]) && $user["isjournalist"])	$this->current_rights[]="isjournalist";

  	$check=str_replace( IA_URL, "", $_SERVER["PHP_SELF"]);
    
    $this->user = $user;
    $this->check = $check;
    
  }
  
  function dieRights(){
    if( 
        isset($this->user) && 
        is_array($this->user) && 
        (!isset($this->user["is_admin"]) || !$this->user["is_admin"] ) 
      ){
        
      if (array_key_exists($this->check, $this->scripts)){
    		$ok=false;
        if(is_array($this->current_rights) && $this->current_rights)
      		foreach ($this->current_rights as $v){
      			if (in_array($v,$this->scripts[$this->check])) $ok=true;
      		}
    	}
    	if (!$ok){
        unset( $_SESSION["WA_USER"] );
        die("<div class='alert alert-error'>Недостаточно прав</div>");
      }
    }
  }
  
  function logsWrite(){
    $ip=$_SERVER["REMOTE_ADDR"];
    $user=$_SESSION["WA_USER"];
    $script=str_replace( IA_URL, "", $_SERVER["PHP_SELF"] );
    if ($user["id"]) $user_logged=$user["id"]; else $user_logged=0;
    if ($_POST)
    {
    	#mysql_query("INSERT INTO `".DB_PFX."admin_logs` SET `ip`='$ip', `script`='$script',`date_time`=now(), `user_id`=$user_logged");
    	#$log_id=mysql_insert_id();
      $now_date_time = date('Y-m-d H:i:s');
      $query = $this->pdo->query("INSERT INTO `".DB_PFX."admin_logs` SET `ip`='$ip', `script`='$script',`date_time`='$now_date_time', `user_id`=$user_logged"); 
      $log_id = $this->pdo->lastInsertId();
      switch ($script)
    	{
    		case "index.php":
    		if ($this->access_error){
          
    			if ($user_id==0){
            $this->pdo->query("UPDATE `".DB_PFX."admin_logs` SET `action`=17, `user_id`=0, `changes`='Ошибка входа: введен неверный логин {$_POST["login"]}' WHERE `id`=$log_id");
          }else{ 
            $this->pdo->query("UPDATE `".DB_PFX."admin_logs` SET `action`=17, `user_id`=$user_id, `changes`='Ошибка входа: введен неверный пароль для пользователя {$_POST["login"]}' WHERE `id`=$log_id");
          }
    		}else{ 
          if($log_id){
            $s = "UPDATE `".DB_PFX."admin_logs` SET `action`=1, `user_id`=$user_logged, `changes`='Пользователь {$_POST["login"]} успешно вошел в систему ' WHERE `id`=$log_id";  
            $this->pdo->query($s);
          }
          
        }
    		break;
		    
    	}
    }
  }
 
  function getHeader(){
    $output = '';
    $output .= '
      <div id="wrapper">';
    
    //$this->dieRights();
    //$this->logsWrite();

    return $output;
  }
  
  function getFooter($output = ''){
    
    $output .= '
      </div>
      <!-- /#wrapper -->
    ';
    
    return $output;
  }
  
  function showSearchItems($tbl = '', $tbl_title = '', $mod_script, $type = 0){
    
    $output = $s_find = $where = '';
    
    $types = array(
      '0' => 'Only table link',
      '1' => 'Table item link',
      '2' => 'Table cat item link'
    );
    
    $field_arr = array();
    # = '';
    
    $result = $this->pdo->query("SHOW COLUMNS FROM `{$tbl}`");
    $i = 0; 
    while($col = $result->fetch()){
      $field_arr[] = $col['Field'];
      #pri($col); 
      #print "<br>\n";
      if($i++ ) $where .= ' OR ';
      $where .= ' CONVERT(  `'.$col['Field'].'` USING utf8 ) LIKE  \'%'.$this->search_query.'%\' 
      ';
    }
    $s_find = "
      SELECT * 
      FROM  `$tbl` 
      WHERE ( 
      $where )
    ";
    /*$s_find .= "
      LIMIT 1000
    ";*/
    
    #pri($field_arr);
    #pri($s_find);
    
    if($q = $this->pdo->query($s_find)){
      if($count = $q->rowCount()){
        
        
        /*$output .= '
<div class="box box-success">
  <div class="box-header">
    <h3 class="box-title">';*/
        $output .= '<hr style = "border-top: 3px solid #00a65a;  margin-top: 5px;   margin-bottom: 35px; "    />';
        $output .= '<h3>';
        $output .= '<a href="..'.IA_URL.$mod_script.'" class="btn btn-success btn-md" title="Перейти к модулю">
              <big>'.$count.'</big>
              </a>';
        #$output .= ' соответствие в модуле <a href="..'.IA_URL.'$mod_script.'" title="Перейти к модулю">'.$tbl_title.'</a> ';
        $output .= ' <a href="..'.IA_URL.$mod_script.'" title="Перейти к модулю">'.$tbl_title.'</a> ';
        $output .= '<a href="..'.IA_URL.$mod_script.'" class="btn btn-primary btn-sm" title="Перейти к модулю">
                <i class="fa fa-binoculars" aria-hidden="true"></i> Перейти
              </a>';
        $output .= '</h3>';
        /*$output .= '  
    </div>
  <div class="box-body">';*/
         
        if($type){
          $output .= '
          <table id="" class="adm_search_table table  table-sm table-striped">
            <thead>
              <tr class="th nodrop nodrag" style = "background-color: #3c8dbc; color: #fff;">
              	<th style="width: 55px;">#</th>
          		  <th>Название</th>
          		  <th style="width: 80px">Действие</th>
              </tr>
            </thead>
            <tbody>
          ';
          $edit_link = 'edits';
          if($type == '2')$edit_link = 'editc';
          while($r = $q->fetch()){
            $title = $r['title'];
            if(!$title) $title = $r['login'];
            $output .= '
              <tr>			 
                <td>'.$r['id'].'</td>
                <td>
                  <a href="..'.IA_URL.$mod_script.'?'.$edit_link.'='.$r['id'].'" class="btn btn-info btn-sm" title="Обзор">
                    <i class="fa fa-binoculars"></i>
                  </a>
                  <a href="..'.IA_URL.$mod_script.'?'.$edit_link.'='.$r['id'].'" title="Обзор"><b>'.$title.'</b> </a>
                </td>
              
          	    <td style="" class="img-act">
                  <a href="..'.IA_URL.$mod_script.'?'.$edit_link.'='.$r['id'].'" class="btn btn-info btn-sm" title="Обзор">
                    <i class="fa fa-binoculars"></i> Обзор
                  </a>  
                </td>
    			    </tr>';
          }
          $output .= '
            </tbody>
          </table>';  
        }
        /*$output .= '
          </div>
        <!-- /.box-body -->
      </div>';*/
        
      }
    }
    
    return $output;
  }
  
  function showSearch(){
    $output = '';
    $this->checkRights();
    $this->dieRights();
    $output = AllFunction::setHeaderForAdm('Поиск', 'Поиск', $this);
    $output .= '
      <h2><span style = "color:red;">Запрос:</span> '.$this->search_query.'</h2>
    ';
    $table_list_arr = array();
    $CFG = & $_SESSION["NEX_CFG"];
    $table_list = $this->pdo->query("SHOW TABLES FROM `".$CFG["db_basename"]."`");
    while ($row = $table_list->fetch()) {
      $table_list_arr[] = $row['Tables_in_'.$CFG["db_basename"]];
    }
    #pri($table_list_arr);
    #pri($this->mainmenu);
    $arr_module_is_search = array(); # Хранит скрипты в которых уже проводился поиск
    
    foreach ($this->mainmenu as $k=>$v){
      $mod_script = $mod_title = '';
      if( strripos($k, ".php") === false ) continue;
      
      if( strripos($k, "?") ) $k = strstr($k, '?', true);
      
    	if ($this->user["is_admin"]){
        $mod_script = $k;
        $mod_title = $v;
      }else{
        $ok=false;
    		if (array_key_exists($k, $this->scripts)){
    		  
    			foreach ($this->current_rights as $val){
    				if (in_array($val, $this->scripts[$k])) $ok=true;
    			}
    		}
    		if ($ok){
          $mod_script = $k;
          $mod_title = $v;
        }
    	}
      
      if( in_array($mod_script, $arr_module_is_search) ) { # Не искать в одном и том же несколько раз
        continue;
      }else{
        $arr_module_is_search[] = $mod_script;
      }
      
      if($mod_script){
        if( strpos($mod_script, '.php')){
          $mod_t = str_replace('.php', '', $mod_script);
          
          $mod_cat_table = $this->prefix.$mod_t.'_cat';
          $mod_table = $this->prefix.$mod_t;
          
          if($mod_t == 'param'){
            #$output .= "param - config<br>";
            $output .= $this->showSearchItems('config', $mod_title, $mod_script);
          }
          if(in_array($mod_cat_table, $table_list_arr)){
            #$output .= "$mod_cat_table<br>";  
            $output .= $this->showSearchItems($mod_cat_table, $mod_title, $mod_script, 2);
          }
          if(in_array($mod_table, $table_list_arr)){
            #$output .= "$mod_table<br>";
            $output .= $this->showSearchItems($mod_table, $mod_title, $mod_script, 1);
          }
        }
      }
    }
    
    
    return $output;
  }
  
  function blankPage($output = ''){
    
    #$output .= $this->getMineMenu();
    
    /*$output .= '

      <!-- Page Content -->
      <div id="page-wrapper">
          <div class="container-fluid">
              ';*/
    $output .= '
                <div class="row">
                  <div class="col-lg-12">
    ';
    if ($_SESSION["WA_USER"]["fullname"]) 
      $output .=  '<h1 class="page-header">Добро пожаловать, '.$_SESSION["WA_USER"]["fullname"].'</h1>';
      $output .= '<div style = "    text-align: center;">';
      $output .= '<ul class="nav nav-pills nav-stacked" style="max-width:250px; margin: 0 auto; ">
      ';
      foreach ($this->mainmenu as $k=>$v)
      {
      	if ($this->user["is_admin"]) $output .= "<li><a href='$k'>$v</a></li>";
      	else
      	{
      		if (array_key_exists($k, $this->scripts)) 
      		{
      		$ok=false;
      			foreach ($this->current_rights as $val)
      			{
      				if (in_array($val, $this->scripts[$k])) $ok=true;
      			}
      		}
      		if ($ok) $output .= "<li><a href='$k'>$v</a></li>";
      	}
      }
      if ($_SESSION["WA_USER"]['is_admin']) {
	      $output .= '<li><a href="accounts.php">Пользователи</a></li>';
      }
      
      $output .= '<li><a href="'.IA_URL.'?logout" target="_top" class="domain">Выход</a></li>';
      $output .= '</ul>';
    $output .= '</div>';
                      
    $output .= ' 
                  </div>
                  <!-- /.col-lg-12 -->
              </div>
              <!-- /.row -->';
    /*$output .= '  
          </div>
          <!-- /.container-fluid -->
      </div>
      <!-- /#page-wrapper -->

    ';*/
    
    return $output;
  }
  
  function getLoginPage(){
    $output = '';
    $output .= '
        <div class="container">
          <div class="row">
            <div class="col-md-4 col-md-offset-4">
              <div class="login-panel panel panel-default">
              
                <div class="panel-heading">
                  <h3 class="panel-title">ВХОД В ПАНЕЛЬ УПРАВЛЕНИЯ</h3>
                </div>
                
                <div class="panel-body">
                  <form role="form" method="POST" action="'.IA_URL.'?auth">
                    <fieldset>
                      <div class="form-group">
                        <input class="form-control" placeholder="login" name="login" type="text" autofocus>
                      </div>
                      <div class="form-group">
                        <input class="form-control" placeholder="Password" name="password" type="password" value="">
                      </div>
                      <div class="checkbox">
                        <label>
                          <input name="remember" type="checkbox" value="remember_me">Запомнить меня
                        </label>
                      </div>
                      <!-- Change this to a button or input when using this as a form -->
                      <button href="index.html" class="btn btn-lg btn-success btn-block">Войти</button>
                    </fieldset>
                  </form>
                </div>
                
              </div>
              
            </div>
          </div>
        </div>
      ';
    
    return $output;
  }
  
  function getIndexContent($output = ''){

    if (!isset($_SESSION["WA_USER"])){
      $output .= $this->getLoginPage();  
    }else {
      
      $this->setContent($this->blankPage());
      $output .= $this->getContent();
      
      $this->dieRights();
      $this->logsWrite();

    }
    
    return $output;
  }

  function getContent($output = ''){
    
    $this->adminHeader = $this->getHeader();
    $this->adminFooter = $this->getFooter(); 
    
    $output .= $this->adminHeader;
    $output .= $this->getMineMenu();
    $output .= '

      <!-- Page Content -->
      <div id="page-wrapper">
          <div class="container-fluid">
              <div class="row">
    ';
    
    $output .= $this->content;
                      
    $output .= ' 
              </div>
              <!-- /.row -->
          </div>
          <!-- /.container-fluid -->
      </div>
      <!-- /#page-wrapper -->
    ';
    $output .= $this->adminFooter;
    
    return $output;
  }
  
  function showAdmin($view = 'index'){
    $this->checkRights();
    
    $this->adminHead = $this->getHead();
    
    switch($view){
      case 'index':
        $this->adminContent = $this->getIndexContent();
      break; 
      
      case 'content':
        if (!isset($_SESSION["WA_USER"])){
          $this->adminContent = $this->getIndexContent();
        }else{
          $this->adminContent = $this->getContent();
        }
      break; 
      
      case '404':
        $this->adminContent = $this->get404Content();
      break; 
    }
    
    $output = '';
    
    $output .= $this->adminDoctype;
    $output .= '
<html lang="ru">
  <head>

';
    $output .= $this->adminCharset.'
    ';
    $output .= '<title>'.$this->adminTitle.'</title>
';
    $output .= '<meta name="description" content="'.$this->adminDescription.'">
';
    $output .= '<meta name="keywords" content="'.$this->adminKeywords.'">

';
    $output .= $this->adminHead;
    $output .= '
  </head>
  <body>';
        $output .= $this->adminContent;
        $output .= $this->adminFooterScripts;
        
        /*$output .= $this->adminHeader;
        $output .= $this->adminContent;
        $output .= $this->adminFooter;*/
        #$output .= $this->adminFooterScripts;
        /*$output .= $this->getMobal();
        $output .= $this->getAdminPanel();*/
        $output .= '
  </body>
</html>
';
    
    return $output;
  }
  
}
