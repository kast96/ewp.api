<?
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\Tables\ApiTable;

$accessLevel = (string)$APPLICATION->GetGroupRight('ewp.api');

if ($accessLevel > 'D' && Loader::includeSharewareModule('ewp.api'))
{
	Loc::loadMessages(__FILE__);

	$arApies = [];
	$rsApies = ApiTable::getList(['select' => ['ID', 'NAME']]);
	while ($arApi = $rsApies->fetch())
	{
		$arApies[] = $arApi;
	}

	$arApiesMenu = [];
	foreach ($arApies as $arApi)
	{
		$arApiesMenu[] = [
			'text' => $arApi['NAME'],
			'title' => $arApi['NAME'],
			'url' => 'ewp_api_route_list.php?API_ID='.$arApi['ID'].'&lang='.LANGUAGE_ID.'&set_filter=Y&apply_filter=Y',
			'more_url' => [
				'ewp_api_route_list.php?API_ID='.$arApi['ID'],
				'ewp_api_route_edit.php?API_ID='.$arApi['ID'],
			],
		];
	}

	$arMenu = [
		[
			'parent_menu' => 'global_menu_services',
			'sort' => 100,
			'text' => Loc::getMessage('EWP_API_MENU_TURBO_ROOT'),
			'title' => Loc::getMessage('EWP_API_MENU_TURBO_ROOT'),
			'icon' => 'ewp_api_icon',
			'items_id' => 'menu_ewp_api',
			'items' => [
				[
					'text' => Loc::getMessage('EWP_API_MENU_LIST'),
					'title' => Loc::getMessage('EWP_API_MENU_LIST'),
					'url' => 'ewp_api_list.php?lang='.LANGUAGE_ID.'&set_filter=Y&apply_filter=Y',
					'more_url' => [
						'ewp_api_list.php',
						'ewp_api_edit.php',
					],
					'items' => $arApiesMenu,
				],
			],
		],
	];

	return $arMenu;
}
else
{
	return false;
}