<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>elFinder</title>

</head>

<body>

	<div id="finder">finder</div>

	<script type="text/javascript" charset="utf-8">

			var f = $('#finder').elfinder({

				url : '/wordpad/plugins/AjexFileManager/connectors/php/connector.php',
				lang : 'ru',
                places : '',
				height : 500,

				editorCallback : function(url) {
					if (window.console && window.console.log) {
						window.console.log(url);
					} else {
						alert(url);
					}

				},
				closeOnEditorCallback : true
				// docked : true,
				// dialog : {
				// 	title : 'File manager',
				// }
			});

	</script>

</body>
</html>
