<?
namespace Ewp\Api\V1\Controllers;

use \Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\V1\ActionFilter\AuthenticationToken;

class ProductsController extends BaseController
{
	protected $iblockCode = 'catalog';
	protected $iblockId;

	public function prolog()
	{
		if (!$this->iblockId = $this->getIblockByCode($this->iblockCode))
		{
			$this->addError(new Error(Loc::getMessage("ERROR_INCORRECT_IBLOCK_CODE"), 204));
			return false;
		}

		return true;
	}

	public function configureActions(): array
	{
		return [
			'list' => [
				'prefilters' => [
					new AuthenticationToken()
				]
			],
			'id' => [
				'prefilters' => [
					new AuthenticationToken()
				]
			]
		];
	}

	public function listAction() :? array
	{
		if(!$this->prolog()) return null;

		$limit = $this->getHttpRequest()->get('limit');

		return $this->_listAction([
			'select' => ['ID', 'NAME'],
			'filter' => ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y'],
		], ['limit' => $limit]);
	}

	public function idAction() :? array
	{
		if(!$this->prolog()) return null;

		return $this->_idAction($this->getParameterValue('id'), [
			'select' => ['ID', 'NAME'],
			'filter' => ['IBLOCK_ID' => $this->iblockId, 'ACTIVE' => 'Y'],
		]);
	}
}