<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use \Bitrix\Main\Application;
use \Bitrix\Main\Loader;
use \Bitrix\Main\DB\Connection;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

Class ewp_api extends CModule
{
	var $MODULE_ID = "ewp.api";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";
	var $MODULE_LOCAL = false;

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("EWP_API_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("EWP_API_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("EWP_API_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("EWP_API_PARTNER_URI");
	}

	public function DoInstall()
	{
		include(__DIR__."/functions.php");

		global $APPLICATION;
		if (CheckVersion(ModuleManager::getVersion("main"), "21.400.00"))
		{
			$this->InstallFiles();
			ModuleManager::registerModule($this->MODULE_ID);
			$this->InstallDB();
			$this->InstallEvents();
		}
		else
		{
			$APPLICATION->ThrowException(Loc::getMessage("EWP_API_INSTALL_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("EWP_API_INSTALL_TITLE")." \"".Loc::getMessage("EWP_API_MODULE_NAME")."\"", __DIR__."/step.php");
	}

	public function DoUninstall()
	{
		include(__DIR__."/functions.php");

		global $APPLICATION;
		$this->UnInstallFiles();

		CEwpInstall::SetRouteConfiguration();
		CEwpInstall::AddUfUserToken();

		$this->UnInstallDB();
		$this->UnInstallEvents();
		ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(Loc::getMessage("EWP_API_UNINSTALL_TITLE")." \"".Loc::getMessage("EWP_API_MODULE_NAME")."\"", __DIR__."/unstep.php");
	}
	
	public function InstallFiles()
	{
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/routes/')) mkdir($_SERVER['DOCUMENT_ROOT'].'/bitrix/routes/');

		CopyDirFiles(__DIR__.'/routes', $_SERVER['DOCUMENT_ROOT'].'/bitrix/routes', true, true);
		CopyDirFiles(__DIR__.'/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin', true, true);
		CopyDirFiles(__DIR__.'/components', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components', true, true);

		return true;
	}
	
	public function UnInstallFiles()
	{
		DeleteDirFiles(__DIR__.'/routes', $_SERVER['DOCUMENT_ROOT'].'/bitrix/routes');
		DeleteDirFiles(__DIR__.'/admin', $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin');

		$this->deleteDirDirs(__DIR__.'/components/ewp', $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/ewp', array(), true);
		
		return true;
	}

	public function deleteDirDirs($frDir, $toDir, $arExept = array(), $rmToDir = false)
	{
		if(is_dir($frDir))
		{
			$d = dir($frDir);
			while ($entry = $d->read())
			{
				if (!is_dir($toDir."/".$entry)) continue;
				if ($entry=="." || $entry=="..") continue;
				if (in_array($entry, $arExept)) continue;

				Directory::deleteDirectory($toDir."/".$entry);
			}
			$d->close();

			if ($rmToDir && count(scandir($toDir)) == 2) {
				Directory::deleteDirectory($toDir);
			}
		}
	}

	public function InstallDB()
	{
		$connection = Application::getConnection();
		try
		{
			$connection->startTransaction();
			Loader::includeModule($this->MODULE_ID);     
			$this->createProcessTable($connection);        
			$this->insertProcessTable($connection);        
			$connection->commitTransaction();
		}
		catch (\Exception $e)
		{
			$connection->rollbackTransaction();
			global $APPLICATION;
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($e->getMessage());
			return false;
		}
		
		return true;
	}

	protected function createProcessTable(Connection $connection)
	{
		$tableName = \Ewp\Api\Tables\ApiTable::getTableName();
		if (!$connection->isTableExists($tableName))
		{
			$connection->createTable($tableName, \Ewp\Api\Tables\ApiTable::getMap(), ['ID'], ['ID']);
		}

		$tableName = \Ewp\Api\Tables\RouteTable::getTableName();
		if (!$connection->isTableExists($tableName))
		{
			$connection->createTable($tableName, \Ewp\Api\Tables\RouteTable::getMap(), ['ID'], ['ID']);
		}
	}

	public function insertProcessTable(Connection $connection){
	}
	
	public function UnInstallDB()
	{
		Option::delete($this->MODULE_ID);
		
		$connection = Application::getConnection();
 
		try
		{
			Loader::includeModule($this->MODULE_ID);
			$connection->startTransaction();
			$this->dropProcessTable($connection);
			$connection->commitTransaction();
		}
		catch (\Exception $e)
		{
			$connection->rollbackTransaction();
			global $APPLICATION;
			$APPLICATION->ResetException();
			$APPLICATION->ThrowException($e->getMessage());
			return false;
		}
				
		return true;
	}

	protected function dropProcessTable(\Bitrix\Main\DB\Connection $connection)
	{
		//if ($_REQUEST["DELETE_TABLE"] === 'Y') {
			$tableName = \Ewp\Api\Tables\ApiTable::getTableName();
			if ($connection->isTableExists($tableName)) {
				$connection->dropTable($tableName);
			}

			$tableName = \Ewp\Api\Tables\RouteTable::getTableName();
			if ($connection->isTableExists($tableName)) {
				$connection->dropTable($tableName);
			}
		//}
	}

	public function InstallEvents()
	{
		//$eventManager = \Bitrix\Main\EventManager::getInstance(); 
		//$eventManager->registerEventHandler("main", "OnPageStart", $this->MODULE_ID, "Ewp\\Api\\Events\\EventHandlers", "OnPageStart");

		return true;
	}
		
	public function UnInstallEvents()
	{
		//$eventManager = \Bitrix\Main\EventManager::getInstance(); 
		//$eventManager->unRegisterEventHandler("main", "OnPageStart", $this->MODULE_ID, "Ewp\\Api\\Events\\EventHandlers", "OnPageStart");
		
		return true;
	}
}
?>