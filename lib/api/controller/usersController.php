<?
namespace Ewp\Api\Controller;

use \Bitrix\Main\Error;
use \Bitrix\Main\Context;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\ActionFilter\AuthenticationToken;
use \Ewp\Api\Token\JWT;

Loc::loadMessages(__DIR__);

class UsersController extends BaseController
{
	public function configureActions(): array
	{
		return [
			'auth' => [
				'prefilters' => []
			],
			'profile' => [
				'prefilters' => [
					new AuthenticationToken()
				]
			],
		];
	}

	public function authAction()
	{
		$arParams = $this->_getRouteParams();

		$request = Context::getCurrent()->getRequest();
		$login = $request->get('login');
		$password = $request->get('password');

		//old core
		$user = new \CUser;
		$arResult = $user->login($login, $password);
		
		if($arResult['TYPE'] == 'ERROR')
		{
			$this->addError(new Error(strip_tags($arResult['MESSAGE'])));
			return null;
		}
		else
		{
			$token = JWT::getToken($user->GetID());

			return [
				'accessToken' => $token
			];
		}
	}

	public function profileAction()
	{
		$arParams = $this->_getRouteParams();
		
		$payload = JWT::getPayload();
		
		$arUser = UserTable::getList([
			'select' => ['ID', 'LOGIN'],
			'filter' => ['ID' => $payload['userId']]
		])->fetch();

		if (!$arUser)
		{
			$this->addError(new Error(Loc::getMessage("EWP_API_USER_CONTROLLER_ERROR_USER_NOT_FOUND")));
			return null;
		}

		return [
			'id' => $arUser['ID'],
			'login' => $arUser['LOGIN'],
		];
	}
}