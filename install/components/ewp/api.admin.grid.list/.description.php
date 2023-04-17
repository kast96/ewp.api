<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = [
	"NAME" => Loc::getMessage("EWP_API_COMP_ADMIN_GRID_LIST_NAME"),
	"DESCRIPTION" => Loc::getMessage("EWP_API_COMP_ADMIN_GRID_LIST_DESCRIPTION"),
	"SORT" => 100,
	"PATH" => [
		"ID" => "content",
		"CHILD" => [
			"ID" => "ewp_api",
			"NAME" => Loc::getMessage("EWP_API_COMP_API"),
			"SORT" => 100,
			"CHILD" => [
        "ID" => "ewp",
        "NAME" => Loc::getMessage("EWP_API_COMP"),
        "SORT" => 100,
      ],
    ],
  ],
];
?>