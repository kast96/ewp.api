<?
namespace Ewp\Api;

use \Bitrix\Main\Application;
use \Bitrix\Main\Error;
use \Bitrix\Main\Context;
use \Bitrix\Main\Localization\Loc;

class Pagination
{
	protected $page = 1;
	protected $limit = 20;
	protected $count = 0;
	protected $arErrors = [];
	
	/**
	 * @param string $pageParameterName название get параметра текущей страницы
	 * @param int $limit количество элементов на странице
	 */
	public function __construct($pageParameterName = 'page', $limit = 20)
	{
		$page = Application::getInstance()->getContext()->getRequest()->get($pageParameterName ?: 'page');
		if (isset($page)) $this->setPage(intval($page));
		$this->setLimit(intval($limit));
	}

	/**
	 * Устанавливает текущую страницу
	 * @param int $page текущая страница
	 */
	public function setPage($page)
	{
		if ($page <= 0)
		{
			Context::getCurrent()->getResponse()->setStatus(400);
			$this->arErrors[] = new Error(Loc::getMessage("ERROR_BAD_PAGE_VALUE"));
			return false;
		}

		$this->page = $page;
	}

	/**
	 * Возвращает текущую страницу
	 * @return int текущая страница
	 */
	public function getPage()
	{
		return $this->page;
	}

	/**
	 * Возвращает текущий сдвиг для выборки
	 * @return int текущий сдвиг
	 */
	public function getOffset()
	{
		return ($this->page - 1) * $this->limit;
	}
	
	/**
	 * Устанавливает количество элементов на странице
	 * @param int $limit количество элементов на странице
	 */
	public function setLimit($limit)
	{
		if ($limit < 1) $limit = 20;
		$this->limit = $limit;
	}

	/**
	 * Возвращает количество элементов на странице
	 * @return int количество элементов на странице
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Устанавливает общее количество элементов
	 * @param int $count общее количество элементов
	 */
	public function setCount($count)
	{
		if ($this->getOffset() >= $count)
		{
			Context::getCurrent()->getResponse()->setStatus(204);
			$this->arErrors[] = new Error(Loc::getMessage("ERROR_ITEMS_NOT_FOUND"));
		}

		if ($count < 0) $count = 0;
		$this->count = $count;
	}

	/**
	 * Возвращает общее количество элементов
	 * @return int общее количество элементов
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * Возвращает массив ошибок пагинации
	 * @return array массив ошибок
	 */
	public function getErrors()
	{
		return $this->arErrors;
	}

	/**
	 * Возвращает массив пагинации для ответа клиенту
	 * @return array массив пагинации
	 */
	public function getPaginationResponse()
	{
		return [
      'pagination' => [
        'page' => $this->page,
				'count' => $this->count,
				'limit' => $this->limit,
        'pageCount' => ceil($this->count / $this->limit),
      ]
    ];
	}
}