<?
require_once(NX_PATH.'vendors/phpmorphy/phpmorphy_init.php'); // Морфология

class Search{
  
  function __construct(){
    global $PDO;
    $this->pdo = $PDO;
    
    if(!isset($_SESSION['search_q'])){
      $_SESSION['search_q'] = '';
    }
    #pri($_POST);
    
    if(isset($_POST['search_query'])){
      $search_quer = $_POST['search_query'];
      $search_quer = strip_tags($search_quer);
      $search_quer = trim(preg_replace('#[^a-zA-Z0-9а-яёйА-ЯЁЙ]+#ui', ' ', $search_quer));
      #pri($search_quer);
  
      if($search_quer){
        #session_destroy();
        if($search_quer != $_SESSION['search_q']){
          #echo "ne sovpadaet";
          $_SESSION['search_q'] = $search_quer;
        }
      }
    }
    $this->search_query = $_SESSION['search_q'];
  }
  
  function showSearchLine(&$site){
    $output = '';
    
    $output .= '
      <div class="search_query_container" style = "position: relative;">
        <div class="row show_search_box">
          <div class = "col-12 col-md">
            <input type="text" class="search_query form-control" name="q" id = "q_search" placeholder="поиск по каталогу" value="';
    if(isset($_SESSION['search_q'])){
      if($_SESSION['search_q']){
        $output .= $_SESSION['search_q'];
      }
    }
    $output .= '" />
          </div>
          <div class = "col-12 col-md-auto">
            <button class="search_btn btn btn-info"><i class="fas fa-search"></i>&nbsp; Поиск</button>
          </div>
        </div>
        <div class="row">
          <div id="searchPad"></div>
        </div>
      </div>
    ';
    
    $site->js_scripts .= '
    <script type="text/javascript">
      //Страница поиска
    $(document).ready(function() {  
    
      $(".search_query").keypress(function(e){
    	  if(e.keyCode==13){
    	    if($(".search_query").val()){
            var search_query = encodeURI($(".search_query").val());
            $.ajax({
               type: "POST",
               url: "/ajax.php",
               data: "search_query="+search_query,
               success: function(msg){
                if(msg == "ok"){
                  window.location = "/search";
                }
               }
            });
          }
    	  }
    	});
      
      $(".search_btn", this).click(function(){
        var search_query = encodeURI($(".search_query").val());
        $.ajax({
           type: "POST",
           url: "/ajax.php",
           data: "search_query="+search_query,
           success: function(msg){
            if(msg == "ok"){
              window.location = "/search";
            }
           }
         });
      });
    });
    
    
	 $(function(){
			$("#q_search").keyup(function(){
			var q=$(this).val();
			$.post("/ajax.php", {ajax_search:q}).done(function( data ) 
				{
					
					if (data) {$("#searchPad").fadeIn(500).html(data);}
					$("#q_search").focusout(function() {$("#searchPad").fadeOut(500);});
				});
		 });
		 });
    </script>
    <style>
    #searchPad{display:none;background:#eee;width:930px;position:absolute;z-index:2000;margin-top: 0px;padding:5px; left:0;border-radius:0 0 5px 5px;-webkit-box-shadow:0 25px 42px -18px #000;box-shadow:0 25px 42px -18px #000}
    </style>';
    
    return $output;
  }
  
  function showListSearchCompany ($q){
    
    $output = '';
    $img_dir = "company";
    
    $output .= '
      <div class="list_box" style="max-width: 100%; margin-left: 15px; margin-right: 15px; ">      
        <div class="list_card">';
    
    while($r = $q->fetch()){
      extract($r);
      # onclick="return location.href = \'/'.$url.'\'"
      $output .= '
          
          <div class="row list_card_item">
            <div class="col-12 col-md-auto lsc1">
              <div class="list_img_box">';
      if($img)  $output .= '<a href = "/'.$url.'"> <img src="/images/'.$img_dir.'/slide/'.$img.'" title="" alt=" "></a> ';
      $output .= '
              </div>
            </div>
            <div class="col-12 col-md lsc2">
              <p class = "list_title"><a href="/'.$url.'">'.$ownership_type.' "'.$title.'"</a></p>';
      if($inn) $output .= '
              <p><span>ИНН</span> '.$inn.'</p>';
      if($full_name) $output .= '
              <p> '.$full_name.' </p>';
      if($longtxt1) $output .= $longtxt1;
      $output .= '
            </div>
            
            <div class="col-12 col-md-4 lsc3">';
      if($address) $output .= '
              <p><span>Адрес:</span><br>'.$address.'</p>';
      if($phone) $output .= '
              <p><span>Телефон:</span> '.$phone.'<br>';
      if($fax) $output .= '
              <span>Факс:</span> '.$fax.'<br>';
      if($email) $output .= '
              <span>E-mail:</span> '.$email.'<br>';
      if($site) $output .= '
              <span>Сайт:</span> <a href="http://'.$site.'" target = "_blank">'.$site.'</a></p>';
              
              
      $output .= '
            </div>
            
          </div>';
    }
    $output .= '
        </div>
      </div>';
    

    return $output;
      
  }
  
  function showListSearch($q, $img_path = "news"){
    $output = '';
    #$q = $this->pdo->query($s);
    #if($q->rowCount())
    while($r = $q->fetch()){
      extract($r);
      
      $output .= '<div class="anons_line"></div>';
      $output .= '<div class="anons row">';
      $output .= '  <div class="date col-12">';
      if($img){
        $st1 = "col-sm";
      }else{
        $st1 = "col-sm-12";
      }
      if( isset($date) && $date ){
        /*pri($date);
        $date_str = new DateTime($date." 01:00:00");
        $date = $date_str->Format('d.m.Y');*/
        $output .= sqlDateToRusDate($date);
      }
      $output .= '</div>';
      
      $href = "/".$url;
      #$href = Url::getStaticUrlForModuleAndModuleId( DB_PFX.'url', DB_PFX.'news', $id);
      $output .= '<div class="txt '.$st1.' col-12">
                    <div class="txt_title">
                      <a href="'.$href.'"> '.$title.' </a>
                    </div>
                    '.$longtxt1.'<br> 
                    <a href="'.$href.'">Читать дальше</a> 
                  </div>
      ';
      if($img){
        $output .= '<div class="anons_img_box col-sm-auto col-12">';
        $output .= '<img src="/images/'.$img_path.'/slide/'.$img.'" title="" alt=" ">';
        $output .= '</div>';
      }
        
      $output .= '
                </div>
      ';
    }
    
    /*while($r = mysql_fetch_assoc($q)){
      extract($r);
      if($title == '')$title = $longtxt1;
      $output .= '
      <div class="col-xs-12">
        <a href="/'.$url.'" class="p-search-link">'.$title.'</a>
      </div>
      ';
    }*/
    
    return $output;
          
  }
  
  function showSearchItems(&$site){
    global $morphy;
    $output = '';
    $output .= '<div class=" mine_content_box" >';
    $site->bread = '
      <div class="bread_crumbs_box ">
        <div class="bread_crumbs border_top_button_line">
          <a href="/">Главная</a>  → <span>Поиск</span>
        </div>      
      </div>
    ';
    #$output .= $site->bread;
    
    $output .= '<h1 class = "cat_h1" >Поиск</h1>';
    
    if(isset($_SESSION['search_q'])){
      if($_SESSION['search_q']){
        
        //Ведем логи
        $sive_log = false;
        if(!isset($_SESSION['pre_search_q'])){
          $sive_log = true;
        }elseif(  isset($_SESSION['pre_search_q']) && 
                  $_SESSION['pre_search_q'] && 
                  ($_SESSION['pre_search_q'] != $_SESSION['search_q']) 
                ){
          $sive_log = true;
        }
        
        if($sive_log){
          $_SESSION['pre_search_q'] = $_SESSION['search_q'];
          $date_log = date("Y-m-d h:i:s");
          #echo " date_log = $date_log ";
          $ip_log = $_SERVER['REMOTE_ADDR'];
          
          $arr = array(
              'title' => $_SESSION['search_q'],
              'datetime' => $date_log,
              'ip' => $ip_log
          );
          
          $rres = db::insert( DB_PFX.'search_log', $arr, 0);
          #echo $rres;
        }

        $output .= '
        <p><span style = "color:red;">Запрос:</span> '.$_SESSION['search_q'].'</p>
        ';
        
        require_once('vendors/phpmorphy/phpmorphy_init.php');
        
        $searchs = str_word_count($_SESSION['search_q'], 1, "АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя0123456789");
        $arr_sear = array();
        foreach($searchs as $search){
          $sear = mb_strtoupper($search, 'utf-8');
          $collection = $morphy->findWord($sear);
          
          
          if(false === $collection) { 
            #echo $des, " NOT FOUND\n<br>";
            $arr_sear[] = '%'.$sear.'%';
            continue;
          } else {
            
          }

          foreach($collection as $paradigm) {
            #echo "lemma: ", $paradigm[0]->getWord(), "\n<br>";
            $arr_sear[] = '%'.$paradigm[0]->getWord().'%';
            break;
          }
        }
        
        
        #echo "arr_sear = $arr_sear<br>";
        /*echo "<pre>";
        print_r($arr_sear);
        echo "</pre>";*/
        
        
        $orm_search_name_cat = '';
        $orm_search_cat = '';
        
        $orm_search_name_item = '';
        $orm_search_item = '';
        $orm_search_article_item = '';
        $article_item = '';
        $article_provider_item = '';
        
        $orm_search_name_article = $orm_search_article = $orm_search_name_news = $orm_search_news = '';
        
        $i=0;
        foreach ($arr_sear as $a_sear){
          if($i){
            $orm_search_name_cat .= " AND  `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_cat .= " AND  `orm_search`  LIKE  '$a_sear' ";
            
            $orm_search_name_item .= " AND  `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_item .= " AND  `orm_search`  LIKE  '$a_sear' ";
            /*$article_provider_item .= " AND  `article_provider`  LIKE  '$a_sear' ";*/
            
            $orm_search_name_article .= " AND  `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_article .= " AND  `orm_search`  LIKE  '$a_sear' ";
            
            $orm_search_name_news .= " AND  `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_news .= " AND  `orm_search`  LIKE  '$a_sear' ";
          }else{
            $orm_search_name_cat .= " `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_cat .= " `orm_search`  LIKE  '$a_sear' ";
            
            $orm_search_name_item .= " `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_item .= " `orm_search`  LIKE  '$a_sear' ";
            /*$article_provider_item .= " `article_provider`  LIKE  '$a_sear' ";*/
            
            $orm_search_name_article .= " `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_article .= " `orm_search`  LIKE  '$a_sear' ";
            
            $orm_search_name_news .= " `orm_search_name`  LIKE  '$a_sear' ";
            $orm_search_news .= " `orm_search`  LIKE  '$a_sear' ";
          }
          
          $i++;
        }
        
        //Поиск по категориям товаров
        $tbl_url = DB_PFX."url";
        $tbl_goods_cat = DB_PFX."goods_cat";
        $tbl_goods = DB_PFX."goods";
        $s = "
        SELECT `$tbl_goods_cat`.*, `$tbl_url`.`url` 
        FROM  `$tbl_goods_cat` 
        LEFT JOIN `$tbl_url`
        ON (`$tbl_url`.`module` = '$tbl_goods_cat') AND (`$tbl_url`.`module_id` = `$tbl_goods_cat`.`id`)
        WHERE  ( ($orm_search_name_cat) OR ($orm_search_cat) )
        AND `$tbl_goods_cat`.`hide` = 0
        ";
        #echo $s."<br>";
        /*if($q = $this->pdo->query($s)){
          if($q->rowCount()){
            $output .= '<h3>Разделы товаров</h3>';
            $output .= '<div class="row">';
            $output .= $this->showListSearch($q, "goods/cat");
            $output .= '</div><br><br>';
          }
        }*/
        
        if($q = $site->pdo->query($s)){
          if( $q->rowCount()){
            $output .= '
              <h1 class="c_h1">Разделы товаров</h1>
              <div class = "m_catalog">
                <div class="catalog_dir card-deck">
            '; 
            while($r = $q->fetch()){
              ($r['img']) ? $image = '/images/goods/cat/slide/'.$r['img'] : $image = '/css/img/nofoto.gif' ;
              $output .= '
                <div class="card">
                  <a href="/'.$r['url'].'">
                    <div class="card_img_box">  
                      <img class="" src = "'.$image.'" alt = "Изображение - '.$r['title'].'"   title = "'.$r['title'].'">
                      <div class="card_img_shadow"></div>
                    </div>
                  </a> 
                  <div class="card-body">
                  </div>
                  <div class="card-footer">
                    <p class="card-title"><a href="/'.$r['url'].'">'.$r['title'].'</a></p> 
                  </div>
                </div>
                ';
              
            }
            $output .= '
                </div>
              </div>
            ';
          }
        }
        
        //Поиск по товарам
        
        $s = "
        SELECT COUNT( * ) AS count
        FROM  `$tbl_goods`
        WHERE (
          ($orm_search_name_item)
          OR
          ($orm_search_item) 
        ";
        /*  OR
          ($article_item) 
          OR
          ($article_provider_item) */
        $s .= "  
        )
        AND hide = 0
        
        ";
        
        //echo $s;
        
        $q = $site->pdo->query($s);
        $r = $q->fetch();
        $filter_count = $r['count'];
        
        $pagerPage = 0;
        if(isset($_GET['page'])){
          if($_GET['page']){
            $pagerPage = intval($_GET['page']);
            //echo "pagerPage = $pagerPage<br>";
          }
        }
        
        $limit = '';
        
        $strPager = Article::getPager($site, $pagerPage, $filter_count, 20, $limit);
        
        
        
        $s = "
        SELECT `$tbl_goods`.*, `$tbl_url`.`url` 
        FROM  `$tbl_goods`
        LEFT JOIN `$tbl_url`
        ON (`$tbl_url`.`module` = '$tbl_goods') AND (`$tbl_url`.`module_id` = `$tbl_goods`.`id`)
        WHERE (
          ($orm_search_name_item)
          OR
          ($orm_search_item) 
        ";
        /*
          OR
          ($article_item) 
          OR
          ($article_provider_item) 
        */
        $s .= "
        )
        
        AND `$tbl_goods`.`hide` = 0
        
        ORDER BY `$tbl_goods`.`img` DESC
        $limit
        ";
        //echo "s = $s<br>";
        
        if($filter_count){
          $output .= '<h3>Товары</h3>';
          $s_all_count = "SELECT COUNT(*) AS count FROM `$tbl_goods` WHERE `hide` = 0 ";
          $q_all_count = $site->pdo->query($s_all_count);
          $r_all_count = $q_all_count->fetch();
          $all_count = $r_all_count['count'];
          
          #$output .= '<p> Найдено позиций: <span style="color:red;">'.$filter_count.'</span> из <span style="color:red;">'.$all_count.'</span> </p>';
          
          //$output .= Catalogue::show_rub_pager($filter_count, $filter_pager_page, $filter_pager_per_page);
        
          $output .= $strPager;
          $output .= '
            <div class="catalog">
          ';
          $output .= Goods::show_catalog_items($site, $s, $filter_count );
          $output .= '</div>';
          $output .= $strPager;
          //$output .= Catalogue::show_rub_pager($filter_count, $filter_pager_page, $filter_pager_per_page);
        }
        
        //Поиск по новостям
        $tbl_news = DB_PFX."news";
        $s = "
        SELECT `$tbl_news`.*, `$tbl_url`.`url` 
        FROM  `$tbl_news` 
        LEFT JOIN `$tbl_url`
        ON (`$tbl_url`.`module` = '$tbl_news') AND (`$tbl_url`.`module_id` = `$tbl_news`.`id`)
        WHERE  ( ($orm_search_name_news) OR ($orm_search_news) )
        AND `$tbl_news`.`hide` = 0
        ORDER BY `$tbl_news`.`date` DESC
        "; #pri($s);
                  
        if($q = $this->pdo->query($s)){
          if($q->rowCount()){
          
            $output .= '<h3>Новости</h3>';
            $output .= '<div class="">';
            $output .= $this->showListSearch($q);
            $output .= '</div>';
          }
        }
      
        //Поиск по категориям статей
        $tbl_articles_cat = DB_PFX."articles_cat";
        $tbl_articles = DB_PFX."articles";
        $s = "
        SELECT `$tbl_articles_cat`.*, `$tbl_url`.`url` 
        FROM  `$tbl_articles_cat` 
        LEFT JOIN `$tbl_url`
        ON (`$tbl_url`.`module` = '$tbl_articles_cat') AND (`$tbl_url`.`module_id` = `$tbl_articles_cat`.`id`)
        WHERE  ( ($orm_search_name_article) OR ($orm_search_article) )
        AND `$tbl_articles_cat`.`link` = ''
        AND `$tbl_articles_cat`.`hide` = 0
        ORDER BY `$tbl_articles_cat`.`id` DESC
        "; #pri($s);
        
        if($q = $this->pdo->query($s)){
          if($q->rowCount()){
              $output .= '<h3>Разделы статей</h3>';
              $output .= '<div class="">';
              $output .= $this->showListSearch($q, "articles");
              $output .= '</div>';
          }
        }
        
        //Поиск по статьям
        $s = "
        SELECT `$tbl_articles`.*, `$tbl_url`.`url` 
        FROM  `$tbl_articles` 
        LEFT JOIN `$tbl_url`
        ON (`$tbl_url`.`module` = '$tbl_articles') AND (`$tbl_url`.`module_id` = `$tbl_articles`.`id`)
        WHERE  ( ($orm_search_name_article) OR ($orm_search_article) )
        AND `$tbl_articles`.`hide` = 0
        ORDER BY `$tbl_articles`.`id` DESC
        "; #pri($s);
        
        if($q = $this->pdo->query($s)){
          if($q->rowCount()){
              $output .= '<h3>Cтатьи</h3>';
              $output .= '<div class="">';
              $output .= $this->showListSearch($q, "articles");
              $output .= '</div>';
          }
        }
        

       
      }else{
        $output .= "Задан пустой поисковый запрос";
      }
      
    }else{
      $output .= "Задан пустой поисковый запрос";
    }
    
    $output .= '</div>';
    
    return $output;  
    
  }
  
}

