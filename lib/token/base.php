<?
namespace Ewp\Api\Token;

use \Bitrix\Main\Context;
use \Bitrix\Main\UserTable;

class Base
{
	public static function getTokenFromHeaders()
	{
		$server = Context::getCurrent()->getServer();
		return $server->get('HTTP_EWP_API_TOKEN');
	}

	protected static function getTokenData($userId)
	{
		$arUser = UserTable::getList([
			'select' => ['ID', 'UF_EWP_API_SECRET_KEY'],
			'filter' => ['ID' => $userId],
		])->fetch();

		if (!$arUser)
		{
			return false;
		}

		//secret key
		if (!$secretKey = $arUser['UF_EWP_API_SECRET_KEY'])
		{
			$secretKey = base64_encode(date('r'));

			//old core
			$user = new \CUser;
			$user->Update($arUser['ID'], ['UF_EWP_API_SECRET_KEY' => $secretKey]);
		}

		return [
			'payload' => [
				'userId' => $arUser['ID']
			],
			'secretKey' => $secretKey
		];
	}

	protected static function getToken($userId)
	{
	}

	protected static function checkToken()
	{
	}
}