<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl" lang="pl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<BODY>
<?php
session_start();
session_unset();
header('Location: logowanie.php');
exit();
?>
</BODY>
</html>