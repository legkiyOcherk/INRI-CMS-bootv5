<?php

$CFG = array(
#db_config
	"db_hostname" => "localhost",
	"db_username" => "cms",
	"db_password" => "TpZPbJGsNbKytehD",
	"db_basename" => "cms" 
#end_db_config
);

$_SESSION["DB_OPENED"] = FALSE;
$_SESSION["NEX_CFG"] =& $CFG;
