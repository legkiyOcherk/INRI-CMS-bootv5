<?php

$CFG = array(
#db_config
	"db_hostname" => "localhost",
	"db_username" => "",
	"db_password" => "",
	"db_basename" => "" 
#end_db_config
);

$_SESSION["DB_OPENED"] = FALSE;
$_SESSION["NEX_CFG"] =& $CFG;
