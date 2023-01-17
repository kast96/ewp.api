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
}