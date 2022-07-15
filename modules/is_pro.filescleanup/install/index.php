<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
Loc::loadMessages(__FILE__);

Class is_pro_filescleanup extends CModule
{
	public function __construct()
	{
		if(file_exists(__DIR__."/module.cfg.php")){
			include(__DIR__."/module.cfg.php");
		}
		if(file_exists(__DIR__."/version.php")){
			$arModuleVersion = array();
			include(__DIR__."/version.php");
			$this->MODULE_ID 		   = $arModuleCfg['MODULE_ID'];
			$this->MODULE_VERSION  	   = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
			$this->MODULE_NAME 		   = Loc::getMessage("ISPRO_FILESCLEANUP_NAME");
			$this->MODULE_DESCRIPTION  = Loc::getMessage("ISPRO_FILESCLEANUP_DESC");
			$this->PARTNER_NAME 	   = Loc::getMessage("ISPRO_FILESCLEANUP_PARTNER_NAME");
			$this->PARTNER_URI  	   = Loc::getMessage("ISPRO_FILESCLEANUP_PARTNER_URI");
		}
		return false;
	}


	public function DoInstall()
	{
		global $DB, $APPLICATION, $step;
		ModuleManager::registerModule($this->MODULE_ID);
		return true;
	}

	public function DoUninstall()
	{
		global $DB, $APPLICATION, $step;
		ModuleManager::unRegisterModule($this->MODULE_ID);
		return true;
	}
}
