<?php
use \Bitrix\Main\Routing\RoutingConfigurator;
use \Ewp\Api\V1\Controllers\UsersController;
use \Ewp\Api\V1\Controllers\ProductsController;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

define('API_PATH', '/api/v1');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
header('Access-Control-Allow-Headers: X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version, EWP-API-Token');

return function (RoutingConfigurator $routes)
{
	$routes->any(API_PATH.'/profile', [UsersController::class, 'profile'])->methods(['GET', 'OPTIONS']);
	$routes->any(API_PATH.'/auth', [UsersController::class, 'auth'])->methods(['POST', 'OPTIONS']);
	
	$routes->any(API_PATH.'/products/{id}', [ProductsController::class, 'id'])->where('id', '[\d]+')->methods(['GET', 'OPTIONS']);
	$routes->any(API_PATH.'/products', [ProductsController::class, 'list'])->methods(['GET', 'OPTIONS']);

	$routes->any(API_PATH.'/search/{search}', function($search){
		return "search {$search} response";
	})->where('search', '.*')->methods(['GET', 'OPTIONS']);
};