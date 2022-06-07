<?
require_once "class.AdminLTE.php";

class AdminCutaway extends AdminLTEextends{
  
  function getMineMenuListArr(){
     
    $this->mainmenu = array(	
      "index.php"              => ADMIN_NAME,
      "smpl_article.php"       => "Содержание сайта",
      "mine_block.php"         => "Главная страница",
      
      #---------------------------------------------------------
      "delimiter1"             => "Блоки на главной странице",
      #---------------------------------------------------------
      "carusel.php"            => "Слайдер на главной",
      #---------------------------------------------------------
      "delimiter2"             => "Администрирование",
      #---------------------------------------------------------
      "reservations.php"       => "Заявки",
      "config.php"             => "Настройки",
      "seo.php"                => "SEO Настройки",
      "design.php"             => "Оформление сайта",
      
      #---------------------------------------------------------
      "delimiter3"             => "Система",
      #---------------------------------------------------------
      "all_files.php"          => "Файлы",
      "all_images.php"         => "Изображения",
      "all_log.php"            => "Логи",
      "admin_logs.php"         => "Логи входа в панель", 
      "url.php"                => "ЧПУ",
      
		);
  }
  
  function setMineMenuAddData(){
    foreach ($this->mainmenu as $k=>$v){
      switch ($v) {
          case ADMIN_NAME:
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-dashboard"></i>',
                  'short_decr' => 'Начальный экран'
                ); break;
                
          case 'Статьи':
          case 'Содержание сайта':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-list-ul" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Главная страница':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-university" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Слайдер на главной':
          case 'Изображения':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-picture-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Партнеры':
          case 'Блок контакты':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-handshake-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Настройки':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-gear" aria-hidden="true"></i> ',
                  'short_decr' => ''
                ); break; 
                
          case 'Оформление сайта':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-paint-brush" aria-hidden="true"></i>',
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
                
          case 'Логи входа в панель':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-exchange" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;      
                
          case 'ЧПУ':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-link" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;      
                
          case 'Пользователи':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-user"></i>',
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
        
		);

  	if (isset($user["iscontent"])    && $user["iscontent"])	    $this->current_rights[]="iscontent";
    if (isset($user["is_programmer"])&& $user["is_programmer"])	$this->current_rights[]="is_programmer";
  	if (isset($user["ismanag"])      && $user["ismanag"])	      $this->current_rights[]="ismanag";
  	if (isset($user["iscatalog"])    && $user["iscatalog"])	    $this->current_rights[]="iscatalog";
  	if (isset($user["isjournalist"]) && $user["isjournalist"])	$this->current_rights[]="isjournalist";

  	$check=str_replace( IA_URL, "", $_SERVER["PHP_SELF"] );
    
    $this->user = $user;
    $this->check = $check;
    
  }
  
}

class AdminCorporate extends AdminLTEextends{
  
  function getMineMenuListArr(){
    
    $this->mainmenu = array(
      "index.php"              => ADMIN_NAME,
      "mine_block.php"         => "Главная страница",
      "articles.php?c_id=root" => "Содержание сайта",
      "news.php"               => "Новости",
      
      #---------------------------------------------------------
      "delimiter1"             => "Блоки на главной странице",
      #---------------------------------------------------------
      "carusel.php"            => "Слайдер на главной",
      #---------------------------------------------------------
      "delimiter2"             => "Администрирование",
      #---------------------------------------------------------
      "reservations.php"       => "Заявки",
      "config.php"             => "Настройки",
      "seo.php"                => "SEO Настройки",
      "design.php"             => "Оформление сайта",
      
      #---------------------------------------------------------
      "delimiter3"             => "Система",
      #---------------------------------------------------------
      "all_files.php"          => "Файлы",
      "all_images.php"         => "Изображения",
      "all_log.php"            => "Логи",
      "admin_logs.php"         => "Логи входа в панель", 
      "url.php"                => "ЧПУ",
      
      /*""=>"---- --- --- ----",
      "search_log.php"         => "Лог поиска",
      */
          
		);
  }

  function setMineMenuAddData(){
    foreach ($this->mainmenu as $k=>$v){
      switch ($v) {
          case ADMIN_NAME:
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-dashboard"></i>',
                  'short_decr' => 'Начальный экран'
                ); break;
                
          case 'Статьи':
          case 'Содержание сайта':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-list-ul" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Главная страница':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-university" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Новости':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-newspaper-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Слайдер на главной':
          case 'Изображения':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-picture-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Партнеры':
          case 'Блок контакты':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-handshake-o" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Настройки':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-gear" aria-hidden="true"></i> ',
                  'short_decr' => ''
                ); break; 
                
          case 'Оформление сайта':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-paint-brush" aria-hidden="true"></i>',
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
                
          case 'Логи входа в панель':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-exchange" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;      
                
          case 'ЧПУ':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-link" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;      
                
          case 'Пользователи':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-user"></i>',
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
  
  function checkRights(){
    
    $user = array();
    if(isset($_SESSION["WA_USER"]))
      $user = $_SESSION["WA_USER"];
    
    $this->scripts=array(	
			"index.php"         => array("iscontent","is_programmer","ismanag","iscatalog","isjournalist"),
      "search.php"        => array("iscontent","is_programmer","ismanag","iscatalog","isjournalist"),
      "articles.php"      => array("ismanag", "iscontent"),
      "news.php"          => array("ismanag", "iscontent"),
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
        
		);

  	if (isset($user["iscontent"])    && $user["iscontent"])	    $this->current_rights[]="iscontent";
    if (isset($user["is_programmer"])&& $user["is_programmer"])	$this->current_rights[]="is_programmer";
  	if (isset($user["ismanag"])      && $user["ismanag"])	      $this->current_rights[]="ismanag";
  	if (isset($user["iscatalog"])    && $user["iscatalog"])	    $this->current_rights[]="iscatalog";
  	if (isset($user["isjournalist"]) && $user["isjournalist"])	$this->current_rights[]="isjournalist";

  	$check=str_replace( IA_URL, "", $_SERVER["PHP_SELF"] );
    
    $this->user = $user;
    $this->check = $check;
    
  }
   
}

class AdminOnlineshop extends AdminLTEextends{
  
  function getMineMenuListArr(){
    
    $this->mainmenu = array(	
      "index.php"              => ADM_DIR,
      "orders.php"             => "Заказы",
      "mine_block.php"         => "Главная страница",
      "articles.php?c_id=root" => "Содержание сайта",     
      "goods.php?c_id=1"       => "Каталог товаров", 
      "goods.php?view_tree"    => "Дерево всех категорий", 
      "news.php"               => "Новости",
      
      #---------------------------------------------------------
      "delimiter1"             => "Блоки на главной странице",
      #---------------------------------------------------------
      "carusel.php"            => "Слайдер на главной",
      #---------------------------------------------------------
      "delimiter2"             => "Справочники",
      #---------------------------------------------------------
      "availability.php"       => "Варианты наличия",
      "country.php"            => "Страны",
      "brand.php"              => "Бренд",
      "units.php"              => "Ед. измерения",
      #---------------------------------------------------------
      "delimiter3"             => "Администрирование",
      #---------------------------------------------------------
      #"orders.php"            => "Корзина",
      "reservations.php"       => "Заявки",
      "search_log.php"         => "Логи поиска",
      "config.php"             => "Настройки",
      "seo.php"                => "SEO Настройки",
      "design.php"             => "Оформление сайта",
      
      #---------------------------------------------------------
      "delimiter4"             => "Система",
      #---------------------------------------------------------
      "all_files.php"          => "Файлы",
      "all_images.php"         => "Изображения",
      "all_log.php"            => "Логи",
      "url.php"                => "ЧПУ",
      
      /*""=>"---- --- --- ----",
      "search_log.php"         => "Лог поиска",
      */
          
		);
    
    $this->adminFooterScripts .= '
      <style>
        .sidebar-menu > li > a {
          padding: 7px 5px 7px 15px;
          display: block;
        }
      </style>
    ';
  }
  
  function setMineMenuAddData(){
    foreach ($this->mainmenu as $k=>$v){
      switch ($v) {
          case ADMIN_NAME:
          case ADM_DIR:
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-dashboard"></i>',
                  'short_decr' => 'Начальный экран'
                ); break;
                
          case 'Главная страница':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-university" aria-hidden="true"></i>',
                  'short_decr' => ''
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
          case 'Каталог товаров':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-folder-open-o"></i>',
                  'short_decr' => ''
                ); break;
          
          case 'Полный каталог':
          case 'Дерево всех категорий':
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
                
          case 'Логи поиска':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-search" aria-hidden="true"></i> ',
                  'short_decr' => ''
                ); break;
                
          case 'Настройки':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-gear" aria-hidden="true"></i> ',
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
                  'icon' => '<i class="fa fa-gear"></i>',
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
          
          case 'Заказы':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-bullhorn" aria-hidden="true"></i>',
                  'short_decr' => ''
                ); break;
                
          case 'Пользователи':
              $this->mainmenu_add_data[$v] = 
                array(
                  'icon' => '<i class="fa fa-user"></i>',
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
        
		);

  	if (isset($user["iscontent"])    && $user["iscontent"])	    $this->current_rights[]="iscontent";
    if (isset($user["is_programmer"])&& $user["is_programmer"])	$this->current_rights[]="is_programmer";
  	if (isset($user["ismanag"])      && $user["ismanag"])	      $this->current_rights[]="ismanag";
  	if (isset($user["iscatalog"])    && $user["iscatalog"])	    $this->current_rights[]="iscatalog";
  	if (isset($user["isjournalist"]) && $user["isjournalist"])	$this->current_rights[]="isjournalist";

  	$check=str_replace( IA_URL, "", $_SERVER["PHP_SELF"] );
    
    $this->user = $user;
    $this->check = $check;
    
  }
  
}

require_once('../define.php');
switch(SITE_TYPE){
  case 'CUTAWAY':
    class Admin extends AdminCutaway{};
    break;
  
  case 'CORPORATE':
    class Admin extends AdminCorporate{};
    break;
    
  case 'ONLINESHOP':
    class Admin extends AdminOnlineshop{};
    break;
  
  default:
    die('Не задан тип сайта');
    break;
}
