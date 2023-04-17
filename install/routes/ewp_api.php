<?
foreach(array('local', 'bitrix') as $folder) {
  $file = $_SERVER["DOCUMENT_ROOT"].'/'.$folder.'/modules/ewp.api/routes/routes.php';
  if(file_exists($file)) return include($file);
}