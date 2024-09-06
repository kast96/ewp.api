<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;
use \Ewp\Api\Tables\ApiTable;
use \Ewp\Api\Main;

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin.php';

Loc::loadMessages(__FILE__);

global $APPLICATION;

if (!Loader::includeSharewareModule('ewp.api'))
{
	\CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('EWP_API_API_LIST_REQUIRE_MODULE')
	]);
}
else
{
	$APPLICATION->SetTitle(Loc::getMessage('EWP_API_API_LIST_TITLE'));

	$APPLICATION->IncludeComponent(
		'ewp:api.admin.grid.list',
		'',
		[
			'TABLE_CLASS' => ApiTable::class,
			'LIST_FIELDS' => [
				'ID',
				'NAME',
				'PATH',
				'ACTIVE',
			],
			'DEFAULT_LIST_FIELDS' => [
				'ID',
				'NAME',
				'PATH',
				'ACTIVE',
			],
			'DEFAULT_FILTER_FIELDS' => [
				'ID',
				'NAME',
				'PATH',
				'ACTIVE',
			],
			'VIEW_LIST_FIELDS' => [
				'PATH' => Option::get(Main::getModuleId(), 'API_PATH').'/#PATH#',
			],
			'LIST_FIELDS_EDIT_DISABLED' => [
			],
			'MENU' => [
				'EDIT' => [
					'TEXT'	=> Loc::getMessage('EWP_API_API_LIST_CREATE'),
					'TITLE' => Loc::getMessage('EWP_API_API_LIST_CREATE'),
					'LINK' => 'ewp_api_edit.php?lang='.LANG
				]
			],
			'CONTEXT_MENU' => [
				'ROUTES' => [
					'TEXT' => Loc::getMessage('EWP_API_API_LIST_ROUTES'),
					'TITLE' => Loc::getMessage('EWP_API_API_LIST_ROUTES'),
					'LINK' => 'ewp_api_route_list.php?API_ID=#ID#&lang='.LANG.'',
					'DEFAULT' => 'Y',
				],
				'EDIT' => [
					'TEXT' => Loc::getMessage('EWP_API_API_LIST_EDIT'),
					'TITLE' => Loc::getMessage('EWP_API_API_LIST_EDIT'),
					'LINK' => 'ewp_api_edit.php?lang='.LANG.'&ID=#ID#',
				],
				'ACTIVE' => [],
				'DELETE' => [
					'TEXT' => Loc::getMessage('EWP_API_API_LIST_DELETE'),
					'TITLE' => Loc::getMessage('EWP_API_API_LIST_DELETE'),
				],
			],
		]
	);
}

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';