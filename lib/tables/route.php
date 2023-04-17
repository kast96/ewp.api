<?
namespace Ewp\Api\Tables;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class RouteTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'ewp_api_route';
	}
	
	public static function getMap()
	{
		return [
			'ID' => new Entity\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('TABLE_ID'),
			]),
			'NAME' => new Entity\StringField('NAME', [
				'title' => Loc::getMessage('TABLE_NAME'),
			]),
			'PATH' => new Entity\StringField('PATH', [
				'title' => Loc::getMessage('TABLE_PATH'),
			]),
			'ACTIVE' => new Entity\BooleanField('ACTIVE', [
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('TABLE_ACTIVE'),
			]),
			'API_ID' => new Entity\IntegerField('API_ID', [
				'title' => Loc::getMessage('TABLE_API_ID'),
			]),
			'METHOD' => new Entity\TextField('METHOD', [
				'title' => Loc::getMessage('TABLE_METHOD'),
			]),
			'CONTROLLER' => new Entity\TextField('CONTROLLER', [
				'title' => Loc::getMessage('TABLE_CONTROLLER'),
			]),
			'CONTROLLER_METHOD' => new Entity\TextField('CONTROLLER_METHOD', [
				'title' => Loc::getMessage('TABLE_CONTROLLER_METHOD'),
			]),
			'PARAMS' => new Entity\TextField('PARAMS', [
				'title' => Loc::getMessage('TABLE_PARAMS'),
			]),
		];
	}
}