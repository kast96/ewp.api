<?
namespace Ewp\Api\Controller;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\ActionFilter\AuthenticationToken;

Loc::loadMessages(__DIR__);

class IblockController extends BaseController
{
	public function configureActions(): array
	{
		return [
			'getList' => [
				'prefilters' => [
				]
			],
			'getById' => [
				'prefilters' => [
				]
			]
		];
	}

	public function getListAction()
	{
		$arParams = $this->_getRouteParams();
		
		$limit = $this->getHttpRequest()->get('limit');

		return $this->_getListAction([
			'select' => ['ID', 'NAME'],
			'filter' => ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'],
		], ['limit' => $limit]);
	}

	public function getByIdAction()
	{
		$arParams = $this->_getRouteParams();

		return $this->_getByIdAction($this->getParameterValue('id'), [
			'select' => ['ID', 'NAME'],
			'filter' => ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'],
		]);
	}

	public function getParams()
	{
		return [
			'getList' => [
				'IBLOCK_ID' => [
					'NAME' => Loc::getMessage("EWP_API_IBLOCK_CONTROLLER_PARAMS_IBLOCK_ID"),
					'TYPE' => 'select',
					'VALUES' => array_map(function($arItem){return '['.$arItem['ID'].'] '.$arItem['NAME'];}, self::getIblockList())
				],
			],
			'getById' => [
				'IBLOCK_ID' => [
					'NAME' => Loc::getMessage("EWP_API_IBLOCK_CONTROLLER_PARAMS_IBLOCK_ID"),
					'TYPE' => 'select',
					'VALUES' => array_map(function($arItem){return '['.$arItem['ID'].'] '.$arItem['NAME'];}, self::getIblockList())
				],
			],
		];
	}
}