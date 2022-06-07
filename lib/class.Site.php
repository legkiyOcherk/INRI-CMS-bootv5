<?php
require_once('lib/class.SiteBase.php');  
 
class SiteСutaway extends SiteBase{
  
}

class SiteCorporate extends SiteBase{
  
  function getMineTopMenu(){
    $output = '';
    
    $output .= '
          <!-- top_menu -->
          <div class="top_menu_box">
            <div class="top_menu">
            
              <nav class= "navbar navbar-expand-sm navbar-dark bg-dark">
                <a class="navbar-brand d-sm-none" href="#">Меню</a>
                <button class="navbar-toggler" type="button" 
                  data-toggle="collapse" 
                  data-target="#navbarsTop" 
                  aria-controls="navbarsTop" 
                  aria-expanded="false" 
                  aria-label="Toggle navigation">
                  <!--<span class="navbar-toggler-icon"></span>-->
                  <i class="fa fa-bars fa-lg" title="Toggle navigation"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarsTop">
                  
                  <ul class="navbar-nav mr-auto">';
    $output .= Article::show_head_chief_menu2($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url'); # Меню с выподашкой cat_articles
    #$output .= Article::show_head_chief_menu($this); # Меню cat_articles
    #$output .= Article::show_simple_menu($this);     # Меню smpl_article
    $output .= '
                  </ul>

                </div>
              </nav>
              
            </div>
          </div>
          <!-- End top_menu -->
    ';
    
    $output = $this->addEditAdminLink($output, IA_URL.'article.php');
    
    return $output;
  }
  
  function getFooter(){
    $output = '';
        
    $output .= '
      </div>
    </div>
    ';
    
    $output .= '
    <!-- footer -->
    <div class="footer_box">
      <div class="footer">
        
        <div class="row">
          <div class="col-12 footer_menu_box">
            <ul class="footer_menu ">';
    $output .= Article::show_footer_menu($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url');
    #$output .= Article::show_simple_menu($this);
    $output .= '
            </ul>
          </div>';
    
    if($this->soc_net){
      $output .= '
          <div class="col-12 soc_net_box">
            <div class = "soc_net">'.$this->soc_net.'</div>
          </div>';
    }
          
    $output .= '      
        </div>';
    if( isset($this->phone_header) && $this->phone_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->phone_header.'
          </div>
        </div>';
    }
    if( isset($this->adress_header) && $this->adress_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->adress_header.'
          </div>
        </div>';
    }
    $output .= '
      </div>
    </div>
    <!-- End footer -->';
    
    
    return  $output;
  }
  
  function getContent(){
    $output = '';
    $flIsProduction = false;
    $left_menu = false;
    $cont = '';
    #pri($this); 
    switch($this->module){
      
      case DB_PFX.'articles_cat':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?editc=".$this->module_id;
          $_SESSION['articles']['c_id'] = $this->module_id;
        }
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getCatItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
        
      case DB_PFX.'articles':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?edits=".$this->module_id;
          $_SESSION['articles']['c_id'] = db::value("cat_id", DB_PFX."articles", "id = ".$this->module_id );
        }        
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
       
      case DB_PFX.'news':
        $this->adminLink = "/news.php";
        if($this->module_id) $this->adminLink .= "?edits=".$this->module_id;
        
        $cont = News::getNews($this, $this->module_id, DB_PFX."news");
        $cont = $this->getContentPrefix(false).$cont.$this->getContentPostfix(false);
        break;
        
      case 'search':
        $cont = $this->search->showSearchItems($this);
        $cont = $this->getContentPrefix().$cont.$this->getContentPostfix();
        break;
        
      case 'backup_sql':
        echo 'backup_sql';
        $GLOBALS['DATE_UPDATE'] = date("Y-m-d H:i:s");
        self::backup();
        die();
        break;
      
      case 'robots_txt':
        echo 'robots.txt';
        die(); 
        break; 
      
      case '404':
        header("HTTP/1.0 404 Not Found");
        header("Location: /404.php"); 
        break; 
        
      default:
        $this->module = "index";
        $this->module_id = 0;
        $output .= self::getIndexContent();   
    }
    
    if($cont){
      $output = $this->getInnerContent($cont);
    }
    
    
    return $output;
  }
  
 
}

class SiteOnlineshop extends SiteBase{
  
  var $search;
  
  function __construct($module = '404') {
    parent::__construct($module);
    
    $this->_BASKET = new Basket();
    $this->search = new Search();
    
    
    
    $this->js_scripts .= '<script type="text/javascript" src="/js/store1.js"></script>';
    $this->js_scripts .= '
    <link rel="stylesheet" href="/vendors/iCheck/skins/all.css">
    <script src="/vendors/iCheck/icheck.min.js"></script>
    <script>
    $(document).ready(function(){
      $(".сonsent_checkbox").iCheck({
        checkboxClass: "icheckbox_square-blue",
        radioClass: "iradio_square-blue"
      });
      $(".dost_radio").iCheck({
        checkboxClass: "icheckbox_square-blue",
        radioClass: "iradio_square-blue"
      });
    });
    </script>';
  }
  
  function getMineHeader(){
    $output = '';
    
    $output .= '
          <!-- header -->
          <div class = "header_box">
            <div class = "header">
            
              <div class="row align-items-center">
              
                <div class="col-12 col-sm-4 col-lg-auto">
                  <div class="logo_box">
                    <a href="/"><img class="logo" src="'.$this->logo_path.'" alt="'.$this->site_slogan.'" title="'.$this->site_slogan.'"></a>
                  </div>
                </div>
                
                <div class="col-12 col-sm-4 col-lg">
                  <div class="header_phone_box">
                    <div class="header_phone">
                      '.$this->phone_header.'
                    </div>
                    <div class="header_adress">
                      '.$this->adress_header.'
                    </div>
                    <div class="header_work">
                      '.$this->working_hour.'
                    </div>
                  </div>
                </div>
                
                <div class="col-12 col-sm-4 col-lg-auto">
                  <div class="header_callback_box">
                    <div class="header_callback">
                      <button class="btn flmenu1" data-id="0" data-target="#myModal" data-title="Заказать обратный звонок" data-toggle="modal">Заказать обратный звонок</button>
                    </div>
                    <div class="header_soc">
                      '.$this->soc_net.'
                    </div>
                  </div>
                </div>';
      
    $basked_data = $this->_BASKET->get_basket_data();
    $style = '';
    #( $this->_BASKET->getCount() ) ? $style = "" : $style = "display: none;" ;
    $output .= '
                <div class="col-12 col-sm-4 col-lg-auto">
                
                  <!-- basked -->
                  <div class="basked_box" style = "'.$style.'">
                    <a href="/basket">
                      <div class="basked_icon">
                        <i class="fas fa-cart-arrow-down" aria-hidden="true"></i>
                      </div>
                      <div class="basked">
                        '.$basked_data['basket_head'].'
                      </div>
                    </a>
                  </div>
                  <!-- End basked -->
                  
                </div>
    ';
    
    $output .= '
              </div>
              
            </div>
          </div>
          <!-- End header -->';
          
    $output = $this->addEditAdminLink($output, IA_URL.'config.php');
    
    return $output;
  }
  
  function getMineTopMenu(){
    $output = '';
    
    $output .= '
          <!-- top_menu -->
          <div class="top_menu_box">
            <div class="top_menu">
            
              <nav class= "navbar navbar-expand-sm navbar-dark bg-dark">
                <a class="navbar-brand d-sm-none" href="#">Меню</a>
                <button class="navbar-toggler" type="button" 
                  data-toggle="collapse" 
                  data-target="#navbarsTop" 
                  aria-controls="navbarsTop" 
                  aria-expanded="false" 
                  aria-label="Toggle navigation">
                  <!--<span class="navbar-toggler-icon"></span>-->
                  <i class="fa fa-bars fa-lg" title="Toggle navigation"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarsTop">
                  
                  <ul class="navbar-nav mr-auto">';
    $output .= Article::show_head_chief_menu2($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url'); # Меню с выподашкой cat_articles
    #$output .= Article::show_head_chief_menu($this); # Меню cat_articles
    #$output .= Article::show_simple_menu($this);     # Меню smpl_article 
    $output .= '
                  </ul>

                </div>
              </nav>
              
            </div>
          </div>
          <!-- End top_menu -->
    ';
    
    $output = $this->addEditAdminLink($output, IA_URL.'article.php');
    
    return $output;
  }
  
  function getMineCatGoods($text = null){
    $output = '';
    
    $output .= '
      <div class="block_box mine_goods_cat">
        <div class="block">
          '.$text.'
          '.Goods::show_mine_goods_cat($this).'
       </div>
      </div>';
      
      $output = $this->addEditAdminLink($output, IA_URL.'goods.php?c_id=root');
      
    return $output; 
  }
  
  function getSearchBox(){
    $output = '';
    
    $output .= '
    <div class="block_box mine_search_line">
      <div class="block">';
    $output .= $this->search->showSearchLine($this);
    $output .= '
      </div>
    </div>';
    $output = $this->addEditAdminLink($output, IA_URL.'mine_block.php');
    return $output;
  }
  
  function getMineGoods(){
    $output = '';
    
    $output .= '
    <div class="block_box">
      <div class="block">';
    $output .= Goods::show_mine_goods($this, 1);
    $output .= '
      </div>
    </div>';
    
    $output = $this->addEditAdminLink($output, IA_URL.'mine_block.php');
    return $output;
  }
  
  function getFooter(){
    $output = '';
        
    $output .= '
      </div>
    </div>
    ';
    
    $output .= '
    <!-- footer -->
    <div class="footer_box">
      <div class="footer">
        
        <div class="row">
          <div class="col-12 footer_menu_box">
            <ul class="footer_menu ">';
    $output .= Article::show_footer_menu($this, DB_PFX.'articles_cat', DB_PFX.'articles', DB_PFX.'url');
    #$output .= Article::show_simple_menu($this);
    $output .= '
            </ul>
          </div>';
    
    if($this->soc_net){
      $output .= '
          <div class="col-12 soc_net_box">
            <div class = "soc_net">'.$this->soc_net.'</div>
          </div>';
    }
          
    $output .= '      
        </div>';
    if( isset($this->phone_header) && $this->phone_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->phone_header.'
          </div>
        </div>';
    }
    if( isset($this->adress_header) && $this->adress_header ){
      $output .= '
        <div class="row">
          <div class="col-12 tac">
            '.$this->adress_header.'
          </div>
        </div>';
    }
    $output .= '
      </div>
    </div>
    <!-- End footer -->';
    
    
    return  $output;
  }
  
  function getContent(){
    $output = '';
    $flIsProduction = false;
    $left_menu = false;
    $cont = '';
    #pri($this); 
    switch($this->module){
      
      case DB_PFX.'goods_cat':
        $this->adminLink = "/goods.php";
        if($this->module_id){
          $this->adminLink .= "?editc=".$this->module_id;
          $_SESSION['goods']['c_id'] = db::value("cat_id", DB_PFX.'goods', "id = ".$this->module_id );
        }
        
        $this->cat_arr = db::select('id, parent_id, title, hide', DB_PFX.'goods_cat');
        $cont = Goods::show_cat_items($this, $this->module_id, DB_PFX.'goods_cat', DB_PFX.'goods');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
        
      case DB_PFX.'goods':
        $this->adminLink = "/goods.php";
        if($this->module_id){
          $this->adminLink .= "?edits=".$this->module_id;
          $_SESSION['goods']['c_id'] = db::value("cat_id", DB_PFX.'goods', "id = ".$this->module_id );
        }
        
        $this->cat_arr = db::select("id, parent_id, title, hide", DB_PFX.'goods_cat');
        $item = db::row('*', DB_PFX.'goods', "id = ".$this->module_id);
        $cont = Goods::show_item_full($this, $item);
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
      
      case DB_PFX.'articles_cat':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?editc=".$this->module_id;
          $_SESSION['articles']['c_id'] = $this->module_id;
        }
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getCatItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
        
      case DB_PFX.'articles':
        $this->adminLink = "/articles.php";
        if($this->module_id){
          $this->adminLink .= "?edits=".$this->module_id;
          $_SESSION['articles']['c_id'] = db::value("cat_id", DB_PFX."articles", "id = ".$this->module_id );
        }        
        (isset($this->left_menu) && $this->left_menu) ? $left_menu = true : $left_menu = false;
        
        $cont = Article::getItems($this, $this->module_id, DB_PFX.'articles_cat', DB_PFX.'articles');
        $cont = $this->getContentPrefix($left_menu).$cont.$this->getContentPostfix($left_menu);
        break;
       
      case DB_PFX.'news':
        $this->adminLink = "/news.php";
        if($this->module_id) $this->adminLink .= "?edits=".$this->module_id;
        
        $cont = News::getNews($this, $this->module_id, DB_PFX."news");
        $cont = $this->getContentPrefix(false).$cont.$this->getContentPostfix(false);
        break;
        
      case 'search':
        $cont = $this->search->showSearchItems($this);
        $cont = $this->getContentPrefix().$cont.$this->getContentPostfix();
        break;
      
      case 'basket':
        $this->adminLink = "/orders.php";
        $this->siteTitle = "Корзина - ".$_SERVER['HTTP_HOST'];
        $cont = '';
        $cont .= '<div  style="margin-top: 10px;" id = "basket_ajx_box">';
        $this->bread = '
            <div class="bread_crumbs_box ">
              <div class="bread_crumbs border_top_button_line">
                <a href="/">Главная</a> → <span>Корзина</span>
              </div>      
            </div>
        ';
        #pri($_SESSION);
        #$cont .= $this->bread;
        
        $cont .= '<h1>Корзина</h1>';
        $cont .= '<div id = "basket_ajx">';
    	  $cont .= $this->_BASKET->show_basket();
        $cont .= '</div>';
        $cont .= '<br/><br/>';
        $cont .= $this->_BASKET->show_pre_order();
        $cont .= '</div>';
        
        $cont = $this->getContentPrefix(false).$cont.$this->getContentPostfix(false);
        
        break;
      
      case 'xlsorder':
        echo Basket::xls_order($this);
        die();
        break;
       
      case 'backup_sql':
        echo 'backup_sql';
        $GLOBALS['DATE_UPDATE'] = date("Y-m-d H:i:s");
        self::backup();
        die();
        break;
      
      case 'robots_txt':
        echo 'robots.txt';
        die(); 
        break; 
      
      case '404':
        header("HTTP/1.0 404 Not Found");
        header("Location: /404.php"); 
        break; 
        
      default:
        $this->module = "index";
        $this->module_id = 0;
        $output .= self::getIndexContent();   
    }
    
    if($cont){
      $output = $this->getInnerContent($cont);
    }
    
    
    return $output;
  }
  
  function getBlockSwitchSelector($s, $cont = ''){
    $output = '';
    
    if($q = $this->pdo->query($s))
      if($q->rowCount())
        while($r = $q->fetch()){
          $output .= '
    <a name="'.$r['url'].'"></a>';
          
          switch ($r['link']) {
            case  'block_mine_header':
                  $output .= $this->getMineHeader(); break;
            case  'block_mine_top_menu':
                  $output .= $this->getMineTopMenu(); break;
            case  'block_mine_slider':
                  $output .= $this->getMineSlider(); break;
            case  'block_mine_news':
                  $output .= $this->getMineNews(); break;
            case  'block_search':
                  $output .= $this->getSearchBox(); break;
            case  'block_mine_cat_goods':
                  $output .= $this->getMineCatGoods($r['longtxt2']); break;
            case  'block_mine_goods':
                  $output .= $this->getMineGoods(); break;
            case  'block_ferrum_form':
                  $output .= $this->getMineFerrumForm(); break;
            case  'block_mine_footer':
                  $output .= $this->getFooter(); break;
                  
            case  'block_inner_content': // Контент на внутренних страницах
                  $output .= $this->addEditAdminLink($cont, '/'.ADM_DIR.$this->adminLink); 
                  break;
                  
            default:
                  if($r['longtxt2']){
                    $output .= $this->addEditAdminLink($r['longtxt2'], IA_URL.'mine_block.php?edits='.$r['id']);
                  }
                  break; 
          }
          
        }
    
    return $output;
  }
  
  
  
} 

switch(SITE_TYPE){
  
  case 'CUTAWAY':
    class Site extends SiteСutaway{};
    break;
  
  case 'CORPORATE':
    class Site extends SiteCorporate{};
    break;
    
  case 'ONLINESHOP':
    class Site extends SiteOnlineshop{};
    break;
  
  default:
    die('Не задан тип сайта');
    break;
}

