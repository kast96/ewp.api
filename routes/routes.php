<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Routing\RoutingConfigurator;
use \Ewp\Api\Main;
use \Ewp\Api\Tables\ApiTable;
use \Ewp\Api\Tables\RouteTable;

Loc::loadMessages(__FILE__);
Loader::includeSharewareModule('ewp.api');

$apiPath = Option::get(Main::getModuleId(), 'API_PATH');

define('API_PATH', $apiPath);
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version, EWP-API-Token');

return function (RoutingConfigurator $routes)
{
	//Данные
  $arRoutes = [];
	$rsRoutes = RouteTable::getList(['filter' => ['ACTIVE' => 'Y'], 'select' => ['ID', 'PATH', 'API_ID', 'CONTROLLER', 'CONTROLLER_METHOD', 'METHOD', 'PARAMS']]);
	while ($arRoute = $rsRoutes->fetch())
	{
    $arRoute['METHOD'] = unserialize($arRoute['METHOD']);
    $arRoute['PARAMS'] = unserialize($arRoute['PARAMS']) ?: [];
		$arRoutes[] = $arRoute;
	}

	$arApies = [];
	$rsApies = ApiTable::getList(['filter' => ['ACTIVE' => 'Y'], 'select' => ['ID', 'PATH']]);
	while ($arApi = $rsApies->fetch())
	{
    $arApi['ROUTES'] = array_filter($arRoutes, function($arRoute) use ($arApi) {
      return $arRoute['API_ID'] == $arApi['ID'];
    });
		$arApies[] = $arApi;
	}

	//Роуты
	$routes->any(API_PATH.'/', function(){
		return "EWP API Running";
	})->methods(['GET', 'OPTIONS']);

	foreach ($arApies as $arApi)
	{
		$apiPath = API_PATH.$arApi['PATH'];

    if ($arApi['ROUTES'])
    {
      foreach ($arApi['ROUTES'] as $arRoute)
      {
				$routes->any($apiPath.$arRoute['PATH'], function() use ($arRoute) {
					$controller = new $arRoute['CONTROLLER'];
					$action = $arRoute['CONTROLLER_METHOD'].'Action';
					return $controller->$action($arRoute['PARAMS']);
				})->methods(['GET', 'OPTIONS']);
      }
    }
	}
};