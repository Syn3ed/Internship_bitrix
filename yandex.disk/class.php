<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Arhitector\Yandex\Disk;

/**
 * Компонент для работы с Yandex Disk
 */
class CMyYandexDiskComponent extends CBitrixComponent
{
    /**
     * Подготовка параметров компонента
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['TOKEN'] = trim($arParams['TOKEN'] ?? '');

        $arParams['ROOT_PATH'] = trim(
            $arParams['ROOT_PATH'] ?? 'disk:/'
        );

        if (empty($arParams['ROOT_PATH'])) {
            $arParams['ROOT_PATH'] = 'disk:/';
        }

        return $arParams;
    }

    /**
     * Выполнение компонента
     */
    public function executeComponent()
    {
        if (empty($this->arParams['TOKEN'])) {
            $this->arResult['ERROR'] = 'Не указан OAuth-токен';
        }

        $this->includeComponentTemplate();
    }
}