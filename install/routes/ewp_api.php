<?php
$file = $_SERVER["DOCUMENT_ROOT"]."/local/modules/ewp.api/routes/routes.php";
if(!file_exists($file)) $file = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ewp.api/routes/routes.php";
return include($file);