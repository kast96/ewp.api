<?
namespace Ewp\Api\ActionFilter;

use \Bitrix\Main\Engine\ActionFilter\Base;
use \Bitrix\Main\Error;
use \Bitrix\Main\Event;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\Localization\Loc;
use \Ewp\Api\Token\JWT;

Loc::loadMessages(__DIR__);

final class AuthenticationToken extends Base
{
	public function onBeforeAction(Event $event)
	{
		if (!JWT::checkToken())
		{
			$this->addError(new Error(Loc::getMessage("ERROR_INVALID_TOKEN"), 401));
			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}