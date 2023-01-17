<?
namespace Ewp\Api\V1\Controllers;

use \Ewp\Api\V1\Pagination;
use \Bitrix\Iblock\ElementTable;
use \Bitrix\Main\Engine\Controller;
use \Bitrix\Main\Application;
use \Bitrix\Iblock\IblockTable;
use \Bitrix\Main\Error;
use \Bitrix\Main\Context;
use \Bitrix\Main\Localization\Loc;

class BaseController extends Controller
{
  /**
   * Перед выполнением экшена, если запрос был с параметром OPTIONS, возвращаем 204.
   * Некоторые запросы могут отправляться с дополнительным подзапросом перед остновным preflight (OPTIONS). Сервер должен отвечать на него 204 иначе основной запрос не отправится.
   */
  protected function processBeforeAction(\Bitrix\Main\Engine\Action $action)
  {
    $server = Context::getCurrent()->getServer();
    if($server->getRequestMethod() == 'OPTIONS') {
      Context::getCurrent()->getResponse()->setStatus(204);
      return false;
    }
    
    return true;
  }

  /**
   * Возвращает id инфоблока по его коду
   * @param string $code - код ифоблока
   * @return int|false
  */
	protected function getIblockByCode($code)
	{
		return IblockTable::getList([
      'select' => ['ID'],
      'filter' => ['CODE' => $code]
    ])->fetch()['ID'];
	}

  /**
   * Возвращает запрошенный объект контекста
   * @return \Bitrix\Main\HttpRequest
  */
	protected function getHttpRequest()
	{
		return Context::getCurrent()->getRequest();
	}

  /**
   * Возвращает значение параметра из url при роутинге
   * Пример, /example/{param}/
   * @param string $param название параметра
   * @return string значение параметра
  */
	protected function getParameterValue($param)
	{
		return Application::getInstance()->getCurrentRoute()->getParameterValue($param);
	}

  /**
   * Общее событие для getList. Собирает ответ в виде списка элементов инфоблока
   * @param array $params параметры метода getList
   * @param array $pagination параметры пагинации
   * @param string $pagination['pageParameterName'] название url параметра текщей страницы пагинации
   * @param int $pagination['limit'] количество элементов на странице
   * @return array сформированная структура ответ клиенту
  */
  protected function _listAction($params = array(), $pagination = array())
  {
    $nav = new Pagination($pagination['pageParameterName'], $pagination['limit']);

    if($arErrors = $nav->getErrors()) return $this->addErrors($arErrors);

		$rsItems = ElementTable::getList(array_merge(
      $params,
      [
        "count_total" => true,
        "offset" => $nav->getOffset(),
        "limit" => $nav->getLimit(),
      ]
    ));

		$nav->setCount($rsItems->getCount());

		$arItems = [];
		while ($arItem = $rsItems->fetch())
		{
			$arItems[] = $arItem;
		}

    if($arErrors = $nav->getErrors()) return $this->addErrors($arErrors);

		return array_merge(
			[
				'result' => [
					'items' => $arItems
				],
			],
			$nav->getPaginationResponse()
		);
  }

  /**
   * Общее событие для getById. Собирает ответ в виде элемента инфоблока
   * @param array $id идентификатор элемента
   * @param array $params опциональные параметры
   * @return array сформированная структура ответ клиенту
  */
  protected function _idAction($id, $params = array())
  {
    if(!$arElement = ElementTable::getByPrimary($id, $params)->fetch()) {
      Context::getCurrent()->getResponse()->setStatus(400);
      $this->addError(new Error(Loc::getMessage("ERROR_ITEM_NOT_FOUND")));
      return null;
    }

    return [
      'result' => $arElement
    ];
  }
}