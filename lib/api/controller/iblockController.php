<?
namespace Ewp\Api\Controller;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\ActionFilter\AuthenticationToken;

class IblockController extends BaseController
{
	public function configureActions(): array
	{
		return [
			'list' => [
				'prefilters' => [
					//new AuthenticationToken()
				]
			],
			'id' => [
				'prefilters' => [
					new AuthenticationToken()
				]
			]
		];
	}

	public function listAction(array $arParams = [])
	{
		$limit = $this->getHttpRequest()->get('limit');

		return $this->_listAction([
			'select' => ['ID', 'NAME'],
			'filter' => ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'],
		], ['limit' => $limit]);
	}

	public function idAction(array $arParams = [])
	{
		return $this->_idAction($this->getParameterValue('id'), [
			'select' => ['ID', 'NAME'],
			'filter' => ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'],
		]);
	}

	public function getParams()
	{
		return [
			'list' => [
				'IBLOCK_ID' => [
					'NAME' => Loc::getMessage("EWP_API_IBLOCK_CONTROLLER_PARAMS_IBLOCK_ID"),
					'TYPE' => 'select',
					'VALUES' => array_map(function($arItem){return '['.$arItem['ID'].'] '.$arItem['NAME'];}, self::getIblockList())
				],
			],
			'id' => [
				'IBLOCK_ID' => [
					'NAME' => Loc::getMessage("EWP_API_IBLOCK_CONTROLLER_PARAMS_IBLOCK_ID"),
					'TYPE' => 'select',
					'VALUES' => array_map(function($arItem){return '['.$arItem['ID'].'] '.$arItem['NAME'];}, self::getIblockList())
				],
			],
		];
	}
}