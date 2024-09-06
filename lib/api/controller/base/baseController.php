<?
namespace Ewp\Api\Controller;

use \Bitrix\Iblock\ElementTable;
use \Bitrix\Iblock\IblockTable;
use \Bitrix\Main\Engine\Controller;
use \Bitrix\Main\Application;
use \Bitrix\Main\Error;
use \Bitrix\Main\Context;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Engine\Action;
use \Ewp\Api\Pagination;

Loc::loadMessages(__DIR__);

class BaseController extends Controller
{
  /*
  protected function processBeforeAction(Action $action)
  {
    $server = Context::getCurrent()->getServer();
    if($server->getRequestMethod() == 'OPTIONS') {
      Context::getCurrent()->getResponse()->setStatus(204);
      return false;
    }
    
    return true;
  }
  */

	protected function getIblockList($arParams = ['select' => ['ID', 'NAME']])
	{
    Loader::includeModule('iblock');

    $arIblocks = [];
		$rsIblocks = IblockTable::getList($arParams);
    while ($arIblock = $rsIblocks->fetch())
    {
      $arIblocks[$arIblock['ID']] = $arIblock;
    }
    return $arIblocks;
	}

	protected function getIblockByCode($code)
	{
    Loader::includeModule('iblock');

		return IblockTable::getList([
      'select' => ['ID'],
      'filter' => ['CODE' => $code]
    ])->fetch()['ID'];
	}

	protected function getHttpRequest()
	{
		return Context::getCurrent()->getRequest();
	}

	protected function getParameterValue($param)
	{
		return Application::getInstance()->getCurrentRoute()->getParameterValue($param);
	}

  protected function _getListAction($params = array(), $pagination = array())
  {
    Loader::includeModule('iblock');

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

  protected function _getByIdAction($id, $params = array())
  {
    Loader::includeModule('iblock');

    if(!$arElement = ElementTable::getByPrimary($id, $params)->fetch())
    {
      Context::getCurrent()->getResponse()->setStatus(400);
      $this->addError(new Error(Loc::getMessage("EWP_API_BASE_CONTROLLER_ERROR_ITEM_NOT_FOUND")));
      return null;
    }

    return [
      'result' => $arElement
    ];
  }

  public function getActions()
  {
    $reflectionClass = new \ReflectionClass($this::class);
    $arMethods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

    $arActionMethods = array_filter($arMethods, function($method) {
      return preg_match('/Action$/', $method->getName());
    });

    $arActionMethods = array_map(function($method){
      return preg_replace('/Action$/', '', $method->getName());
    },  $arActionMethods);
  
    return $arActionMethods;
  }

  public function getParams()
  {
    return [];
  }

  protected function _getRouteParams()
  {
    $arResult = [];

    $arParams = $this->getParams();
    if (!is_array($arParams))
    {
      $arParams = [];
    }

    $backtrace = debug_backtrace();
    $method = preg_replace('/Action$/', '', $backtrace[1]['function']);

    $arNames = is_array($arParams[$method]) ? array_keys($arParams[$method]) : [];
    
    $route = Application::getInstance()->getCurrentRoute();
    foreach ($arNames as $name)
    {
      $value = $route->getParameterValue($name);
      $arResult[$name] = $value;
    }

    return $arResult;
  }
}