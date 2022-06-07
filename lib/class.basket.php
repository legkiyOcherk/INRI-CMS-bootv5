<?
class Basket {

	private $user_id = false;
	private $basket_id = 0;
	private $count = 0;
	private $price = 0;
	private $time_life_cookie = 14400; // 120min
	private $charset_ajx = 'utf-8';
  
  private $user;
  private $eur;
  private $usd;
  
  private $tbl_goods;
  private $tbl_goods_cat;
  private $tbl_url;
  private $logo_path;
  
	private $debug = false;

	function __construct($create = false, $update = true) {
		$this->user_id = self::Authorization();
		$this->start_session($create, $update);
    //($db_ye = db::value( 'val', 'config', 'name = "ye"')) ? $this->ye = $db_ye : $this->ye = 10;
    $this->eur = db::value( 'val', DB_PFX.'currency', 'name = "eur"');
    $this->usd = db::value( 'val', DB_PFX.'currency', 'name = "usd"');
    
    $this->tbl_goods = DB_PFX.'goods';
    $this->tbl_goods_cat = DB_PFX.'goods_cat';
    $this->tbl_url = DB_PFX.'url';
    if(!$this->logo_path     = db::value("value", DB_PFX."design", "type = 'user_logo'")){
      $this->logo_path       = '/css/img/logo2.png';
    }
    
		if ($this->basket_id) {
			$this->count = db::value("SUM(amount)", DB_PFX.'basket_items', "basket_id = $this->basket_id");
		}
    
    /*if(isset($_SESSION['personal_user_id']))
      if($_SESSION['personal_user_id'])
        $this->user = db::row("*", DB_PFX."personal_accaunt", "`id` = '".$_SESSION['personal_user_id']."'");
      */  
    
	}
	private function start_session($create = false, $update = true) {
		$last_time = time();
		if (isset($_COOKIE['basket_id'])) {
			$this->basket_id = $_COOKIE['basket_id'];
			$isset = db::value('id', DB_PFX.'basket', "id = $this->basket_id");
			if (!$isset and $create) {
				$this->basket_id = $this->create_basket($last_time);
			}
			db::update(DB_PFX.'basket', compact('last_time'), "id = $this->basket_id");
		} elseif ($create) {
			$this->basket_id = $this->create_basket($last_time);
		}

		if ($this->basket_id and $update) {
			setcookie("basket_id", $this->basket_id, time() + $this->time_life_cookie);
		}
	}


	function show_head() {
		$str = $this->get_basket_data();
    return $str['basket_head'];
		#echo "<div id=\"head_basket\"$style><p>{$str['basket_head']}</p></div>";
	}
  function getCount() {
    return $this->count;
  }

	function getPage() {
		if (!$_POST['act']) return;
		$output = array();
		switch ($_POST['act']) {
			case 'add':
				$output = $this->add_basket_item();
				break;

			case 'get':
				$output = $this->get_basket_data();
				break;

			case 'change_amount':
				header("Content-Type: text/html; charset=$this->charset_ajx");
				if (isset($_POST['id']) and isset($_POST['amount'])) {
					$id = $_POST['id'];
					$amount = floatval($_POST['amount']);
          $this->change_amount($id, $amount);
				}
				echo $this->get_basket();
				break;

			case 'order':
				$this->send_order();
				break;

			case 'delete_item':
				header("Content-Type: text/html; charset=$this->charset_ajx");
				echo $this->delete_item();
				break;

			case 'login':
				header("Content-Type: text/html; charset=$this->charset_ajx");
				$this->Login();
				break;
		}
		if (count($output)) {
			/**/
			header('Content-Type: text/x-json; charset=UTF-8');
			$output2 = array();
			foreach ($output as $k=>$v) {
				/*$k2 = iconv('cp1251', 'utf-8', $k);
				$v2 = iconv('cp1251', 'utf-8', $v);
				$output2[$k2] = $v2;*/
        $output2[$k] = $v;
			}
			echo json_encode($output2);
		}
	}

	
	function set_state() {
		if (!$this->user_id) return;
		$basket_id = intval($_POST['basket_id']);
		if (!$basket_id) return;
		db::q("UPDATE basket SET `state` = 0 WHERE user_id = {$this->user_id} AND id <> $basket_id");
		db::update('basket', array('state'=>1), "id = $basket_id");
		setcookie("basket_id", $basket_id, time() + $this->time_life_cookie);
	}

	function add_basket_item() {
		if (!$this->basket_id) {
			if ($this->debug) echo '!basket_id';
			return;
		}
		if ((!isset($_POST['id'])) or (!intval($_POST['id']))) {
			if ($this->debug) echo '!POST[id]';
			return;
		}
    $id = intval($_POST['id']);
		$amount = ($_POST['amount']) ? intval($_POST['amount']) : 1;
		$item = db::row('*', $this->tbl_goods, "id = $id", null);
    //$item = db::row('*', DB_PFX.'articul', "id = $id", null);
		if (!$item) {
			if ($this->debug) echo '!item';
			return;
		}
    
    $art_price = 0;
  
    if($item['price']){
  	$art_price = $item['price'];
    }elseif($item['price_eur'] > 0){
  	$art_price = round($item['price_eur'] * $this->eur, 2); 
    }elseif($item['price_dol'] > 0){
  	$art_price = round($item['price_dol'] * $this->usd, 2);
    }
    #$art_price = number_format($art_price, 2, ',', ' ');      
        
    
    
    //$sitecatname=db::value('title', DB_PFX.'goods_cat', "id = ".$item["cat_id"]);
    
    $amount_exist = db::value('amount', DB_PFX.'basket_items', "item_id = $id AND basket_id = $this->basket_id");
    if ($amount_exist) {
  		$amount = $amount + $amount_exist;
  		$res = db::update(DB_PFX.'basket_items', compact('amount'), "item_id = $id AND basket_id = $this->basket_id");
  	} else {
  		$res = db::insert(DB_PFX.'basket_items', array('amount'=>$amount, 'item_id'=>$id, 'basket_id'=>$this->basket_id, 'siteprice'=>$art_price, 'sitename'=>$item["title"]/*, 'sitecatname'=>$sitecatname*/));
  	}
    
    
    
		$output = $this->get_basket_data();
		$output['status'] = ($res) ? 'ok' : 'mistake';
		return $output;

	}


	/**
	 * Получаем количество товаров и общую сумму
	 *
	 * @return array
	 */
	function get_basket_data() {

		$basket_price = 0;
    $basket_count = 0;
    $s = "
    SELECT  
            i.id AS item_id, 
            i.title, 
            bi.id, 
            bi.amount, 
            i.img as img, 
            i.price,
            u.url
    FROM   
            `".DB_PFX."basket_items` AS bi 
    LEFT JOIN 
            `".DB_PFX."basket` AS b 
    ON      (b.id = bi.basket_id) 
    LEFT JOIN 
            `".$this->tbl_goods."` AS i 
    ON 
            (bi.item_id = i.id)
    LEFT JOIN `".$this->tbl_url."` AS u
    ON (u.module = '".$this->tbl_goods."') AND (u.module_id = i.id)
    WHERE 
            bi.basket_id = $this->basket_id 
    AND 
            b.state = 1    
    "; #pri($s); 
    
		$basket_list = db::arr( $s );
    
		if ($basket_list and count($basket_list)) {
			foreach ($basket_list as $item) {
				$amount = $item['amount'];
        $id = $item['id'];
        $art_price = 0;
        $art_portion = 1;
        
        if($item['price']){
          $art_price = $item['price'];
        }
        
				$basket_price = $basket_price + ( $amount/$art_portion * $art_price);
				$basket_count = $basket_count + $amount;
			}
		}

		$this->count = $basket_count;
		$this->price = number_format($basket_price, 0, '', ' ');
    $str = '';
		if ($this->basket_id and $this->count) {
			$string_coung = string_tools::get_int_text('', 'а', 'ов', $this->count);
      $str .= '
        <div class="basked_count">'.$this->count.' товар'.$string_coung.'</div>
        <div class="basked_price_box"> <span class="basked_price"> '.$this->price.' </span> <i class="fa fa-rub" aria-hidden="true"></i></div>
      ';
		} else {
      $str .= '
        <div class="basked_price_box"> нет товаров </div>';
		}

		return array('basket_head'=>$str);
	}


	/**
	 * Показываем юзеру его корзину
	 * @param int $basket_id
	 */
	function show_basket() {
    $output = '';
		if (isset($_POST['view']) and $_POST['view'] == 'pre_order') {
			$output .= $this->show_pre_order();
		} else {
			$output .= ''.$this->get_basket().'';
		}
    return $output;
	}


	function get_basket() {
    $output = '';
		if (!$this->basket_id) {
			 $output = '<p>Ваша корзина пуста</p>';
			return $output;
		}
		$basket_price = 0;
		$basket_count = 0;
		$i = 0;
    
    $basket_list_s = "
    SELECT  
            i.id AS item_id, 
            i.title, 
            bi.id, 
            bi.amount, 
            i.img as img, 
            i.availability_id as nal, 
            i.cat_id as cat_id,
            i.price,            
            mi.title AS units,
            u.url
    FROM   
            ".DB_PFX."basket_items AS bi 
    LEFT JOIN 
            `".$this->tbl_goods."` AS i 
    ON 
            (bi.item_id = i.id) 
    LEFT JOIN
            ".DB_PFX."units AS mi
    ON
            (i.units_id = mi.id)
    LEFT JOIN 
            ".DB_PFX."basket AS b 
    ON      (b.id = bi.basket_id) 
    LEFT JOIN `".$this->tbl_url."` AS u
    ON (u.module = '".$this->tbl_goods."') AND (u.module_id = i.id)
    WHERE 
            bi.basket_id = $this->basket_id 
    AND 
            b.state = 1
    "; #pri($basket_list_s);
    
    $basket_list = db::arr($basket_list_s);
		// print_r($basket_list);
		if (!count($basket_list)) {
			$output .= '
        <p>Ваша корзина пуста</p>
  			<script>
  			$(function(){
  			$("#order_form_holder").remove();
  			})
  			</script>
  			';
			return;
		}
    $output .= '
      <div class="basket_header row">
        <div class="col-3 col-sm-2">Фото</div>
        <div class="col-4 col-sm-3">Наименование</div>
        <div class="col-5 col-sm-7">
          <div class="row">
            <div class="col-12 col-sm-3">Цена</div>
            <div class="col-12 col-sm-5">Количество</div>
            <div class="col-12 col-sm-3"><div class = "">Сумма</div></div>
          </div>
        </div>
      </div>
    ';
		foreach ($basket_list as $item) {
			
			$i++;
			extract($item);
      #$nal=db::value("title", DB_PFX."availability_items", "id=$nal");
      /*if($item["price"] > 0){
        $price = $item["price"];
      }else{
        $price = $item["price_ye"] * $this->ye;
      }*/
      
      $art_price = 0;
  
      if($item['price'] > 0){
    	  $art_price = $item['price'];
      }elseif($item['price_eur'] > 0){
    	  $art_price = round($item['price_eur'] * $this->eur, 2); 
      }elseif($item['price_dol'] > 0){
    	  $art_price = round($item['price_dol'] * $this->usd, 2);
      }
      
      // Название коллекции
      $cats_item = db::row("title, parent_id", $this->tbl_goods_cat, "id = ".$cat_id );
      
      /*echo "<pre>";
      print_r($cats_item);
      echo "</pre>";*/
      
      // Название фабрики
      $fab_item = db::row("title", $this->tbl_goods_cat, "id = ".$cats_item['parent_id'] );
		
      $output .= '
        <div id="row_'.$id.'" class="basket_line basket_item row align-items-center">
          <div class="col-xs-3 col-sm-2 basket_img_box">
            <div class="row align-items-center">
      ';
      if ($img) { 
        $output .= '
          <a href="/'.$url.'">
            <img src = "'.Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/100x100',  100, null, 0xFFFFFF, 100).'"  title = "'.$cats_item['title'].' '.$title.' " />
          </a>
        ';
 		 } 
      $output .= '
            </div>
          </div>
          <div class="col-xs-4 col-sm-3">
            <a href="/'.$url.'" class = "card_title"> '.$title.'</a> 
      ';
      
      #$output .= '<br><span class = "item_id">id: '.$id.'</span>';
      
      if(isset($country) && $country){
        $output .= '<br><b>Страна</b>: '.$country.'';
      }
      $output .= '
          </div>
          
          <div class="col-xs-5 col-sm-7">
            <div class="row align-items-center">
              <div class="col-xs-12 col-sm-3 basket_price">
                '.number_format($art_price, 0, ',', ' ').' <i class="fa fa-rub" aria-hidden="true"></i>';
      #' / '.$units.' '; 
      /*if($item['price_eur'] > 0){
    	  $output .= '
          <br>'.number_format($item['price_eur'] , 0, ',', ' ').' / '.$units.' <i class="fa fa-eur" aria-hidden="true"></i>'; 
      }elseif($item['price_dol'] > 0){
    	  $output .= '
          <br>'.number_format($item['price_dol'], 0, ',', ' ').' / '.$units.' <i class="fa fa-usd" aria-hidden="true"></i>'; 
      }*/
      if($units == 'шт'){
        $amount = round($amount);
      }
      $amount = round($amount);
      $output .= '
              </div>
             <div class="col-xs-12 col-sm-5">
                <div class="input-prepend input-append amount_btn_box" style="margin-bottom:0; box-shadow: none;">
                	<div class="btn-group">
                		<button onclick="change_amount('.$id.', 1, 1)" class="btn"><i class="fas fa-minus fa-sm"></i></button>
                		<input type="text" class="store_amount" id="amount_'.$id.'" value="'.$amount.'" onblur="change_amount('.$id.', 0, 1)">
                		<button onclick="change_amount('.$id.', 2, 1)" class="btn"><i class="fas fa-plus fa-sm"></i></button>
                	</div>
                </div>
              </div>';
      $output .= '
              <div class="col-xs-12 col-sm-3 basket_price">
                '.number_format($art_price * $amount, 0, ',', ' ').' <i class="fa fa-rub" aria-hidden="true"></i>
              </div>';
      
      $output .= '
              <div class = "col-xs-12 col-sm-1 delete_basked_box" align = "right">
                <button class="btn ajx btn_backed_remove" onclick="delete_basket_item('.$id.')">
                  <i class="fa fa-times" aria-hidden="true" style = "color: red;"></i> 
                </button> 
              </div>
            </div>
          </div>
      
        </div>
      ';
      
      $basket_price = $basket_price + ($amount * $art_price);
			$basket_count = $basket_count + $amount;
			
		}
		if ($basket_count) {
			$output .= '
        <div class="basked_total_box row">
          <div class="col-12">Итого: '.number_format($basket_price, 0, ',', ' ') .' <i class="fa fa-rub" aria-hidden="true"></i></div>
        </div>
        
        <div class="row">
          <div class="col-xs-12 col-sm-6">
      ';
            //<a class="shop_back " href="/">вернуться к покупкам</a>
    	$output .= '
          </div>
      ';
      /*
      $output .= '
          <div class="col-xs-12 col-sm-6 order_submit_box">
            <button type="button" id="order_submit" class="order_submit" value="oформить заказ" onclick="send_order()">Оформить заказ</button>
          </div>
      ';*/
      $output .= '
        
        </div>
      ';
		}

		
    
    return $output;
	}


	/**
	 * Изменяем количество товара в корзине
	 *
	 * @param int $basket_id
	 * @param int $id
	 * @param int $amount
	 * @return int
	 */
	function change_amount($id, $amount) {
		if ((!$id) or ($amount < 0) or !$this->basket_id) return;
		$exist = db::value('1', DB_PFX.'basket_items', "id = $id AND basket_id = $this->basket_id");
		if (!$exist) return;
    /*echo "$amount = $amount";
    echo print_r(compact('amount'));
    die();*/
		$res = db::update( DB_PFX.'basket_items', compact('amount'), "id = $id AND basket_id = $this->basket_id" );
		return ($res) ? $amount : 'mistake';
	}


	function create_basket($last_time) {
		$state = 1;
		$user_id = intval($this->user_id);
		return db::insert(DB_PFX.'basket', compact('last_time', 'state', 'user_id'));
	}


	function delete_item() {
		if (!$this->basket_id) return;
		if ((!isset($_POST['id'])) or (!intval($_POST['id']))) return;
		$id = intval($_POST['id']);
		$res = db::delete(DB_PFX.'basket_items', "basket_id = {$this->basket_id} AND id = $id", 1);
		if ($res) {
			return $this->get_basket();
		}
	}


	/**
	 * Очистить старые записи
	 * @return int
	 */
	function clear_old_basket() {
		$time = time() - $this->time_life_cookie;
		$baskets = db::select('id', DB_PFX.'basket', "last_time < $time", 'id');
		foreach ($baskets as $basket) {
			$id = $basket['id'];
			db::delete(DB_PFX.'basket', "id = $id");
			db::delete(DB_PFX.'basket_items', "basket_id = $id");
		}
		return count($baskets);
	}


	function clear_basket() {
    #echo "clear_basket!";
		$upd = array('state'=>0);
		$user_id_empty = db::value('1', DB_PFX.'basket', "id = {$this->basket_id} AND user_id = 0");
		if ($this->user_id AND $user_id_empty) {
			$upd['user_id'] = $this->user_id;
		}
		db::update( DB_PFX.'basket', $upd, "id = {$this->basket_id}" );
		setcookie("basket_id", $this->basket_id, time() - $this->time_life_cookie);
	}


	/**
	 * Форма оформления заказа
	 */
	function show_pre_order() {
    $output = '';
		if (!$this->basket_id or !$this->count) {
			// $output .= "<p>Ваша корзина пуста</p>";
			return;
		}
		$output .= '
      <div id="order_form_holder">
    ';
		/*$output .= '<h3>Оформление заказа</h3>';*/
		
		$fio = '';
		$email = '';
    $city = '';
    
    /*$output .= '<p style="margin: 15px 10px 10px 10px; padding-top: 5px;">Заполните форму заказа. Поля отмеченные звёздочкой <span class="z" style="color: red;">* являются обязательными</span>.</p>'; */
    
    $output .= '
      <form id = "basket_form" class="form-horizontal col-xs-12" role="form">
        <div class="row ordering_hd_box">
          <div class="col-12 ordering_hd">
            Для оформления заказа, пожалуйста, заполните форму
          </div>
        </div>
        <div class="basket_form_cont">
          <div class="form-group row">
            <label for="fio" class="col-sm-3 col-form-label">Имя:&nbsp;<span style = "color: red;">*</span></label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="fio" id="fio" placeholder="" required value="'.$fio.'">
            </div>
          </div>
          
          <div class="form-group row">
            <label for="phone" class="col-sm-3 col-form-label ">Телефон:&nbsp;<span style = "color: red;">*</span></label>
            <div class="col-sm-9">
              <input type="text" name="phone" id="phone" class = "form-control" required value="" placeholder="+7 (___) ___-____" />
            </div>
            <div class="hidden-xs col-sm-1"></div>
          </div>
          
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">E-mail:&nbsp;<span style = "color: red;">*</span></label>
            <div class="col-sm-9">
              <input type = "email" required placeholder = "your@email.com" class="form-control" name="email" id="email" value="'.$email.'">
            </div>
          </div>
          
          <div class="form-group row">
            <label for="address" class="col-sm-3 control-label">Адрес:</label>
            <div class="col-sm-9">
              <input type="text" name="address" class="form-control" id="address" value=""  />
            </div>
          </div>
          
          <div class="form-group row">
            <label for="address" class="col-sm-3 control-label">Доставка:&nbsp;<span style = "color: red;">*</span></label>
            <div class="col-sm-9 radio_basked_box">
              <div class="row">
                <div class="col-auto"><input type="radio" name="dost" id="dost1" value="1" class = "dost_radio"></div>
                <div class="col"><label for="dost1"><strong>Самовывоз:</strong> '.db::value("val", DB_PFX."config", "name = 'adress'").'</label></div>
              </div>
               
              <div class="row">
                <div class="col-auto"><input type="radio" name="dost" id="dost2" value="2" class = "dost_radio" checked="checked"></div>
                <div class="col"><label for="dost2"><strong>Доставка </strong> с 8:00 до 24:00</label></div>
              </div>
			
            </div>
          </div>
          
          <div class="form-group row">
            <label for="comment" class="col-sm-3 col-form-label ">Комментарий:</label>
            <div class="col-sm-9">
              <textarea name="comment" id="comment" class = "form-control" rows = "3"></textarea>
            </div>
          </div>
          
           <div class="form-group row">
            <label for="comment" class="col-sm-3 col-form-label ">Согласие:&nbsp;<span style = "color: red;">*</span></label>
            <div class="col-sm-9 radio_basked_box">
              <div class="row">
                <div class="col-auto">
                  <input class="req сonsent_checkbox" type="checkbox" id="checkbox_politic" checked="checked" required>
                </div>
                <div class="col">
                  <span class="input-group-addon" >Я согласен с <a href="/politikoy-organizacii-po-obrabotke-personalnyh" rel="nofollow" target="_blank">политикой организации по обработке персональных данных</a> и даю свое <a href="/soglasie-posetitelya-sayta" rel="nofollow" target="_blank">согласие</a> на их обработку</span>
                </div>
              </div>
            </div>
          </div>
          
        </div>

        <input type = "hidden" name = "address" id = "address" value = "Уточнить">
        <br /><br />
        
        <div class="order_submit_b">
          <div class="catalog_download_btn_box">
            <button class="catalog_download_btn order_submit btn btn-success btn-lg" value="oформить заказ" onclick="send_order()">
              Оформить заказ
            </button>
          </div>
        </div>
        
      </form>
    '; 
    $output .= '  
    </div>
    <br /><br /><br />
    ';

    
    return $output;
    
	}


	function send_order() {
		if (!$this->basket_id or empty($_POST)) return;
		$contacts_id = false;
		
		if ($this->user_id and isset($_POST['contacts_id'])) {
			$user_data = db::row('email, fio', 'basket_user', "id = {$this->user_id}");
			if (!$user_data) return;
			$fio = $user_data['fio'];
			$email = $user_data['email'];
      //$city = $user_data['city'];
			$contacts_id = intval($_POST['contacts_id']);
			$user_contacts = db::row('address, phone, org', 'basket_user_contacts', "id = $contacts_id AND user_id = {$this->user_id}");
			if (!$user_contacts) return;
			$phone = $user_contacts['phone'];
			//$org = $user_contacts['org'];
			$address = $user_contacts['address'];
		
		} else {
			if ((!$_POST['fio']) or (!$_POST['phone'])) return;
			//			$regexp = "^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$";
			//			if (preg_match($regexp, $_POST['email']) == false) return;

      $fio = trim($_POST['fio']);
			//$org = trim($_POST['org']);
			$phone = trim($_POST['phone']);
      //$city = trim($_POST['city']);
			$address = trim($_POST['address']);
			$comment = trim($_POST['comment']);
			//$datetime = trim($_POST['datetime']);
			$email = trim($_POST['email']);
      $vyvoz = '';
     
			$vyvoz="Доставка: ";
			switch  ($_POST["dost"])
			{
				case 1:
				$vyvoz .= " Самовывоз";
				break;
				case 2:
				$vyvoz .= " Доставка";
				break;
				break;
			}
        
			$user_not_logined = db::value('id', DB_PFX.'basket_user', "email = '$email'"); 
		}

		$charset_to = 'UTF-8';
		$charset_from = 'UTF-8';
    
		$basket_price = 0;
		$basket_count = 0;
    
    $sql = "
    SELECT  
            i.id AS item_id, 
            i.title, 
            bi.id, 
            bi.amount, 
            i.img as img, 
            i.availability_id as nal, 
            i.cat_id as cat_id,
            i.price,
            
            mi.title AS units,
            u.url
    FROM   
            ".DB_PFX."basket_items AS bi 
    LEFT JOIN 
            `".$this->tbl_goods."` AS i 
    ON 
            (bi.item_id = i.id) 
    LEFT JOIN
            ".DB_PFX."units AS mi
    ON
            (i.units_id = mi.id)
    LEFT JOIN 
            ".DB_PFX."basket AS b 
    ON      (b.id = bi.basket_id) 
    LEFT JOIN `".$this->tbl_url."` AS u
    ON (u.module = '".$this->tbl_goods."') AND (u.module_id = i.id)
    WHERE 
            bi.basket_id = $this->basket_id 
    AND 
            b.state = 1
    ";
    
    #echo "sql = $sql";
    
		$basket_list = db::arr($sql);
		$ip=$ips=$_SERVER["REMOTE_ADDR"];
		#pri($basket_list);
    $message = '
      <style>
        table.company_data{       width: 100%;     }
      </style>
      <table class="company_data">
        <tr>
          <td>
'.db::value("value", DB_PFX."seo", 'type = "mine_title"' ).'<br>
<a href = "http://'.$_SERVER['HTTP_HOST'].'">'.$_SERVER['HTTP_HOST'].'</a>
          </td>          
          <td align = "right">
            <a href = "http://'.$_SERVER['HTTP_HOST'].'">
              <img src = "http://'.$_SERVER['HTTP_HOST'].$this->logo_path.'">
            </a>
          </td>
          
        </tr>
      </table>
      <br>
    ';
    $message .= ' от '.$fio."<br />";
		$message .= " Тел: $phone<br />";
		$message .= " Email: $email<br />";
		$message .= " Адрес: $address<br /><br />";
		//$message .= " Желаемые дата и время доставки: $datetime<br /><br />";
		//$message .= $ras."<br/>";
		if($vyvoz) $message .= $vyvoz."<br/>";
		//$message .= "<br/>";
		$message .= "Комментарий:".$comment;
    $message .= "<br/>";
    $message .= '<h5>'.$ips.'</h5>';
    $order_c = '';
    
    $order_c .=  '
				<style>
				 table.order, table.order tr, table.order td { border-collapse: collapse; vertical-align: top; empty-cells: show; }
				 table.order th, table.order td { border: 1px #ccc solid; padding: 2px; }
         .item_id{ color: #ccc;}
				</style>';
		$order_c .=  '
      <table class="order">
        <tr>
          <th>Фото</th>          
          <th>Наименование</th>
          <th>Цена</th>
          <th>Количество</th>
          <th>Итого</th>
        </tr>';
		
    $answer_table = '';
    
		foreach ($basket_list as $item) {
			extract($item);
      $price = 0;
  
      if($item['price']){
    	  $price = $item['price'];
      }elseif($item['price_eur'] > 0){
    	  $price = round($item['price_eur'] * $this->eur, 2); 
      }elseif($item['price_dol'] > 0){
    	  $price = round($item['price_dol'] * $this->usd, 2);
      }    
      // Название коллекции
      $cats_item = db::row("title", $this->tbl_goods_cat, "id = ".$cat_id );
      
      $order_c .= '
      <tr>
      ';
      if ($img) { 
        $order_c .= '
          <td  width="170" height = "160" align="center" valign = "middle" >
          <a href="http://'.$_SERVER['SERVER_NAME'].'/'.$url.'">
            <img src = "http://'.$_SERVER['HTTP_HOST'].Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/100x100',  100, null, 0xFFFFFF, 100).'"  title = "'.$cats_item['title'].' '.$title.' " />
          </a>
        ';
      }else{
        $order_c .= '
        <td>
        ';
      }
      $order_c .= '
        </td>
        <td>
          <a href="http://'.$_SERVER['SERVER_NAME'].'/'.$url.'">'.$cats_item['title'].' '.$title.'</a>
      ';
      $order_c .= '<br><span class = "item_id">id: '.$id.'</span>';
      $order_c .= '
        </td>
        <td>
          '.$price.' руб ';
      #    '/ '.$units.'       ';
      $order_c .= '
        </td>
        <td>'.$amount.'</td>
        <td>'.$amount * $price.' руб.</td>
      </tr>
      ';
      
      $answer_table .= '
      	<tr>
      ';
      if ($img) { 
        $answer_table .= '
          <td  style = "    min-width: 95px; min-height: 95px; text-align: center;">
            <img src = "http://'.$_SERVER['HTTP_HOST'].Images::static_get_img_link("images/goods/orig", $img,  'images/goods/variations/100x100',  100, null, 0xFFFFFF, 100).'"  title = "'.$cats_item['title'].' '.$title.' " />
        ';
      }else{
        $answer_table .= '
          <td>
        ';
      }
      $answer_table .= '
          </td>
      		<td>
            '.$cats_item['title'].' '.$title.'
      ';
      $answer_table .= '<br><span class = "item_id">id: '.$id.'</span>';

      /*
      if($country){
        $answer_table .= ' <br>Страна: '.$country.'';
      }*/
      $answer_table .= '
          </td>
      		<td align="center" >
            '.$price.' руб ';
      #/ '.$units.'      ';
      $answer_table .= '
          </td>
          <td align="center" >'.$amount.'</td>
          <td align="center" >'.number_format($amount * $price , 0, ',', ' ').' руб.</td>
      	</tr>
      ';
      
			$basket_price = $basket_price + ($amount * $price);
			$basket_count = $basket_count + $amount;
		}
    $order_c .= '
      <tr>
        <td colspan = "4" style="color:#000" align="right"><b>ИТОГО:</b></td>
        <td style = "text-align:center;"><strong>'.round($basket_price).' руб.</strong></td>
      </tr>
    ';
		$order_c .= '</table>';
		$order_c .= "
    <br />
    <strong>Общее количество товаров: $basket_count; на сумму: $basket_price руб."."
    <br><h5><a href='http://".$_SERVER['SERVER_NAME'].IA_URL."orders.php'>админка</a></h5>";

		$site_name = $_SERVER['HTTP_HOST'];

		// DATABASE ------------------------------------------------------------
		$date_time = time();
		$basket_id = $this->basket_id;
		$comment_cust=$comment;
		//$date_dost=$datetime;
    /*
    $personal_accaunt_id = 0;
    if(isset ($this->user))
      if(isset ($this->user['id']))
        if($this->user['id']){
          $personal_accaunt_id = $this->user['id'];
        }
        */
		$sum= $basket_price;
    $message .= $order_c;
		$tosend=$message;
		#$message=mysql_real_escape_string($message);
    $message = addslashes($message);
		$order_id=db::insert(DB_PFX.'basket_orders', compact('basket_id','phone','address', 'org', 'city', 'email','fio','comment_cust', 'sum','date_dost', 'message', 'date_time','ip', 'personal_accaunt_id'), 0);
    #pri($order_id);
    
		// ---------------------------------------------------------------------

		// User ----------------------------------------------------------------
		$contacts_saved = false;
		if (($this->user_id or $user_not_logined) and !$contacts_id and !$contacts_saved) {
			$contact_exist = false;
			if ($user_not_logined) {
				$contact_exist = db::value('1', DB_PFX.'basket_user_contacts', "org = '$org' AND phone = '$phone' AND address = '$address'");
			}
			if (!$contact_exist) {
				$contacts_user = array();
				$contacts_user['org'] = $org;
				$contacts_user['phone'] = $phone;
        $contacts_user['city'] = $city;
				$contacts_user['address'] = $address;
				$contacts_user['user_id'] = $user_id;
				db::insert(DB_PFX.'basket_user_contacts', $contacts_user);
			}
		}
		// ---------------------------------------------------------------------

		$title = $_SERVER['SERVER_NAME']." $order_id $sum";
    
		$mail = EMail::Factory();
		if ($email) $mail->addHeader("From: $email");
		$mail->addHeader("Content-Type: text/html");
		$email_order = db::value('val', DB_PFX.'config', "name = 'email_order'");
    
    // Если несколько адресов перечисленно через запятую
    $exp_email_order = explode(',', $email_order);
    if(is_array($exp_email_order)){
      $email_order = array();
      foreach($exp_email_order as $ee_mail){
        $email_order[] = $ee_mail;
      }
    }
    
    $tosend = 'Поступил заказ №'.$order_id.' '.$tosend;
		$res = $mail->send($email_order, $title, $tosend);
		$title_for_cust="Спасибо за заказ на ".$_SERVER['SERVER_NAME'];
		$bod =  "<h1>Спасибо, Ваш заказ принят</h1>"; 
		$bod .= "<p>Номер заказа $order_id</p>";
		$bod .= $order_c;
    #echo "res = `$res`";
		if ($res) {
      #echo "order_id = ".$order_id;
			$this->clear_basket();

      $answer = '<div class="print_order_container" id="content-area" style="color:#000">';
      $answer .= '
      	<style>
         table.order {       width: 100%;    min-width: 500px; font-size: 12px;  }
				 table.order, table.order tr, table.order td { border-collapse: collapse; vertical-align: top; empty-cells: show; padding: 5px; }
				 table.order th, table.order td { border: 1px #ccc solid; padding: 2px; }
         table.company_data{       width: 100%;     }
         table.order img { max-width: 75px; max-height: 75px; }
         .item_id{ color: #ccc;}
				</style>
      ';
      $answer .= '<h1>Спасибо, Ваш заказ принят</h1>';
      $answer .= '
      
      <table class="company_data">
        <tr>
          <td>
'.db::value("value", DB_PFX."seo", 'type = "mine_title"' ).'<br>
<a href = "http://'.$_SERVER['HTTP_HOST'].'">'.$_SERVER['HTTP_HOST'].'</a>
          </td>          
          <td align = "right">
            <a href = "http://'.$_SERVER['HTTP_HOST'].'">
              <img src = "http://'.$_SERVER['HTTP_HOST'].$this->logo_path.'" >
            </a>
          </td>
          
        </tr>
      </table>
      <br>
      ';
      $answer .= '<p>Номер заказа <strong>'.$order_id.'</strong></p>';
      $answer .= '<p><br>Имя: '.$fio.'<br> ';
      //if ($org) $answer .= " Организация: $org<br />";
      $answer .= 'Тел: '.$phone.'<br> ';
      if($email)   $answer .= 'Email: '.$email.'<br /> ';
      if($address) $answer .= " Адрес: $address<br />";
      //if($city) $answer .= 'Город: '.$city.'<br> ';
      if($vyvoz)  $answer .= $vyvoz.' <br /><br />';
      if($comment) $answer .= 'Комментарий: '.$comment.'<br></p>';
      $answer .= '
      
      <table class="table table-order order" style="color:#000">
      	<tbody>
      	<tr>
          <th style="color:#000;">фото</th>
      		<th style="color:#000">наименование</th>
          <th style="color:#000" align="center" >цена</th>
      		<th style="color:#000" align="center" >количество</th>
          <th style="color:#000" align="center" >Итого</th>
      	</tr>
      ';
      $answer .= $answer_table;
      $answer .= '
        <tr>
          <td colspan = "4" style="color:#000" align="right"><b>ИТОГО:</b></td>
          <td style = "text-align: center;"><strong>'. round($basket_price).' руб.</strong></td>
        </tr>
      </table>
      <br>
      ';
      /*$answer .= '
      <p>
      Общее количество товаров: '.$basket_count.' на сумму: <strong>'.$basket_price.'</strong> <br><br>
      </p>
      ';*/
      $answer .= '
        
        <br><br>
      </div> 
      <div class="btn btn-success pull-righ btn-order" onclick="print_text()" >Распечатать заказ</div>
      
      <a href = "/xlsorder?id='.$order_id.'" target = "_blank"><div class="btn btn-success pull-righ btn-order" >Скачать в формате xls</div></a>
      
      <br><br><br><br><br>
      ';
      
      $answer .= '
       
      <!--Скрипт вывода части страницы на печать-->
      <script>
      /*function print_text(){
      var txt=document.getElementById("content-area").innerHTML ;
      document.getElementsByTagName("body")[0].innerHTML=txt;
      var text=document;
      print(text);
      }*/
      
      function print_text() {
        var prtContent = document.getElementById("content-area");
        var strOldOne=prtContent.innerHTML;
        //var prtCSS = \'<link rel="stylesheet" href="/css/style.css" type="text/css" />\';
        //var prtCSS = \'<link rel="stylesheet" href="/css/style.min.css" type="text/css" />\';
        var prtCSS = \'<style media=print> h1, p{    font-family: "Arial", sans-serif;} tr:nth-child(even) th{background: #f0f0f0; font-family: "Arial", sans-serif;  border-bottom: 1px solid #000; margin: 0 0 10px 0;}  tr:nth-child(even) td{background: #f0f0f0; font-family: "Arial", sans-serif;} </style>\';
        var WinPrint = window.open("", "","left=50,top=50,width=800,height=640,toolbar=0,scrollbars=1,status=0");
        WinPrint.document.write(\'<div id="print" class="contentpane">\');
        WinPrint.document.write(prtCSS);
        WinPrint.document.write(prtContent.innerHTML);
        WinPrint.document.write(\'</div>\');
        WinPrint.document.close();
        WinPrint.focus();
        WinPrint.print();
        //WinPrint.close();
        prtContent.innerHTML=strOldOne;
      }
      </script>    
      ';
      
      #header("Content-Type: text/html; charset=$this->charset_ajx");
      echo $answer;
		}
	}


	function show_history() {
		if ($this->user_id) {
			$basket_list = db::select('id', DB_PFX.'basket', "user_id = {$this->user_id}");
			foreach ($basket_list as $basket) {
				$basket_id = $basket['id'];
				$sql = "SELECT bi.item_id, i.title, bi.id, bi.amount FROM basket_items AS bi LEFT JOIN ".DB_PFX."goods AS i ON (bi.item_id = i.id) WHERE bi.basket_id = {$basket_id}";
				$list = db::arr($sql);
				?>
				<table class="basket">
				<tr>
			     <th>#</th>
			     <th>Наименование</th>
			     <th>Цена</th>
			     <th>Количесво</th>
			     <th>Сумма</th>
			     <!--<th></th>-->
				</tr>
				<?
				foreach ($list as $item) {
					$i++;
					extract($item);
                    $q = mysql_query("SELECT p.* FROM basket_price AS p WHERE p.item_id = $item_id AND p.from <= $amount ORDER BY p.from DESC LIMIT 1") or die(mysql_error());
                    $p = mysql_fetch_assoc($q);
                    $price = floatval($p['price']);
					echo '<tr id="row_',$id,'" class="basket_item">';
					echo "<td>$i</td>";
					echo "<td>$title</td>";
					echo "<td>$price</td>";
					echo "<td>$amount</td>";
					echo "<td>".($price * $amount)."</td>";
					echo '</tr>';
				}
				?>
				</table>
				<form action="/basket.php" method="POST">
				 <input type="hidden" name="basket_id" value="<?=$basket_id?>" />
				 <input type="hidden" name="repeat" value="1" />
				 <p class="center">
				  <input type="submit" name="submit" value="Повторить заказ" />
				 </p>
				</form>
				<hr />
				<?
			}
		}
	}

	static function xls_order($site){
    
    if( !isset($_GET['id']) || !$_GET['id'] )
      return;
    
    /*echo "<pre>";
    print_r($_GET);
    echo "</pre>";*/
    
    $b_id = $_GET['id'];
    
    $sql = "
      SELECT * FROM `".DB_PFX."basket_orders` WHERE `id` = $b_id
    ";
    

    
    /*echo "<pre>";
    print_r($sql);
    echo "</pre>";*/
    
    if($q_sql = $site->pdo->query($sql) ){
      if($q_sql->rowCount()){
        while($r_sql =  $q_sql->fetch()){
          extract($r_sql);
          
          /*echo "<pre>";
          print_r($r_sql);
          echo "</pre>";*/
          
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename=list.xls"); 
        header("Content-Transfer-Encoding: binary");
        echo 'Поступил заказ №'.$id.' от Илья<br>';
        
        /*if($org) echo 'Организация: '.$org.'<br>';
        if($city) echo 'Город: '.$city.'<br>';
        if($phone) echo 'Тел: '.$phone.'<br>';
        if($email) echo 'Email: '.$email.'<br>';
        if($comment_cust) echo 'Комментарий: '.$comment_cust.'<br>';*/
        $message = preg_replace('#(<h5>.*<\/h5>)#Ui', '', $message); 
        echo $message;
        echo '<br>';
        echo '<h3 style = "color: #bc4747;">Не забудьте спросить о скидке</h3>';
           
        }
      }
    }
    
    
  }
	// USER ----------

  
	static function Authorization() {
		if (empty($_SESSION['basket_user'])) return false;
		reset($_SESSION['basket_user']);
		$id = intval($_SESSION['basket_user']);
		$user = db::value('id', 'basket_user', "id = $id");
		if (!$user) {
			unset($_SESSION['basket_user']);
		}
		return $user;
	}


	static function Login() {
		if (!isset($_POST['email']) or !isset($_POST['password'])) {
			echo 'Введите логин и пароль';
			return;
		}
		$email = trim($_POST['email']);
		$password = md5(trim($_POST['password']));
		$user_id = db::value('id', 'basket_user', "email = '$email' AND password = '$password'");
		if ($user_id) {
			self::set_user_cookie($user_id);
			echo 1;
		} else {
			echo 'Логин и пароль не подходят';
		}
	}


	static function set_user_cookie($user_id) {
		$_SESSION['basket_user'] = $user_id;
	}


	static function logout() {
		unset($_SESSION['basket_user']);
		session_unregister('basket_user');
	}


	static function process_string($str) {
		return str_replace(array('$', '<', '>', '!', "'", '"', '&', '*', '%'), '', trim($str));
	}


	static function generate_password() {
		return substr(md5(substr($_SERVER['HTTP_HOST'], rand(0,3), rand(4,6)).str_replace('-', '', date("s-B-A-L-H-F-U"))), 0, 8);
	}


	function show_buttons() {

	}

}