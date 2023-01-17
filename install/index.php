<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

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

	function ewp_api()
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
			$this->InstallDB();
			$this->InstallEvents();
			ModuleManager::registerModule($this->MODULE_ID);
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
		$this->UnInstallDB();
		$this->UnInstallEvents();
		ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(Loc::getMessage("EWP_API_UNINSTALL_TITLE")." \"".Loc::getMessage("EWP_API_MODULE_NAME")."\"", __DIR__."/unstep.php");
	}
	
	public function InstallFiles()
	{
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/routes/')) mkdir($_SERVER['DOCUMENT_ROOT'].'/bitrix/routes/');

		foreach (['local', 'bitrix'] as $vendor)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$vendor.'/modules/'.$this->MODULE_ID.'/install/routes/'))
			{
				CopyDirFiles($_SERVER['DOCUMENT_ROOT'].'/'.$vendor.'/modules/'.$this->MODULE_ID.'/install/routes/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/routes/', true, true);
			}
		}

		return true;
	}
	
	public function UnInstallFiles(){
		foreach (['local', 'bitrix'] as $vendor)
		{
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$vendor.'/modules/'.$this->MODULE_ID.'/install/routes/'))
			{
				DeleteDirFiles($_SERVER['DOCUMENT_ROOT'].'/'.$vendor.'/modules/'.$this->MODULE_ID.'/install/routes/', $_SERVER['DOCUMENT_ROOT'].'/bitrix/routes/', true, true);
			}
		}
		
		return true;
	}

	public function InstallDB()
	{
		CEwpInstall::SetRouteConfiguration();
		CEwpInstall::AddUfUserToken();
		
		return true;
	}
	
	public function UnInstallDB()
	{
		return true;
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