<?php
global $PDO;

function db_open($charset = 'utf8') {
  global $PDO;
  
  $opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  $CFG = & $_SESSION["NEX_CFG"];
  
  $dsn = 'mysql:host='.$CFG["db_hostname"].';dbname='.$CFG["db_basename"].';charset='.$charset;
  
  $PDO = new PDO($dsn, $CFG["db_username"], $CFG["db_password"], $opt);  
  
  if($PDO){
  		$_SESSION["DB_OPENED"] = TRUE;
			return $PDO;
		
	
	}
	return FALSE;
}

function pdoSet($allowed, &$values, $source = array()) {
  $set = '';
  $values = array();
  if (!$source) $source = &$_POST;
  foreach ($allowed as $field) {
    if (isset($source[$field])) {
      $set.="`".str_replace("`","``",$field)."`". "=:$field, ";
      $values[$field] = $source[$field];
    }
  }
  return substr($set, 0, -2); 
}

function get_row_by_id($tablename, $id){
  global $PDO;
	if (!$_SESSION["DB_OPENED"]) db_open();
	$query = $PDO->query("SELECT * FROM `".$tablename."` WHERE `id` = '$id'");
	return  $query->fetch();
}

function mysql_table_seek($tablename = '', $dbname = ''){
  global $PDO;
  $CFG = & $_SESSION["NEX_CFG"];
  $table_list = $PDO->query("SHOW TABLES FROM `".$CFG["db_basename"]."`");
  while ($row = $table_list->fetch()) {
    pri($row);
    /*if ($tablename==$row[0]) {
      return true;
    }*/
  }
  return false;
}


function transliterate($st) {
  /*$st = strtr($st, 
    "абвгдежзийклмнопрстуфыэАБВГДЕЖЗИЙКЛМНОПРСТУФЫЭ",
    "abvgdegziyklmnoprstufieABVGDEGZIYKLMNOPRSTUFIE"
  );
  $st = strtr($st, array(
    'ё'=>"yo",    'х'=>"h",  'ц'=>"ts",  'ч'=>"ch", 'ш'=>"sh",  
    'щ'=>"shch",  'ъ'=>'',   'ь'=>'',    'ю'=>"yu", 'я'=>"ya",
    'Ё'=>"Yo",    'Х'=>"H",  'Ц'=>"Ts",  'Ч'=>"Ch", 'Ш'=>"Sh",
    'Щ'=>"Shch",  'Ъ'=>'',   'Ь'=>'',    'Ю'=>"Yu", 'Я'=>"Ya",
	' '=>'-',	'.'=>'', ','=>'', '('=>'', ')'=>'', '+'=>'-', '/'=>'-', '!'=>'-', '%'=>'','&'=>'-',':'=>"_"
  ));
  return $st;
  */
  $replace=array(
		"'"=>"",
		"`"=>"",
		"а"=>"a",   "А"=>"a",
		"б"=>"b",   "Б"=>"b",
		"в"=>"v",   "В"=>"v",
		"г"=>"g",   "Г"=>"g",
		"д"=>"d",   "Д"=>"d",
		"е"=>"e",   "Е"=>"e",
    'ё'=>"yo",  'Ё'=>"yo",
		"ж"=>"zh",  "Ж"=>"zh",
		"з"=>"z",   "З"=>"z",
		"и"=>"i",   "И"=>"i",
		"й"=>"y",   "Й"=>"y",
		"к"=>"k",   "К"=>"k",
		"л"=>"l",   "Л"=>"l",
		"м"=>"m",   "М"=>"m",
		"н"=>"n",   "Н"=>"n",
		"о"=>"o",   "О"=>"o",
		"п"=>"p",   "П"=>"p",
		"р"=>"r",   "Р"=>"r",
		"с"=>"s",   "С"=>"s",
		"т"=>"t",   "Т"=>"t",
		"у"=>"u",   "У"=>"u",
		"ф"=>"f",   "Ф"=>"f",
		"х"=>"h",   "Х"=>"h",
		"ц"=>"c",   "Ц"=>"c",
		"ч"=>"ch",  "Ч"=>"ch",
		"ш"=>"sh",  "Ш"=>"sh",
		"щ"=>"sch", "Щ"=>"sch",
		"ъ"=>"",    "Ъ"=>"",
		"ы"=>"y",   "Ы"=>"y",
		"ь"=>"",    "Ь"=>"",
		"э"=>"e",   "Э"=>"e",
		"ю"=>"yu",  "Ю"=>"yu",
		"я"=>"ya",  "Я"=>"ya",
		"і"=>"i",   "І"=>"i",
  	' '=>'-',	
    '.'=>'', 
    ','=>'', 
    '('=>'', 
    ')'=>'', 
    '+'=>'-', 
    '/'=>'-', 
    '!'=>'-', 
    '%'=>'',
    '&'=>'-',
    '«'=>"",
		'»'=>""
	);
	return $str=iconv("UTF-8","UTF-8//IGNORE",strtr($st, $replace));
}

function sqlDateToRusDate(&$date){
  
  $dArr = explode("-", $date);
  if(
      !isset($dArr[0]) || !($dArr[0]) ||
      !isset($dArr[1]) || !($dArr[1]) ||
      !isset($dArr[2]) || !($dArr[2]) 
  ) return false;
  
  $year = $dArr[0];
  $day = $dArr[2];
  $month = $dArr[1];

  switch($dArr[1]){      
    case "01": $month = ' января ';  break;       
    case "02": $month = ' февраля '; break;       
    case "03": $month = ' марта ';   break;       
    case "04": $month = ' апреля ';  break;       
    case "05": $month = ' мая ';     break;       
    case "06": $month = ' июня ';    break;       
    case "07": $month = ' июля ';    break;       
    case "08": $month = ' августа '; break;       
    case "09": $month = ' сентября ';break;       
    case "10": $month = ' октября '; break;       
    case "11": $month = ' ноября ';  break;       
    case "12": $month = ' декабря '; break; 
  }
  
  $rus_date = $day.' '.$month.' '.$year;
  
  return $rus_date;
}

function convert_date($date, $input_format, $output_format){
  
  $res_date = $date;
  $dt = DateTime::createFromFormat($input_format, $date);
  $methodVariable = array($dt, 'format');
  if( is_callable($methodVariable)  ){
    $res_date = $dt->format($output_format);
  }
  
  return $res_date;
  
}

function pri($arr, $head = null){
  if($head) echo '<br>'.$head.'<br>';
  echo '<pre>';
  print_r($arr);
  echo '</pre>';
  echo '<br>';
}

function ucfirst_utf8($str){
  
  $str = mb_strtolower (strip_tags($str));
  
  return mb_substr(mb_strtoupper($str, 'utf-8'), 0, 1, 'utf-8') . mb_substr($str, 1, mb_strlen($str)-1, 'utf-8');
}

class string_tools {

	static function get_int_text($first, $middle, $end, $int) {
		if (substr($int, -2, 1) === '1' and 10 < $int) return $end;
		$last = substr($int, -1);
		if ($last == '1') return $first;
		if (in_array($last, array('2','3','4'))) return $middle;
		return $end;
	}

}