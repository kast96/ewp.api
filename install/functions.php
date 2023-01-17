<?
use \Bitrix\Main\Config\Configuration;
use Bitrix\Main\UserFieldTable;

class CEwpInstall
{
	public static function SetRouteConfiguration()
	{
		$config = Configuration::getInstance();
		$routing = $config->get('routing');
		if(!isset($routing) || !isset($routing['config']) || !is_array($routing['config']))
		{
			$arRoutes = ['ewp_api.php'];
		}
		else if(!in_array('ewp_api.php', $routing['config']))
		{
			$arRoutes = $routing['config'];
			$arRoutes[] = ['ewp_api.php'];
		}

		if ($arRoutes)
		{
			$config->add('routing', ['config' => $arRoutes]);
			$config->saveConfiguration();
		}

		return true;
	}

	public static function AddUfUserToken()
	{
		if (UserFieldTable::getList(['filter' => ['ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_EWP_API_SECRET_KEY']])->fetch())
			return true;
		
		/*
		temp old core, wait d7 adding method

		UserFieldTable::add([
			'ENTITY_ID' => 'USER',
			'FIELD_NAME' => 'UF_EWP_API_SECRET_KEY',
			'USER_TYPE_ID' => 'string',
		]);
		*/

		$ob = new CUserTypeEntity();
		$ob->Add([
			'ENTITY_ID' => 'USER',
			'FIELD_NAME' => 'UF_EWP_API_SECRET_KEY',
			'USER_TYPE_ID' => 'string',
			'EDIT_FORM_LABEL' => [
				'ru' => 'EWP API secret key',
				'en' => 'EWP API secret key',
			]
		]);

		return true;
	}
}