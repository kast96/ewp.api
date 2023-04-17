<?
namespace Ewp\Api\Tables;

use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ApiTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'ewp_api_list';
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
		];
	}
}