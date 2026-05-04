<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;

class MyNewsList extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE'] ?? '');
        $arParams['IBLOCK_CODE'] = trim($arParams['IBLOCK_CODE'] ?? '');
        $arParams['IBLOCK_ID'] = trim($arParams['IBLOCK_ID'] ?? '');

        $arParams['NEWS_COUNT'] = (int) ($arParams['NEWS_COUNT'] ?? 20);
        $arParams['SORT_BY1'] = $arParams['SORT_BY1'] ?? 'ACTIVE_FROM';
        $arParams['SORT_ORDER1'] = $arParams['SORT_ORDER1'] ?? 'DESC';
        $arParams['SORT_BY2'] = $arParams['SORT_BY2'] ?? 'SORT';
        $arParams['SORT_ORDER2'] = $arParams['SORT_ORDER2'] ?? 'ASC';
        $arParams['CACHE_TIME'] = (int) ($arParams['CACHE_TIME'] ?? 3600);

        if (!is_array($arParams['FILTER'] ?? null)) {
            $arParams['FILTER'] = [];
        }

        return $arParams;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Модуль iblock не установлен');
            return;
        }

        if (empty($this->arParams['IBLOCK_TYPE']) && empty($this->arParams['IBLOCK_CODE'])) {
            ShowError('Не указан IBLOCK_TYPE или IBLOCK_CODE');
            return;
        }

        if ($this->startResultCache($this->arParams['CACHE_TIME'])) {

            $this->arResult = ['ITEMS' => [], 'IBLOCKS' => []];

            $arFilter = $this->arParams['FILTER'];
            $arFilter['ACTIVE'] = 'Y';

            if (!empty($this->arParams['IBLOCK_ID'])) {
                $arFilter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
            } elseif (!empty($this->arParams['IBLOCK_CODE'])) {
                $arFilter['IBLOCK_CODE'] = $this->arParams['IBLOCK_CODE'];
            } elseif (!empty($this->arParams['IBLOCK_TYPE'])) {
                $iblockIds = [];
                $rsIb = CIBlock::GetList([], ['TYPE' => $this->arParams['IBLOCK_TYPE'], 'ACTIVE' => 'Y']);
                while ($arIb = $rsIb->Fetch()) {
                    $iblockIds[] = $arIb['ID'];
                }
                if (!empty($iblockIds)) {
                    $arFilter['IBLOCK_ID'] = $iblockIds;
                }
            }

            $arSort = [
                $this->arParams['SORT_BY1'] => $this->arParams['SORT_ORDER1'],
                $this->arParams['SORT_BY2'] => $this->arParams['SORT_ORDER2'],
            ];

            $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'ACTIVE_FROM', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'PREVIEW_TEXT'];

            $rsElement = CIBlockElement::GetList(
                $arSort,
                $arFilter,
                false,
                ['nTopCount' => $this->arParams['NEWS_COUNT']],
                $arSelect
            );

            while ($arItem = $rsElement->GetNext(true, false)) {
                $iblockId = (int)$arItem['IBLOCK_ID'];
                if ($iblockId <= 0)
                    continue;

                if (!isset($this->arResult['ITEMS'][$iblockId])) {
                    $this->arResult['ITEMS'][$iblockId] = [];
                }

                if (!empty($arItem['PREVIEW_PICTURE'])) {
                    $arItem['PREVIEW_PICTURE'] = CFile::GetFileArray($arItem['PREVIEW_PICTURE']);
                }

                $this->arResult['ITEMS'][$iblockId][] = $arItem;
            }

            if (!empty($this->arResult['ITEMS'])) {
                $rsIblock = CIBlock::GetList([], ['ID' => array_keys($this->arResult['ITEMS'])]);
                while ($arIblock = $rsIblock->Fetch()) {
                    $this->arResult['IBLOCKS'][$arIblock['ID']] = $arIblock;
                }
            }

            $this->endResultCache();
        }

        $this->includeComponentTemplate();
    }
}