<?
namespace Ewp\Api\V1\Controllers;

use \Bitrix\Main\Error;
use \Bitrix\Main\Context;
use \Bitrix\Main\Engine\CurrentUser;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\V1\ActionFilter\AuthenticationToken;
use \Ewp\Api\Token\JWT;

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

	public function authAction() :? array
	{
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

	public function profileAction() :? array
	{
		$payload = JWT::getPayload();
		
		$arUser = UserTable::getList([
			'select' => ['ID', 'LOGIN'],
			'filter' => ['ID' => $payload['userId']]
		])->fetch();

		if (!$arUser)
		{
			$this->addError(new Error(Loc::getMessage("ERROR_USER_NOT_FOUND")));
			return null;
		}

		return [
			'id' => $arUser['ID'],
			'login' => $arUser['LOGIN'],
		];
	}
}