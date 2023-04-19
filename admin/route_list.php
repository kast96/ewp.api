<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Context;
use \Ewp\Api\Main;
use \Ewp\Api\Tables\ApiTable;
use \Ewp\Api\Tables\RouteTable;

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php';

Loc::loadMessages(__FILE__);

global $APPLICATION;

if (!Loader::includeSharewareModule('ewp.api'))
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('EWP_API_ROUTE_LIST_REQUIRE_MODULE')
	]);
}
else
{
	$request = Context::getCurrent()->getRequest();
	$API_ID = (int)$request->get('API_ID');
	$arApi = ApiTable::getList(['filter' => ['ID' => $API_ID], 'select' => ['ID', 'NAME']])->fetch();

	if (!$arApi['ID'])
	{
		\CAdminMessage::ShowMessage([
			'TYPE' => 'ERROR',
			'MESSAGE' => Loc::getMessage('EWP_API_ROUTE_LIST_REQUIRE_API_ID')
		]);
	}
	else
	{
		$APPLICATION->SetTitle($arApi['NAME'].': Список роутов');

		$APPLICATION->IncludeComponent(
			'ewp:api.admin.grid.list',
			'',
			[
				'TABLE_CLASS' => RouteTable::class,
				'LIST_FIELDS' => [
					'ID',
					'NAME',
					'PATH',
					'ACTIVE',
					'METHOD',
					'CONTROLLER',
					'CONTROLLER_METHOD',
				],
				'DEFAULT_LIST_FIELDS' => [
					'ID',
					'NAME',
					'PATH',
					'ACTIVE',
					'METHOD',
					'CONTROLLER',
					'CONTROLLER_METHOD',
				],
				'DEFAULT_FILTER_FIELDS' => [
					'ID',
					'NAME',
					'PATH',
					'ACTIVE',
				],
				'VIEW_LIST_FIELDS' => [
					'PATH' => function($arItem)
						{
							return Option::get(Main::getModuleId(), 'API_PATH').$arItem['PATH'];
						},
					'METHOD' => function($arItem)
						{
							return implode('/', unserialize($arItem['METHOD']));
						},
				],
				'LIST_FIELDS_EDIT_DISABLED' => [
					'METHOD',
					'CONTROLLER',
					'CONTROLLER_METHOD',
				],
				'MENU' => [
					'EDIT' => [
						'TEXT'	=> Loc::getMessage('EWP_API_ROUTE_LIST_CREATE'),
						'TITLE' => Loc::getMessage('EWP_API_ROUTE_LIST_CREATE'),
						'LINK' => 'ewp_api_route_edit.php?API_ID='.$arApi['ID'].'&lang='.LANG,
					]
				],
				'CONTEXT_MENU' => [
					'EDIT' => [
						'TEXT' => Loc::getMessage('EWP_API_ROUTE_LIST_EDIT'),
						'TITLE' => Loc::getMessage('EWP_API_ROUTE_LIST_EDIT'),
						'LINK' => 'ewp_api_route_edit.php?API_ID='.$arApi['ID'].'&lang='.LANG.'&ID=#ID#',
					],
					'ACTIVE' => [],
					'DELETE' => [
						'TEXT' => Loc::getMessage('EWP_API_ROUTE_LIST_DELETE'),
						'TITLE' => Loc::getMessage('EWP_API_ROUTE_LIST_DELETE'),
					],
				],
			]
		);
	}
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';