<?php

namespace Dev\Site\Agents;

class Iblock
{
    public static function clearOldLogs()
    {
        if (!\CModule::IncludeModule('iblock')) {
            return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
        }

        $getIblock = \CIBlock::GetList([], ['CODE' => 'LOG']);
        $iblock = $getIblock->Fetch();

        $loggerIblockId = $iblock['ID'];

        $elements = \CIBlockElement::GetList(
            ['ID' => 'DESC'],
            [
                'IBLOCK_ID' => $loggerIblockId,
                'CHECK_PERMISSIONS' => 'N'
            ],
            false,
            false,
            ['ID']
        );

        $counter = 0;
        $logsCount = 10;

        while ($element = $elements->Fetch()) {
            $counter++;
            if ($counter > $logsCount) {
                \CIBlockElement::Delete($element['ID']);
            }
        }

        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}