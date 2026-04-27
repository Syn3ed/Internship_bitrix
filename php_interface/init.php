<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (\Bitrix\Main\Loader::includeModule('dev.site')) {
    $eventManager = \Bitrix\Main\EventManager::getInstance();

    $eventManager->addEventHandler(
        'iblock',
        'OnAfterIBlockElementAdd',
        ['\Dev\Site\Handlers\Iblock', 'addLog']
    );

    $eventManager->addEventHandler(
        'iblock',
        'OnAfterIBlockElementUpdate',
        ['\Dev\Site\Handlers\Iblock', 'addLog']
    );
}