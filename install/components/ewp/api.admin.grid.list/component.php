<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock\Grid\Panel\GroupAction;
use \Bitrix\Iblock\Grid\ActionType;

Loc::loadMessages(__FILE__);

global $adminSidePanelHelper;

$currentUser = [
	'ID' => $USER->GetID(),
	'GROUPS' => $USER->GetUserGroupArray()
];

$arResult = [];
if (!is_array($arParams['LIST_FIELDS'])) $arParams['LIST_FIELDS'] = [];
if (!is_array($arParams['DEFAULT_LIST_FIELDS'])) $arParams['DEFAULT_LIST_FIELDS'] = [];
if (!is_array($arParams['DEFAULT_FILTER_FIELDS'])) $arParams['DEFAULT_FILTER_FIELDS'] = [];

if (!$arParams['TABLE_CLASS'] || !$class = new ($arParams['TABLE_CLASS']))
{
  \CAdminMessage::ShowMessage([
		'TYPE' => 'ERROR',
		'MESSAGE' => Loc::getMessage('EWP_API_ADMIN_LIST_REQUIRE_TABLE_NAME')
	]);
  return;
}

$sTableID = 'tbl_'.$class::getTableName();
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");

global $by, $order;
if (!isset($by)) $by = 'ID';
if (!isset($order))	$order = 'asc';
$by = mb_strtoupper($by);

$lAdmin = new CAdminUiList($sTableID, $oSort);
$lAdmin->bMultipart = true;
//$lAdmin->setPublicModeState(false);

$bExcel = $lAdmin->isExportMode();



$groupParams = array(
	'ENTITY_ID' => $tableId
);

$actionList = array();
$actionList[] = ActionType::DELETE;
$actionList[] = ActionType::EDIT;
$actionList[] = ActionType::SELECT_ALL;
$actionList[] = ActionType::ACTIVATE;
$actionList[] = ActionType::DEACTIVATE;

$panelAction = new GroupAction($groupParams);
$lAdmin->AddGroupActionTable($panelAction->getList($actionList));

//Формирование массива полей
$arTableFields = [];
$arFieldsClass = $class::getMap();
foreach ($arFieldsClass as $arFieldClass)
{
	if (!in_array($arFieldClass->getName(), $arParams['LIST_FIELDS'])) continue;
	
	$arTableField = [
		'ID' => $arFieldClass->getName(),
		'NAME' => $arFieldClass->getTitle(),
	];

	switch ((new \ReflectionClass($arFieldClass))->getShortName()) {
		case 'IntegerField':
			$arTableField['TYPE'] = 'number';
			break;

		case 'BooleanField':
			$arTableField['TYPE'] = 'list';
			$arTableField['LIST_TYPE'] = 'boolean';
			$arTableField['ITEMS'] = [
				"Y" => Loc::getMessage('EWP_API_ADMIN_LIST_YES'),
				"N" => Loc::getMessage('EWP_API_ADMIN_LIST_NO')
			];
			break;
		
		default:
			$arTableField['TYPE'] = 'string';
			break;
	}

	$arTableFields[] = $arTableField;
}

//Формирование фильтра
$filterFields = [];
foreach ($arTableFields as $key => $arField)
{
	$filterFields[] = [
		"id" => $arField['ID'],
		"name" => $arField['NAME'],
		"filterable" => $arField['TYPE'] == 'string' ? '?' : '',
		"quickSearch" => $arField['ID'] == 'NAME' ? '?' : NULL,
		"default" => in_array($arField['ID'], $arParams['DEFAULT_FILTER_FIELDS']),
		"type" => $arField['TYPE'],
		"items" => $arField['ITEMS'],
	];
}

$arFilter = [];
$lAdmin->AddFilter($filterFields, $arFilter);


//Групповое редактирование
if($lAdmin->EditAction())
{
	if (!empty($_FILES['FIELDS']) && is_array($_FILES['FIELDS']))
		CFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);

	if (is_array($_POST['FIELDS']))
	{
		$obj = new $class;

		foreach($_POST['FIELDS'] as $ID => $arFields)
		{
			if(!$lAdmin->IsUpdated($ID))
				continue;
			
			$ID = (int)$ID;

			$arRes = $class::getById($ID)->fetch();
			if(!$arRes)
				continue;

			//All not displayed required fields from DB
			foreach($arIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if(
					$field["IS_REQUIRED"] === "Y"
					&& !array_key_exists($FIELD_ID, $arFields)
					&& $FIELD_ID !== "DETAIL_PICTURE"
					&& $FIELD_ID !== "PREVIEW_PICTURE"
				)
					$arFields[$FIELD_ID] = $arRes[$FIELD_ID];
			}

			$arFields["MODIFIED_BY"] = $currentUser['ID'];
			
			$DB->StartTransaction();

			if(!$obj->update($ID, $arFields))
			{
				$lAdmin->AddGroupError(GetMessage("EWP_API_ADMIN_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_TEXT#" => $obj->LAST_ERROR)), $ID);
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}
		}

		unset($ib);
	}
}

//Групповые действия
if ($arID = $lAdmin->GroupAction())
{
	$actionId = $lAdmin->GetAction();
	$actionParams = null;
	if (is_string($actionId))
	{
		$actionParams = $panelAction->getRequest($actionId);
	}

	if ($actionId !== null && $actionParams !== null)
	{
		if ($lAdmin->IsGroupActionToAll())
		{
			$arID = array();
			$rsData = $class::getList([
				'filter' => $arFilter,
				'select' => ['ID']
			]);
			while ($arRes = $rsData->Fetch())
			{
				$arID[] = $arRes['ID'];
			}
			unset($arRes, $rsData);
		}

		$obj = new $class;

		foreach ($arID as $ID)
		{
			$ID = (int)$ID;
			if ($ID <= 0)
				continue;

			$arRes = $class::getById($ID)->fetch();
			if (!$arRes)
				continue;

			switch ($actionId)
			{
				case ActionType::DELETE:
					@set_time_limit(0);
					$DB->StartTransaction();
					$APPLICATION->ResetException();
					if (!$class::delete($ID))
					{
						$DB->Rollback();
						if ($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError(Loc::getMessage("EWP_API_ADMIN_DELETE_ERROR")." [".$ex->GetString()."]", $ID);
						else
							$lAdmin->AddGroupError(Loc::getMessage("EWP_API_ADMIN_DELETE_ERROR"), $ID);
					}
					else
					{
						$DB->Commit();
					}
					break;
				case ActionType::ACTIVATE:
				case ActionType::DEACTIVATE:
					$obj->LAST_ERROR = '';
					$arFields = array("ACTIVE" => ($actionId == ActionType::ACTIVATE ? "Y" : "N"));
					$arFields['MODIFIED_BY'] = $currentUser['ID'];
					if (!$obj::update($ID, $arFields))
						$lAdmin->AddGroupError(GetMessage("EWP_API_ADMIN_UPDATE_ERROR").$obj->LAST_ERROR, $ID);
					break;
			}
		}
		unset($obj);
	}
	
	unset($actionParams);
	unset($actionId);

	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}

	if(isset($return_url) && $return_url <> '')
		LocalRedirect($return_url);
}

//Заголовки таблицы
$arHeaders = [];
foreach ($arTableFields as $key => $arField)
{
  $arHeaders[] = [
    'id' => $arField['ID'],
    'content' => $arField['NAME'],
    'sort' => $arField['ID'],
    'default' => in_array($arField['ID'], $arParams['DEFAULT_LIST_FIELDS'])
  ];
}

$lAdmin->AddHeaders($arHeaders);
foreach ($arTableFields as $key => $arField)
{
	$lAdmin->AddVisibleHeaderColumn($arField['ID']);
}

//Элементы таблицы
$params = [
	'order' => [$by => $order, 'ID' => 'ASC'],
	'filter' => $arFilter
];
$rsItems = $class::getList($params);

$rsItems = new CAdminUiResult($rsItems, $sTableID);
$rsItems->NavStart();
$lAdmin->SetNavigationParams($rsItems, []);

while($arRes = $rsItems->NavNext(true))
{
	$row = $lAdmin->AddRow($arRes['ID'], $arRes);

	foreach ($arTableFields as $key => $arField)
	{
		if ($arField['ID'] == 'ID' || in_array($arField['ID'], $arParams['LIST_FIELDS_EDIT_DISABLED']))
			continue;

		if ($arField['LIST_TYPE'] == 'boolean')
		{
			$row->AddCheckField($arField['ID']);
		}
		else
		{
			$row->AddInputField($arField['ID'], ['size' => 20]);
			if ($arParams['VIEW_LIST_FIELDS'][$arField['ID']])
			{
				$arFieldKeys = array_keys($arRes);
				$arFieldKeysMasks = array_map(function($item){
					return '#'.$item.'#';
				}, $arFieldKeys);
				$txt = str_replace($arFieldKeysMasks, $arRes, $arParams['VIEW_LIST_FIELDS'][$arField['ID']]);
				$row->AddViewField($arField['ID'], $txt);
			}
		}
	}


	//Кнопки в контекстном меню
	$arActions = [];
	if ($arParams['CONTEXT_MENU'])
	{
		foreach ($arParams['CONTEXT_MENU'] as $code => $arMenu)
		{
			if ($arMenu['LINK'])
			{
				$arMenu['LINK'] = str_replace('#ID#', $arRes['ID'], $arMenu['LINK']);
			}

			switch ($code) {
				case 'ACTIVE':
					if ($row->arRes["ACTIVE"]) {
						if ($row->arRes["ACTIVE"] == "Y")
						{
							$arActive = [
								'ID' => 'deactivate',
								"TEXT" => Loc::getMessage('EWP_API_ADMIN_DEACTIVATE'),
								"TITLE" => Loc::getMessage('EWP_API_ADMIN_DEACTIVATE'),
								"ACTION" => $lAdmin->ActionDoGroup($row->arRes['ID'], ActionType::DEACTIVATE),
								"ONCLICK" => "",
							];
						}
						else
						{
							$arActive = [
								'ID' => 'activate',
								"TEXT" => Loc::getMessage('EWP_API_ADMIN_ACTIVATE'),
								"TITLE" => Loc::getMessage('EWP_API_ADMIN_ACTIVATE'),
								"ACTION" => $lAdmin->ActionDoGroup($row->arRes['ID'], ActionType::ACTIVATE),
								"ONCLICK" => "",
							];
						}
				
						$arActions[] = $arActive;
					}
					break;

				case 'DELETE':
					if (!$arMenu['TEXT']) $arMenu['TEXT'] = Loc::getMessage('EWP_API_ADMIN_DELETE');
					if (!$arMenu['TITLE']) $arMenu['TITLE'] = Loc::getMessage('EWP_API_ADMIN_DELETE');

					$arActions[] = [
						'ID' => 'delete',
						"TEXT" => $arMenu['TEXT'],
						"TITLE" => $arMenu['TITLE'],
						"ACTION" => $lAdmin->ActionDoGroup($row->arRes['ID'], ActionType::DELETE),
						"ONCLICK" => "",
					];
					break;

				default:
					if (!$arMenu['TEXT'] && $code == 'EDIT') $arMenu['TEXT'] = Loc::getMessage('EWP_API_ADMIN_EDIT');
					if (!$arMenu['TITLE'] && $code == 'EDIT') $arMenu['TITLE'] = Loc::getMessage('EWP_API_ADMIN_EDIT');

					$arActions[] = [
						"DEFAULT" => $arMenu['DEFAULT'],
						"TEXT" => $arMenu['TEXT'],
						"TITLE" => $arMenu['TITLE'],
						"ACTION" => $lAdmin->ActionRedirect($arMenu['LINK']),
					];
					break;
			}
		}
	}

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	[
		["title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsItems->SelectedRowsCount()],
		["counter" => true, "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"],
	]
);

// context menu
$aMenu = [];
if (is_array($arParams['MENU'])) {
	foreach ($arParams['MENU'] as $arMenu) {
		$aMenu[] = [
			'TEXT'	=> $arMenu['TEXT'],
			'TITLE' => $arMenu['TITLE'],
			'LINK' => $arMenu['LINK'],
		];
	}
}

$aContext = $aMenu;
$lAdmin->AddAdminContextMenu($aContext);


// check list output mode
$lAdmin->CheckListMode();

$lAdmin->DisplayFilter($filterFields);

$arResult['TABLE'] = $lAdmin;

$this->includeComponentTemplate();