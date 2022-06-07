<?php

class AllFunction{
  
  static function validate_post_vars($a = NULL){
  	if ($a == NULL) $a =& $_POST;
  	if (is_array($a)){
  		foreach($a as $key => $value){
  			if (is_array($value)) {
  				$valid_value = self::validate_post_vars($value);
  			}else{
  				if (get_magic_quotes_gpc()){
  					$valid_value = stripslashes($value);
  				}else{
  					$valid_value = $value;
  				}
  			}
  			$a[$key] = $valid_value;
  		}
  	}
  	return $a;
  }
  
  /**
  * Работа со справочной таблицей
  * id	name	title	val	type	comment
  * 
  * @param undefined $title - Заголовок
  * @param undefined $sql_table_form - таблица
  * 
  * @return
  */
  
  static function setHeaderForAdm($header, $title, &$admin = null){
    $output = '';
    
    if(!is_null($admin) && isset($admin->is_admin_navigation) && ($admin->is_admin_navigation) ){
      $admin->header = $header;
      $admin->title = $title;
    }else{
      $output .=  '<h1>'.$title.'</h1>';
    }
    
    
    return $output;
  }
  
  function OneFormAdmin( $title = '', $sql_table, &$admin = null){
    $output = '';
    
    $output = AllFunction::setHeaderForAdm($title, $title, $admin);
    
    if (isset($_POST['save'])) {
    	foreach ($_POST as $k=>$v) {
    		$param_isset = db::value('1', $sql_table, "name = '$k'");
    		if (!$param_isset) continue;
        $vl = addslashes($v);
    		db::update($sql_table, array('val'=>$vl), "name = '$k'");
    	}
    	$output .= '<p style="text-align: center; font-weight: bold; color: #969696;">Сохранено</p>';
    }

    $list = db::select('*', $sql_table, "`hide` = 0", "`id`");

    $output .= '
      <form method="POST"  class="form-horizontal form-label-left param" >
        
        <input type="hidden" name="save" value="1" />
        <div class="c_form_box">
    ';

    foreach ($list as $param) {
    	extract($param);
    	$output .= '
      	  <div class="form-group row">
            <label class="col-12 col-sm-4 col-md-3 col-lg-2 c_title control-label" for="'.$name.'">'.$title.'</label>
      	    <div class="col-12 col-sm-8 col-md-9 col-lg-10 c_cont">
      ';
      if($type == 1) {
        $output .= '<textarea class = "form-control" name="'.$name.'" id="'.$name.'">'.htmlspecialchars($val).'</textarea>';
      }else{
        $output .= '<input type="text" class="form-control" name="'.$name.'" id="'.$name.'" value="'.htmlspecialchars($val).'" />';
      }
      $output .= '
            </div>
      	    <div class="col-12 col-sm-8 offset-sm-4 col-md-9 offset-md-3 col-lg-10 offset-lg-2 c_comment">
              <span class="comment">'.$comment.'</span> 
            </div>
      	  </div>
      ';
    }
    $output .= '
          <div class="form-group row">
            <div class="col-12 col-sm-4 col-md-3 col-lg-2"></div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-10 c_cont" ><input type="submit" value="сохранить" class="btn btn-success btn-large"  value="Сохранить" /></div>
          </div>
        </div>
      </form>
    ';

    return $output;
    
  }

  /**
  * Работа со справочной таблицей Seo
  * id	title	type	view	ord	hide	value
  * 
  * @param undefined $title - Заголовок
  * @param undefined $sql_table_form - таблица
  * 
  * @return
  */
  
  function OneSeoFormAdmin( $title = '', $sql_table, &$admin = null){
    $output = '';
      
    $output = AllFunction::setHeaderForAdm($title, $title, $admin);
    
    if (isset($_POST['save'])) {
    	foreach ($_POST as $k=>$v) {
    		$param_isset = db::value('1', $sql_table, "type = '$k'");
    		if (!$param_isset) continue;
    		db::update($sql_table, array('value'=> addslashes($v) ), "type = '$k'");
    	}
    	$output .= '<p style="text-align: center; font-weight: bold; color: #969696;">Сохранено</p>';
    }

    $list = db::select('*', $sql_table, "`hide` = 0", "`ord`");

    $output .= '
      <form method="POST"  class="form-horizontal form-label-left param" >
        <input type="hidden" name="save" value="1" />
        <div class="c_form_box">
    ';
    $is_req_codemirror = false;
    foreach ($list as $param) {
    	extract($param);
    	$output .= '
      	  <div class="form-group row">
            <label class="col-12 col-sm-4 col-md-3 col-lg-2 c_title control-label" for="'.$type.'">'.$title.'</label>
      	    <div class="col-12 col-sm-8 col-md-9 col-lg-10 c_cont">
      ';
      if($view == 1) {
        $output .= '<textarea class = "form-control" name="'.$type.'" id="'.$type.'">'.htmlspecialchars($value).'</textarea>';
      }elseif($view == 2){
        if(!$is_req_codemirror){
          $is_req_codemirror = true;
          $output .= '
          <link rel="stylesheet" href="ckeditor/plugins/codemirror/css/codemirror.min.css">     
        	<script src="ckeditor/plugins/codemirror/js/codemirror.min.js"></script>
          <script src="ckeditor/plugins/codemirror/js/codemirror.addons.min.js"></script>
          <script src="ckeditor/plugins/codemirror/js/codemirror.mode.htmlmixed.min.js"></script>';
        }
        $output .= '
        <textarea class = "form-control codemirror_block" name="'.$type.'" id="'.$type.'">'.htmlspecialchars($value).'</textarea>';
        $output .= '
        <script>
          var editor = CodeMirror.fromTextArea(document.getElementById("'.$type.'"), {
            lineNumbers: true, // Нумеровать каждую строчку.
            matchBrackets: true,
            mode: "htmlmixed",
            indentUnit: 2, // Длина отступа в пробелах.
            indentWithTabs: true,
            enterMode: "keep",
            tabMode: "shift"
          });
        </script>';
      }elseif($view == 3){
        $output .= '
          <iframe src="'.IA_URL.'kcfinder/browse.php?langCode=ru" 
                  name="'.$type.'" 
                  id="'.$type.'"
                  style = "width: 100%; min-height: 700px;"
                  > </iframe>';
      }else{
        $output .= '<input type="text" class="form-control" name="'.$type.'" id="'.$type.'" value="'.htmlspecialchars($value).'" />';
      }
      $output .= '
            </div>
      	    <div class="col-12 col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 col-lg-10 col-md-offset-2 c_comment">
              <span class="comment">'.$comment.'</span> 
            </div>
      	  </div>
      ';
    }
    $output .= '
          <div class="form-group row">
            <div class="col-12 col-sm-4 col-md-3 col-lg-2"></div>
            <div class="col-12 col-sm-8 col-md-9 col-lg-10 c_cont" ><input type="submit" value="сохранить" class="btn btn-success btn-large" id="submit" value="Сохранить" /></div>
          </div>
        </div>
      </form>
    ';

    return $output;
    
  }
 
	static public function getYandexMapByAddrss($addrss, $name, $width = 400, $height = 300 ){
		$output ='<div id="map" style="width:'.$width.'px; height:'.$height.'px"></div>
		<script src="//api-maps.yandex.ru/2.0/?load=package.standard,package.traffic&lang=ru-RU" type="text/javascript"></script>';
		/*$key = file_get_contents('http://api.yandex.ru/maps/tools/getlonglat/');
		preg_match_all("#key=([A-Za-z0-9]*)#", $key, $array);
		$path = 'http://api-maps.yandex.ru/1.1.21/xml/Geocoder/Geocoder.xml?'.$array[0][0].'==&geocode='.$addrss;
		$coord = file_get_contents($path);
		preg_match_all("#([0-9]{2}.[0-9]{6}),([0-9]{2}.[0-9]{6})#", $coord , $array);
    */
    
    $path = 'http://geocode-maps.yandex.ru/1.x/?geocode='.$this->getRequestParameter("addrss").'&format=json';
    $coord = json_decode(file_get_contents($path));
    $coord = $coord->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
    $arr = explode("|", $coord);
    $array[2][0] = $arr[0];
    $array[1][0] = $arr[1];
		if(isset($array[2][0])&&isset($array[1][0])){
		$output .= '
<script type="text/javascript">
ymaps.ready(init);

function init () {
    var myMap = new ymaps.Map("map", {
            center: ['.$array[2][0].', '.$array[1][0].'],
            zoom: 16
        }),

        // Создаем метку с помощью вспомогательного класса.
        myPlacemark1 = new ymaps.Placemark(['.$array[2][0].', '.$array[1][0].'], {';
 $output .= <<<HTML
			// Свойства.
            // Содержимое иконки, балуна и хинта.
            iconContent: '
HTML;
 $output .= trim($name);
 $output .= <<<HTML
 ',
					  balloonContent: 'Меня можно перемещать'
        }, {
            // Опции.
            // Иконка метки будет растягиваться под размер ее содержимого.
            preset: 'twirl#redStretchyIcon',
            // Метку можно перемещать.
            draggable: true
        }

       );
     myMap.controls
        // Кнопка изменения масштаба.
        .add('zoomControl', { left: 5, top: 5 })
        // Список типов карты
        .add('typeSelector')
        // Стандартный набор кнопок
        .add('mapTools', { left: 35, top: 5 });
		myPlacemark1.events.add([   
        'dragend'
    ], function (e) {

        coords = this.geometry.getCoordinates();
				
        //alert(coords[0]+" "+coords[1]);
				$('shop[map_coord]').value = coords[0]+"|"+coords[1];

    }, myPlacemark1);

    // Добавляем все метки на карту.
    myMap.geoObjects
        .add(myPlacemark1);
				
		document.getElementById('shop[map]').onkeydown = function () {
        // Для уничтожения используется метод destroy.
        myMap.destroy();
    };
}
</script>
HTML;
		return $output;
		}
		return false;
	}
	
	static public function getYandexMapByPos($pos, $name, $width = 400, $height = 300 ){
		#$output ='<div id="map" style="width:'.$width.'px; height:'.$height.'px"></div>
    $output ='<div id="map" style="width:100%; height:'.$height.'px"></div>
		<script src="//api-maps.yandex.ru/2.0/?load=package.standard,package.traffic&lang=ru-RU" type="text/javascript"></script>';
		$arr= explode("|", $pos);
		if(isset($arr[0])&&isset($arr[1])){
		$output .= '
<script type="text/javascript">
ymaps.ready(init);

function init () {
    var myMap = new ymaps.Map("map", {
            center: ['.$arr[0].', '.$arr[1].'],
            zoom: 16
        }),

        // Создаем метку с помощью вспомогательного класса.
        myPlacemark1 = new ymaps.Placemark(['.$arr[0].', '.$arr[1].'], {';
 $output .= <<<HTML
			// Свойства.
            // Содержимое иконки, балуна и хинта.
            iconContent: '
HTML;
 $output .= trim($name);
 $output .= <<<HTML
 ',
					  balloonContent: 'Меня можно перемещать'
        }, {
            // Опции.
            // Иконка метки будет растягиваться под размер ее содержимого.
            preset: 'twirl#redStretchyIcon',
            // Метку можно перемещать.
            draggable: true
        }

       );
     myMap.controls
        // Кнопка изменения масштаба.
        .add('zoomControl', { left: 5, top: 5 })
        // Список типов карты
        .add('typeSelector')
        // Стандартный набор кнопок
        .add('mapTools', { left: 35, top: 5 });
		myPlacemark1.events.add([   
        'dragend'
    ], function (e) {

        coords = this.geometry.getCoordinates();
				
        //alert(coords[0]+" "+coords[1]);
				$('#shop_map_coord').val(coords[0]+"|"+coords[1]);
    }, myPlacemark1);

    // Добавляем все метки на карту.
    myMap.geoObjects
        .add(myPlacemark1);
				
    if(document.getElementById('shop[map]')){
      document.getElementById('shop[map]').onkeydown = function () {
        // Для уничтожения используется метод destroy.
        myMap.destroy();
      };
    }
		/*document.getElementById('shop[map]').onkeydown = function () {
        // Для уничтожения используется метод destroy.
        myMap.destroy();
    };*/
}
</script>
HTML;
		return $output;
		}
		return false;
	}
	
  static public function getYandexMapShopsByCoordsArray($mapArr ){
		//echo "mapArr =";print_r($mapArr);
		$output ='<script src="//api-maps.yandex.ru/2.0.31/?load=package.standard,package.geoObjects,package.geoQuery&lang=ru-RU" type="text/javascript"></script>';
		if($mapArr){
	$output .= <<<HTML
<script type="text/javascript">
function init() {
    var myMap = new ymaps.Map("map", {
            center: [55.43, 37.75],
            zoom: 14
        });
        objects = ymaps.geoQuery([
HTML;

/*	$output .= '	
            new ymaps.GeoObject({
            geometry: {
                type: "Point",
                coordinates: [55.8, 38.8]
            },
            properties: {
                iconContent: \'Метка\',
                balloonContent: \'Балун\',
            }
        }, {
            preset: \'twirl#redStretchyIcon\',
            draggable: false
        }),
';*/
//echo "mapArr = ".print_r($mapArr);
	foreach ($mapArr as $cord)
		{
			$arr = explode("|", $cord);
			
			$output .= '	
		            new ymaps.GeoObject({
		            geometry: {
		                type: "Point",
		                coordinates: ['.$arr[0].','.$arr[1].']
		            },
		            properties: {
		                iconContent: \''. $arr[2].'\',
		                balloonContent: \''.$arr[3].'\',
		            }
		        }, {
		            preset: \'twirl#redStretchyIcon\',
		            draggable: false
		        }),
		';
	}
$output .= <<<HTML
        ]);

    objects.applyBoundsToMap(myMap);
    ymaps.geoQuery(objects).addToMap(myMap);
		myMap.controls
        // Кнопка изменения масштаба.
        .add('zoomControl', { left: 5, top: 5 })
        // Список типов карты
        .add('typeSelector')
        // Стандартный набор кнопок
        .add('mapTools', { left: 35, top: 5 });
       
 
}
ymaps.ready(init);
</script>
<div id="map" style="width:100%; height:450px"></div>
HTML;
		return $output;
		}
		return false;
	}
	


}


//set_magic_quotes_runtime(0);
/*
validate_post_vars();
@session_start();

if (isset($_SERVER["HTTP_REFERER"])){
	$_SESSION["HTTP_REFERER"] = $_SERVER["HTTP_REFERER"];
}else{
	$_SESSION["HTTP_REFERER"] = FALSE;
}
*/