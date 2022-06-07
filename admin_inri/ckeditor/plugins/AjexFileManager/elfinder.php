<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<?php include("../../../config/config.php"); ?>

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>elFinder</title>

    <link href="css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet">
    <link rel="stylesheet" href="css/elfinder.css" type="text/css" media="screen" title="no title" charset="utf-8">

    <script type="text/javascript" src="js/jquery-1.6.1.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>

    <script src="js/elfinder.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/i18n/elfinder.ru.js" type="text/javascript" charset="utf-8"></script>
</head>

<body>

	<div id="finder">finder</div>

<script type="text/javascript" charset="utf-8">

    var funcNum = window.location.search.replace(/^.*CKEditorFuncNum=(\d+).*$/, "$1");
    var langCode = window.location.search.replace(/^.*langCode=([a-z]{2}).*$/, "$1");

    $('#finder').elfinder({
       url : 'connectors/php/connector.php',
       height: 400,
       lang : 'ru',
       places : '',
       cutURL : '<?php echo SERVER_URL; ?>',
       disableShortcuts : true,
       editorCallback : function(url) {
          window.opener.CKEDITOR.tools.callFunction(funcNum, url);
          window.close();
       }
    })

</script>

</body>
</html>
