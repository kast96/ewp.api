<?
namespace Ewp\Api;

class Main
{
	public const partnerName = "ewp";
	public const solutionName = "api";

	public static function getModuleId()
	{
		return self::partnerName.'.'.self::solutionName;
	}

	public static function getControllers()
	{
		$arFiles = scandir(__DIR__.'/api/controller/');
		$arFiles = array_filter($arFiles, function($file)
		{
			return pathinfo($file, PATHINFO_EXTENSION) === 'php';
		});

		foreach ($arFiles as $file)
		{
			include __DIR__.'/api/controller/'.$file;
		}

		
		$arClasses = get_declared_classes();
		$arControllers = [];

		foreach ($arClasses as $class) {
			if (is_subclass_of($class, '\Ewp\Api\Controller\BaseController')) {
				$arControllers[] = [
					'CLASS' => $class,
					'METHODS' => (new $class)->getActions(),
				];
			}
		}

		return $arControllers;
	}
}