<?php

use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

class phpdevorg_cprop extends CModule
{
    public $MODULE_ID = 'phpdevorg.cprop';

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallEvents();

        return true;
    }

    public function DoUninstall()
    {
        $this->UnInstallEvents();
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            'CIBlockPropertyCProp',
            'GetUserTypeDescription'
        );

        $eventManager->registerEventHandler(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            'CIBlockPropertyCProp'
        );
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'iblock',
            'OnIBlockPropertyBuildList',
            $this->MODULE_ID,
            'CIBlockPropertyCProp',
            'GetUserTypeDescription'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnUserTypeBuildList',
            $this->MODULE_ID,
            'CIBlockPropertyCProp'
        );
    }
}