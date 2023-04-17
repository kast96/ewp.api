<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(!Loader::IncludeSharewareModule('ewp.api')) return;

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"TABLE_CLASS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_TABLE_CLASS"),
			"TYPE" => "STRING",
		),
		"LIST_FIELDS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_LIST_FIELDS"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
		"DEFAULT_LIST_FIELDS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_DEFAULT_LIST_FIELDS"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
		"DEFAULT_FILTER_FIELDS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_DEFAULT_FILTER_FIELDS"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
		"VIEW_LIST_FIELDS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_VIEW_LIST_FIELDS"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
		"LIST_FIELDS_EDIT_DISABLED" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_LIST_FIELDS_EDIT_DISABLED"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
		"MENU" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_MENU"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
		"CONTEXT_MENU" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("EWP_API_PARAMS_CONTEXT_MENU"),
			"TYPE" => "STRING",
      "MULTIPLE" => "Y"
		),
	),
);
