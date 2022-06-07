<?php
require_once('lib/class.Admin.php');
$admin = new Admin();
require_once(WA_PATH.'lib/class.Image.php');
global $PDO;

$days=array(0=>"ВС","ПН","ВТ","СР","ЧТ","ПТ","СБ");

if (!is_array($_SESSION["shown"])) $_SESSION["shown"]=array();

$q1=$PDO->query("SELECT * FROM `".DB_PFX."basket_order_status`");

while ( $opt= $q1->fetch() ){
	$opti[$opt['id']]=$opt['title'];
}

function show_orders_list(){
  
	global $opti,$days,$PDO;
	$output = '';
  $output .= '<h1>Заказы</h1>';
  $output .= '
    <div class="row-fluid">
      <div class="col-12">
        <div class="card card-body ">
          <div class="btn-group-sm">
            <span class="py-2 my-1 mx-1">Фильтр:</span>';
  $output .= '<a href="orders.php" class="btn my-1 mx-1 btn btn-primary">все</a> ';

  foreach($opti as $k=>$v){
  	
  	$output .= '<a href="orders.php?status='.$k.'" class="btn my-1 mx-1  ';
  	if ($k==1) $output .= 'btn-success';
  	elseif (in_array($k,array(2,3,5))) $output .= 'btn-danger';
  	elseif (in_array($k,array(6,7,8))) $output .= 'btn-warning';
  	elseif ($k==9) $output .= 'btn-info';
    else $output .= 'btn-default';
  	$output .= '">'.$v.'</a> ';
	
 	}
   
  $output .= '</div></div>';
  $s = "SELECT * FROM `".DB_PFX."basket_orders`";
  $q=$PDO->query("SELECT * FROM `".DB_PFX."basket_orders`");
  $count = $q->rowCount;
  $limit=15;
  if ($count>$limit){
  	
  	$page=intval($_GET["page"]);
  	$from=$page*$limit;
  	$limiter = " LIMIT $from,$limit";
    
  }
  
  if ($_GET["status"]) $wh=" WHERE `status`=".intval($_GET["status"]);
  
  $q=$PDO->query("SELECT * FROM `".DB_PFX."basket_orders` $wh ORDER BY `date_time` DESC $limiter") or die(mysql_error());
  
  if ($count>$limit)
  {
  	$output .= '<div class="pagination pagination-mini">';
  	$output .= '<ul>';
    
  	for ($i=0; $i<ceil($count/$limit); $i++)
  	{
  		$output .= '<li';
  		if ($i==$page) $output .= ' class = "active"';
  		$n=$i+1;
  		$output .= '><a href="?page='.$i.'">'.$n.'</a></li>';
  	}
    $output .= '</ul>';
  	$output .= '</div>';
  }

  	
  $output .= '
    <table class="table  table-sm">
      <thead>
  ';
  $output .= '
      <tr>
        <th width="20">id</th>
        <th width="80">Дата заказа</th>
        <th width="150">Доставка</th>
        <th width="150">Статус</th>
        <th>Клиент</th>
        <th>Заказ</th>
        <th></th>
        <th></th>
      </tr>
    </thead>
    
    <tbody id="ords">
  ';
  
  while ( $row = $q->fetch() )
  {
  	$items_in_order=db::select("*", DB_PFX."basket_items", "basket_id=$row[basket_id]");
  	$row["items_in_order"]=$items_in_order;
  	$output .= show_row($row);
  	if (!$row["shown"]) $PDO->query ("UPDATE `".DB_PFX."basket_orders` SET `shown`=1 WHERE `id`={$row["id"]}");
  }
  $output .= '
    </tbody>
  </table>
  ';
  
  if ($count>$limit)
  {
  	$output .= '
    <div class="pagination pagination-mini">
    ';
  	$output .= '
      <ul>
    ';
  	
    for ($i=0; $i<ceil($count/$limit); $i++)
  	{
  		$output .= '<li';
  		if ($i==$page) $output .= ' class="active"';
  		$n=$i+1;
  		$output .= '><a href="?page='.$i.'">'.$n.'</a></li>';
  	}
    $output .= '
      </ul>
    ';
  	$output .= '
    </div>
    ';
  }
  
  $output .= '
  </div>
  ';
  
  return $output;
}

function show_row($row, $new=false){
	global $opti,$days,$PDO;
  $output = '';
  #pri($row);
  $status=$row['status'];
	if (!$row['sum']){
		$sum = strstr ($row['message'], 'на сумму: ');
		$sum = substr ($sum,9);
		$sum = str_replace('<br /><br />', '<br>',$sum);
		$sum = str_replace('Стоимость доставки:', '',$sum);
		$sum = str_replace('KZT', '',$sum);
	}
	else $sum=$row['sum'];
		// $name = str_replace("Поступил заказ от ","", strstr ($row["message"], " Тел:", true));
		// $name = "";
		
		$output .= "<tr  class='botstr ";
		
		
		$w=date ("w", $row["date_time"]);
		if ($new) $output .= "ord_new'";
		else 
		{
			if ($row["status"]==1) $output .= "table-success'";
			elseif (in_array($status,array(2,3,5))) $output .= "table-danger'";
			elseif (in_array($status,array(6,7,8))) $output .= "table-warning'"; 
			elseif ($status==9) $output .= "table-info'";
			elseif ($status==10) $output .= "' style='color:#aaa; text-decoration:line-through'";
		}
		$output .= ' id="row_'.$row["id"].'"><td><a href="?id='.$row["id"].'">'.$row["id"].'</a></td>';	
		$output .= '<td  style="vertical-align:top">';
		$output .= '<a href="?id='.$row["id"].'">';		
		$output .= '<span class="badge ';
		if (date ("d.m.Y", $row["date_time"])==date ("d.m.Y")) $output .= "badge-important";
		else $output .= "badge-info";
		$output .= '">'.$days[$w].'</span><br>';
		
		if (date ("d.m.Y", $row["date_time"]) == date ("d.m.Y"))
		{
			
			$output .= date ("H:i",$row["date_time"]);
		}
		else $output .= date ("d.m H:i",$row["date_time"]);

		$output .= "</a></td>";
		$output .= "<td>";
		if($row["date_dost"]) $output .= "дата доставки: ".$row["date_dost"]."<br>";
		if (!$row["address"]) $addr="адрес не указан"; else $addr=$row["address"];
		$output .= "<a href='#' id='cr{$row["id"]}' onclick='$(\"#addr{$row["id"]}\").toggle();return(false)'>$addr <i class='fas fa-pencil-alt' aria-hidden='true'></i></a> ";
    //echo "<i class='icon-map-marker onmap'  data-id='{$row["id"]}'></i>";
    if($row['personal_accaunt_id']){
      $user = db::row("*", DB_PFX."personal_accaunt", "`id` = '".$row['personal_accaunt_id']."'");
      $output .= '<br><br><a href = "'.IA_URL.'personal_accaunt.php?edits='.$row['personal_accaunt_id'].'">'.$user['title'].' '.$user['phone'].'</a>';
    }
		$output .= "<div style='display:none' id='addr{$row["id"]}'><textarea id='taddr{$row["id"]}' class='addr'>{$row["address"]}</textarea><br><a class='btn' onclick='save_addr({$row["id"]})'>сохранить</a></div>";
		$push=urlencode(serialize($row));
		#$output .= "<br><br><a href='http://k30.ru/pad-manager/manager.php?par=$push' target='_blank'>Распечатать документы</a>";



		#print '<form action="http://k30.ru/pad-manager/manager.php" method="POST" target="_blank">';
		#print '<input type="hidden" name="way" value="serail_form_submit">';
		#print "<input type='hidden' name='par' value='$push'>";
		#print 'Введите Serial/IMEI:';
		#print '<input type="text" name="serial" size=10>';	
		#print '<input type="submit" value="Ok">';
		#if (isset ($_GET["debug"]))
		#{
		#	print "<pre>";
		#	print_r(unserialize(urldecode($push)));
		#	print "</pre>";
		#}
		#print '</form>';




		
		$output .= "</td>";
		//if ($row["ip"]=="79.172.35.6" or $row["ip"]=="212.49.108.221") $row["ip"]=" <strong>in-ri.ru</strong>";
$output .= "<td style='vertical-align:top'>";
		$output .= "<select name='status' class='status' data-id='{$row["id"]}'>"; 
		foreach ($opti as $k=>$v)
		{
			$output .= "<option value='$k' ";
			if ($row["status"]==$k) $output .= "selected='selected'";
			$output .= ">$v</option>";
		}
		$output .= "</select>";
		$output .= "</td>";
		$output .= "<td>
		<span class='editable alert alert-info' id='c_fio{$row["id"]}'>
			<span class='area'>
        <input type='text' id='fio{$row["id"]}' value='{$row["fio"]}'><br>
        <div class='btn-group-xs' style = 'padding-top: 5px;'>
          <a class='btn btn-success' onclick='save_fio({$row["id"]})'>сохранить</a>
        </div>
      </span>
			<span class='valu'>{$row["fio"]} <i class='fas fa-pencil-alt' aria-hidden='true'></i></span>
		</span>
		<span class='editable alert alert-info' id='c_phone{$row["id"]}'>
			<span class='area'>
        <input type='text' id='phone{$row["id"]}' value='{$row["phone"]}'><br>
        <div class='btn-group-xs' style = 'padding-top: 5px;'>
          <a class='btn btn-success' onclick='save_phone({$row["id"]})'>сохранить</a>
        </div>
      </span>
			<span class='valu'>{$row["phone"]} <i class='fas fa-pencil-alt' aria-hidden='true'></i></span>
		</span>
		<span class='editable alert alert-info' id='c_email{$row["id"]}'>
			<span class='area'>
        <input type='text' id='email{$row["id"]}' value='{$row["email"]}'><br>
        <div class='btn-group-xs' style = 'padding-top: 5px;'>
          <a class='btn btn-success'  onclick='save_email({$row["id"]})'>сохранить</a>
        </div>
      </span> 
			<span class='valu'>{$row["email"]} <i class='fas fa-pencil-alt' aria-hidden='true'></i></span>
		</span>
    <span class='alert' style='display:block'>{$row["ip"]}</span>
		</td>";					
		/*if ($row["manager_id"])
		{
			
			$man=mysql_fetch_array(mysql_query("SELECT `login` FROM `".DB_PFX."accounts` WHERE `id`=".$row["manager_id"]));
			$manager="$man[0]"; 
		}
		else $manager="<span class='label label-important'>[нет]</span>";*/
		$output .= "<td>"; 
    
		$q1 = $PDO->query("SELECT * FROM ".DB_PFX."basket_items WHERE `basket_id`={$row["basket_id"]} ");
    
		if ($row["id"]>0)
		{
		if ($row["tovar"]) $output .= "<b>{$row["tovar"]}</b> - быстрый заказ";
		$output .= "<table class='goods table-sm'>";
    
    $unit_items =  db::select("*", DB_PFX."units" );
    foreach($unit_items as $unit_item){
      $units[ $unit_item['id'] ] = $unit_item['title'];
    }
    
		while ( $it = $q1->fetch() )
		{
      
      /*$g_id = db::value("goods_id", DB_PFX."articul", "id = ".$it[item_id]);*/
      $link = db::value("url", DB_PFX.'url', "module = '".DB_PFX."goods' AND module_id=".$it["item_id"]);
      $goods_item = db::row("*", "".DB_PFX."goods", "id = ".$it["item_id"]);
      $cats_item = db::row("*", "".DB_PFX."goods_cat", "id = ".$goods_item['cat_id']);
			$output .= "<tr><td>";
			$output .= "<span class='label'>".$it["sitecatname"]."</span>";
			$output .= "</td><td>";
      
			$output .= "<a href='/$link' target='_blank'>".$cats_item['title']." ".$goods_item['title']." ".$it["sitename"];
      $output .= "</a> ";
			$output .= "</td>";
			$output .= "<td width = '100'>";
			if ($it["amount"]==1) $output .= $it["amount"];
			else $output .= $it["amount"]." x ".$it["siteprice"];
      
      /*if ($it["amount"]==1) $output .= round($it["amount"])." ".$units[ $goods_item['units_id'] ];
			else $output .= round($it["amount"])." ".$units[ $goods_item['units_id'] ]." x <br>(".$units[ $goods_item['units_id'] ]."/".round($it["siteprice"]).' <i class="fa fa-rub" aria-hidden="true"></i>)';*/
      
			$output .= "</td>";
			$output .= "<td style='text-align:right'><strong>";
			#$output .= $it["siteprice"]*$it["amount"];
      $output .= round($it["amount"] * $it["siteprice"]);
      
			$output .= "</strong></td></tr>";
		}
		$output .= '<tr>
		<td colspan="3" style="text-align:right">Итого:</td>
		<td width = "60" style="text-align:right"><strong>'.$sum.'</strong>&nbsp;<i class="fa fa-rub" aria-hidden="true"></i></td>
		</tr>';
		$output .= '</table>';
		}
		
		
		$output .= "</td>";
		$output .= "<td>";
		if ($row["comment_cust"])
		{
			$output .= "<div class='alert'>";
			$output .= nl2br($row["comment_cust"]);
			$output .= "</div>";
		}
		$output .= "<div class='well'><a href='#' id='cm{$row["id"]}' onclick='$(\"#comman{$row["id"]}\").toggle();return(false)'>";
		$output .= nl2br($row["comment_manager"]);
		$output .= "<i class='fas fa-pencil-alt' aria-hidden='true'></i></a><div style='display:none' id='comman{$row["id"]}'><textarea id='tcomman{$row["id"]}' class='comman'>{$row["comment_manager"]}</textarea><br><a class='btn' onclick='save_comm({$row["id"]})'>сохранить</a></div></div>";
		$output .= "</td>";
		$output .= "<td style='vertical-align:top; text-align:center'><a onclick='delete_item({$row["id"]})' class='btn btn-sm btn-danger float-right'  title='Удалить'><i class='fa fa-times' aria-hidden='true'></i></a><br style='clear:both'><br>$manager</td>";
		
		$output .= "</tr>";
		
		return $output;
		
		
}

if ($_GET["update"]) { echo show_orders_list(); die();}

if ($_POST["check"])
{
	$last=intval($_POST["check"]);
	// $last=mysql_result(mysql_query("SELECT MAX(`date_time`) FROM `basket_orders`"),0);
	// echo $last;
	$q = $PDO->query("SELECT * FROM `".DB_PFX."basket_orders` WHERE `shown`=0");
	if ( $q->rowCount() ) {
		$i=0;
		while ($row = $q->fetch()) 
		{
			if($i==0) header("Content-type: text/html; charset=utf-8");
			$i++;
			if (!in_array($row["id"],$_SESSION["shown"]))
			{
				show_row($row,true);
				$_SESSION["shown"][]=$row["id"];
			}
		}
	}
	die();
}

if ($_POST["del"])
{
	$del=intval($_POST["del"]);
	$q = $PDO->query("DELETE FROM `".DB_PFX."basket_orders` WHERE `id`=$del");
	die("ok");
	
}
if ($_POST["valu"] && $_POST["upd"])
{
	$upd=intval($_POST["upd"]);
	$status=intval($_POST["valu"]);
	$q = $PDO->query("UPDATE `".DB_PFX."basket_orders` SET `status`=$status, `manager_id`=".$_SESSION["WA_USER"]["id"]." WHERE `id`=$upd");
	die("ok");
	
}

if ($_POST["addr"] && $_POST["addr_c"])
{
	$upd = intval($_POST["addr_c"]);
	$addr = $_POST["addr"];
	$q = $PDO->query("UPDATE `".DB_PFX."basket_orders` SET `address`='$addr', `manager_id`=".$_SESSION["WA_USER"]["id"]." WHERE `id`=$upd");
	$addr = $addr;
	die($addr);
}
if ($_POST["save_id"])
{
	$upd=intval($_POST["save_id"]);
	if ($_POST["fio"])
	{
		$fio = $_POST["fio"];
		$q = $PDO->query("UPDATE `".DB_PFX."basket_orders` SET `fio`='$fio', `manager_id`=".$_SESSION["WA_USER"]["id"]." WHERE `id`=$upd");
		$ret=$fio;
	}	
	if ($_POST["phone"])
	{
		$phone=$_POST["phone"];
		$q = $PDO->query("UPDATE `".DB_PFX."basket_orders` SET `phone`='$phone', `manager_id`=".$_SESSION["WA_USER"]["id"]." WHERE `id`=$upd");
		$ret=$phone;
	}	
	if ($_POST["email"])
	{
		$email = $_POST["email"];
		$q = $PDO->query("UPDATE `".DB_PFX."basket_orders` SET `email`='$email', `manager_id`=".$_SESSION["WA_USER"]["id"]." WHERE `id`=$upd");
		$ret=$email;
	}
	die($ret);
}
if ($_POST["comman"] && $_POST["comm_id"])
{
	$upd=intval($_POST["comm_id"]);
	$addr=$_POST["comman"];
	$q = $PDO->query("UPDATE `".DB_PFX."basket_orders` SET `comment_manager`='$addr', `manager_id`=".$_SESSION["WA_USER"]["id"]." WHERE `id`=$upd");
	$addr=$addr;
	die($addr);
}
if ( isset($_GET["cid"]) && $_GET["cid"] ) $cid=$_GET["cid"];
elseif ( isset($_POST["cid"]) && $_POST["cid"]) $cid=$_POST["cid"];

if ( isset($_POST["id"]) && $_POST["id"]) $cid=$_POST["id"];
elseif (isset($_GET["id"]) && $_GET["id"])  $cid=$_GET["id"];

if (!isset($_SESSION["WA_USER"])) {
	include(WA_PATH.'index.php');
	exit;
}

$output = '';

$output .= <<<HTML
<div class="noprint">
<script>
function delete_item(id)
{
	if (confirm("Удалить заказ "+id+" ?"))
	{
		$.post("orders.php", { del: id }).done(function() 
		{ 
			$("#row_"+id).fadeOut("fast");
			$("#subrow_"+id).fadeOut("fast");
		});
	}
	return false;
}
function save_addr(id)
{
		var addr=$("#taddr"+id).val();
		$.post("orders.php", { addr_c: id, addr:addr }).done(function(data) 
		{ 
			$("#cr"+id).text(data);
			$("#addr"+id).fadeOut("fast");
		});
	return false;
}
function save_fio(id)
{
		var val=$("#fio"+id).val();
		$.post("orders.php", { save_id: id, fio:val }).done(function(data) 
		{ 
			$("#c_fio"+id).find(".valu").text(data);
			$("#c_fio"+id).addClass("alert-info");
		});
	return false;
}
function save_phone(id)
{
		var val=$("#phone"+id).val();
		$.post("orders.php", { save_id: id, phone:val }).done(function(data) 
		{ 
			$("#c_phone"+id).find(".valu").text(data);
			$("#c_phone"+id).addClass("alert-info");
		});
	return false;
}
function save_email(id)
{
		var val=$("#email"+id).val();
		$.post("orders.php", { save_id: id, email:val }).done(function(data) 
		{ 
			$("#c_email"+id).find(".valu").text(data);
			$("#c_email"+id).addClass("alert-info");
		});
	return false;
}
function save_comm(id)
{
		var tcomman=$("#tcomman"+id).val();
		$.post("orders.php", { comm_id: id, comman:tcomman }).done(function(data) 
		{ 
			$("#cm"+id).text(data);
			$("#comman"+id).fadeOut("fast");
		});
	return false;
}

$(function() {
  $(".editable").click(function(){
	  $(this).removeClass("alert-info");
  });

  $(".editable").mouseout(function(){
    /*$(this).addClass("alert-info");*/
  });




$(".onmap").click(function(){
	
	id=$(this).data("id");
	$("#addr_tel").html("<h2>"+$("#phone"+id).val()+"</h2><h2>"+$("#fio"+id).val()+"</h2><h3>"+$("#taddr"+id).val()+"</h3>")
	$("#yamapa").modal("show");
	var myMap;
	myMap = new ymaps.Map('yandex', {
    center:[56.833333,60.583333], 
    zoom:12
    });
	 var objects = ymaps.geoQuery(ymaps.geocode($("#cr"+id).text())).addToMap(myMap);
    

	myMap.controls
	.add('zoomControl', { left: 5, top: 5 })
    .add('typeSelector')
    .add('mapTools', { left: 35, top: 5 });
	$('#yamapa').on('hide', function () {
myMap.destroy();
})
})

$(".status").change(function(){
	var id=$(this).data("id");
	var valu=$(this).val();
		$.post("orders.php", {upd: id,  valu:valu}).done(function() 
		{ 
			$("#row_"+id).removeClass("table-success")
			$("#row_"+id).removeClass("table-danger")
			$("#row_"+id).removeClass("table-warning")
			$("#row_"+id).removeClass("table-info")
			$("#row_"+id).removeClass("ord_new")
			if (valu==1) $("#row_"+id).addClass("table-success")
			if (valu==2 || valu==3 || valu==5) $("#row_"+id).addClass("table-danger")
			if (valu==6 || valu==7 || valu==8) $("#row_"+id).addClass("table-warning")
			if (valu==9) $("#row_"+id).addClass("table-info")
		});
	
});

HTML;

$output .= '
setInterval (function(){
$.post( "orders.php", { check:'.time().' })
  .done(function(data) {
    if (data) {
	$("#ords").prepend(data);
	 
	}
  });
}, 5000);

// setInterval (function(){
// $(".ord_new").toggleClass("success");
// }, 1000);
});
</script>
';

if (!$_GET["id"]){
  
  $output .= show_orders_list();
  
}else {
	$q = $PDO->query("SELECT * FROM `".DB_PFX."basket_orders` WHERE `id`=".intval($_GET["id"]));
	$row = $q->fetch();
	$output .= "<h3>".date ("d.m.Y H:i:s",$row["date_time"])."</h3>";
	$output .= "<div>".$row["message"]."</div>";
	$output .= "<a href='orders.php' class='btn btn-large'>Назад</a>";
}

$output .= '</div>';
$output .= '
<!-- Button to trigger modal -->
 
<!-- Modal -->
</div>
<div id="yamapa" class="modal hide fade" style="height: 90%; top: 0; width: 99%; margin-left:3px; left:0" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
 
  <div class="modal-body" style="height:90%; max-height:1200px">
  <div  style="width:100%; height:100%" id="yandex"></div>
    
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Закрыть</button>
    <button class="btn btn-success" onclick="print()">Печать</button>
  </div>
</div>
';
$admin->header = "Корзина";
$admin->title = "Корзина";
$admin->cont_footer = "...";
$admin->setContent($output);
echo $admin->showAdmin('content');

