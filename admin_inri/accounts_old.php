<?php
require_once('lib/class.Admin.php');
$admin = new Admin();

global $PDO;
$table = DB_PFX."accounts";
$output = '';

if (!(isset($_SESSION["WA_USER"]) && $_SESSION["WA_USER"]["is_admin"])) include(WA_PATH.'index.php'); else{
  
	$continue = true;
	//==================================================================================================================
	//------------------------------------------------------------------------------------------------------------------
	//
	//														DELETE
	//
	//------------------------------------------------------------------------------------------------------------------
	//==================================================================================================================

	if (isset($_GET["delete"])){
		$id = intval($_GET["delete"]);
		$PDO->query("DELETE FROM `$table` WHERE `id` = '$id'");
	}
	//==================================================================================================================
	//------------------------------------------------------------------------------------------------------------------
	//
	//														UPDATE
	//
	//------------------------------------------------------------------------------------------------------------------
	//==================================================================================================================

	if (isset($_GET["update"])){
		if ($cat = get_row_by_id("$table", intval($_GET["update"]))){
			$login = addslashes($_POST["login"]);
			$fullname = addslashes($_POST["fullname"]);
      $is_programmer=intval($_POST["is_programmer"]);
			$ismanag=intval($_POST["ismanag"]);
			$iscontent=intval($_POST["iscontent"]);
			$iscatalog=intval($_POST["iscatalog"]);
			if (isset($_POST["isadmin"]) && ($_POST["isadmin"] == 1)) $isadmin = 1; else $isadmin = 0;
			if (isset($_POST["password"]) && $_POST["password"] !== ''){
				$password = $_POST["password"];
				$key = new_key();
				$hash = adm_password_hash($password, $key);
				$PDO->query("UPDATE `$table` SET `login`='$login', `fullname`='$fullname', `is_admin`='$isadmin', `key`='$key', `hash`='$hash',`is_programmer`='$is_programmer', `ismanag`='$ismanag', `isjournalist`='$isjournalist', `iscontent`='$iscontent', `iscatalog`='$iscatalog' WHERE `id` = '".$cat["id"]."'");
			}else{
				$PDO->query("UPDATE `$table` SET `login`='$login', `fullname`='$fullname', `is_admin`='$isadmin', `is_programmer`='$is_programmer', `ismanag`='$ismanag', `iscontent`='$iscontent', `isjournalist`='$isjournalist', `iscatalog`='$iscatalog' WHERE `id` = '".$cat["id"]."'");
			}
		}
	}
	//==================================================================================================================
	//------------------------------------------------------------------------------------------------------------------
	//
	//														CREATE
	//
	//------------------------------------------------------------------------------------------------------------------
	//==================================================================================================================

	if (isset($_GET["create"])){
		//echo 'Matched.';
		$login = addslashes($_POST["login"]);
		$password = $_POST["password"];
		$key = new_key();
		$hash = adm_password_hash($password, $key);
		$fullname = addslashes($_POST["fullname"]);
		if (isset($_POST["isadmin"]) && ($_POST["isadmin"] == 1)) $isadmin = 1; else $isadmin = 0;
    $is_programmer=intval($_POST["is_programmer"]);
		$ismanag=intval($_POST["ismanag"]);
		$iscontent=intval($_POST["iscontent"]);
		$iscatalog=intval($_POST["iscatalog"]);
		$isjournalist=intval($_POST["isjournalist"]);
		$PDO->query("INSERT INTO `$table` SET `login`='$login', `key`='$key', `hash`='$hash', `fullname`='$fullname', `is_admin`='$is_admin', `is_programmer`='$is_programmer', `ismanag`='$ismanag', `iscontent`='$iscontent', `iscatalog`='$iscatalog', `isjournalist`='$isjournalist'") or (die(mysql_error()));
		//echo mysql_error();
	}

	//==================================================================================================================
	//------------------------------------------------------------------------------------------------------------------
	//
	//														EDIT
	//
	//------------------------------------------------------------------------------------------------------------------
	//==================================================================================================================

	if (isset($_GET["edit"])){
		if ($cat = get_row_by_id("$table", intval($_GET["edit"]))){
			#$output .= '<H1>ACCOUNTS: edit</H1>';
      $output .= AllFunction::setHeaderForAdm('????????????????????????', '???????????????????????????? ??????????????', $admin);
      
			$output .=  '<FORM method="post" action="'.IA_URL.'accounts.php?update='.$cat["id"].'">';
			$output .= '??????????:<BR/><INPUT type="text" name="login" value="'.htmlspecialchars($cat["login"]).'"><BR/><BR/>';
			$output .= '?????????? ???????????? (??????????????????????????):<BR/><INPUT type="password" name="password" value=""><BR/><BR/>';
			$output .= '???????????? ??????:<BR/><INPUT type="text" name="fullname" value="'.htmlspecialchars($cat["fullname"]).'"><BR/><BR/>';
			
					$output .= '<INPUT type="checkbox" name="isadmin" value="1"';
					if ($cat["is_admin"]) $output .= ' checked';
					$output .= '> ?????????????????????????? (????????????????????)<BR/>';
          
					$output .= '<INPUT type="checkbox" name="is_programmer" value="1"';
					if ($cat["is_programmer"]) $output .= ' checked';
					$output .= '> ?????????????????????? <br/>';
          
          $output .= '<INPUT type="checkbox" name="ismanag" value="1"';
					if ($cat["ismanag"]) $output .= ' checked';
					$output .= '> ???????????????? <br/>';
          
					$output .= '<INPUT type="checkbox" name="iscontent" value="1"';
					if ($cat["iscontent"]) $output .= ' checked';
					$output .= '> ??????????????-????????????????<br/><br/>';
          
					$output .= '<!--<INPUT type="checkbox" name="iscatalog" value="1"';
					if ($cat["iscatalog"]) $output .= ' checked';
					$output .= '> ???????????????? ????????????????<br/><br/>';
					$output .= '<INPUT type="checkbox" name="isjournalist" value="1"';
					if ($cat["isjournalist"]) $output .= ' checked';
					$output .= '> ??????????????????--!><br/><br/>';
		
			
			$output .= '<INPUT type="submit" class="btn btn-large btn-success" value="??????????????????"><BR/><BR/>';
			$output .= '</FORM>';
			$continue = false;
		}
	}

	//==================================================================================================================
	//------------------------------------------------------------------------------------------------------------------
	//
	//														ADD
	//
	//------------------------------------------------------------------------------------------------------------------
	//==================================================================================================================

	if (isset($_GET["add"])){
		#$output .= '<H1>ACCOUNTS: add</H1>';
    $output .= AllFunction::setHeaderForAdm('????????????????????????', '???????????????????? ??????????????', $admin);
		$output .=  '<FORM method="post" action="'.IA_URL.'accounts.php?create">';
		$output .= '??????????:<BR/><INPUT type="text" name="login" value=""><BR/><BR/>';
		$output .= '????????????:<BR/><INPUT type="password" name="password" value=""><BR/><BR/>';
		$output .= '???????????? ??????:<BR/><INPUT type="text" name="fullname" value=""><BR/><BR/>';
		$output .= '<INPUT type="checkbox" name="isadmin" value="1"> ?????????????????????????? (????????????????????)<BR/>';
    $output .= '<INPUT type="checkbox" name="is_programmer" value="1"> ?????????????????????? <br/>';
		$output .= '<INPUT type="checkbox" name="ismanag" value="1"> ???????????????? <br/>';
		$output .= '<INPUT type="checkbox" name="iscontent" value="1"> ??????????????-????????????????<br/>';
		$output .= '<!--<INPUT type="checkbox" name="iscatalog" value="1"> ???????????????? ????????????????<br/>';
		$output .= '<INPUT type="checkbox" name="isjournalist" value="1"> ??????????????????<br/>--><br/><br/>';
		$output .= '<INPUT type="submit" value="??????????????????"><BR/><BR/>';
		$output .= '</FORM>';
		$continue = false;
	}

	//==================================================================================================================
	//------------------------------------------------------------------------------------------------------------------
	//
	//														PRIMARY
	//
	//------------------------------------------------------------------------------------------------------------------
	//==================================================================================================================
	if ($continue){
		#$output .= '<H1>????????????????????????</H1>';
    $output .= AllFunction::setHeaderForAdm('????????????????????????', '????????????????????????', $admin);

		$query = $PDO->query("SELECT * FROM `$table`");
		$r = 0;
		if ($query->rowCount()){
			$output .= '<TABLE class="table table-striped">';
			$output .= '<TR><TH style="width:100px">??????????</TH><TH>??????</TH><TH>??????????</TH><TH style="width:100px">????????????????</TH></TR>';
			while ($account = $query->fetch()){
				$output .= '<TR><TD>'.htmlspecialchars($account["login"]).'</TD><TD>'.htmlspecialchars($account["fullname"]).'</TD><TD>';
				if ($account["is_admin"]) $output .= '??????????????????????????<br>';
				if ($account["iscontent"]) $output .= '??????????????-????????????????<br>';
        if ($account["is_programmer"]) $output .= '?????????????????????? <br>';
				if ($account["ismanag"]) $output .= '???????????????? <br>';
				//if ($account["iscatalog"]) $output .= '???????????????? ????????????????<br>';
				//if ($account["isjournalist"]) $output .= '??????????????????<br>';
				$output .= '</TD>';
				$output .= '<TD><A href="'.IA_URL.'accounts.php?edit='.$account["id"].'">????????????????</A>&nbsp;&nbsp;<A href="'.IA_URL.'accounts.php?delete='.$account["id"].'">??????????????</A></TD>';
				$output .= '</TR>';
			}
			$output .= '</TABLE>';
			if (TRUE){ // if admin
			$output .= '<A href="'.IA_URL.'accounts.php?add">?????????? ????????????????????????</A>';
			}
		}else{
			$output .= '<A href="'.IA_URL.'accounts.php?add">add first, superuser account</A>';
		}
	}
}

$admin->setContent($output);
echo $admin->showAdmin('content');
?>