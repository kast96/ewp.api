<?
foreach(array('local', 'bitrix') as $vendor)
{
  $file = $_SERVER["DOCUMENT_ROOT"].'/'.$vendor.'/modules/ewp.api/admin/route_edit.php';
  if(file_exists($file)) return include($file);
}