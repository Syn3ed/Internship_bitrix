<?php

namespace Dev\Site\Handlers;

class Iblock
{
    public static function addLog(&$arFields)
    {
        \CModule::IncludeModule('iblock');

        $loggerCode = 'LOG';
        $logIblock = \CIBlock::GetList([], ['CODE' => $loggerCode])->Fetch();

        if ($arFields['IBLOCK_ID'] == $logIblock['ID']) {
            return;
        }

        $element = \CIBlockElement::GetByID($arFields['ID'])->Fetch();
        $iblock = \CIBlock::GetByID($arFields['IBLOCK_ID'])->Fetch();

        $section = \CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $logIblock['ID'],
                'CODE' => $iblock['CODE'],
            ]
        )->Fetch();

        if (!$section) {
            $sectionObject = new \CIBlockSection();
            $sectionId = $sectionObject->Add([
                'IBLOCK_ID' => $logIblock['ID'],
                'NAME' => $iblock['NAME'],
                'CODE' => $iblock['CODE'],
            ]);
        } else {
            $sectionId = $section['ID'];
        }

        $pathParts = [$iblock['NAME']];

        $sid = $element['IBLOCK_SECTION_ID'];
        $sections = [];
        while ($sid) {
            $s = \CIBlockSection::GetByID($sid)->Fetch();
            $sections[] = $s['NAME'];
            $sid = $s['IBLOCK_SECTION_ID'];
        }

        $pathParts = array_merge($pathParts, array_reverse($sections));
        $pathParts[] = $element['NAME'];

        $fullPath = implode(' -> ', $pathParts);

        $elementObject = new \CIBlockElement();
        $elementObject->Add([
            'IBLOCK_ID' => $logIblock['ID'],
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => $arFields['ID'],
            'ACTIVE_FROM' => $element['TIMESTAMP_X'],
            'PREVIEW_TEXT' => $fullPath,
        ]);
    }
}