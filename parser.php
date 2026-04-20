<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    die;
}

$IBLOCK_ID = 18;
$CSV_FILE  = $_SERVER['DOCUMENT_ROOT'] . '/local/parser/vacancy.csv';

$handle = fopen($CSV_FILE, 'r');

$propertyCodes = null;
$data          = [];

while (($row = fgetcsv($handle, 0, ',', '"')) !== false) {
    if (!$propertyCodes && in_array('ID', $row, true)) {
        $propertyCodes = $row;
        continue;
    }

    $item = [];
    foreach ($propertyCodes as $i => $code) {
        if (isset($row[$i])) {
            $item[$code] = trim($row[$i]);
        }
    }
    $data[] = $item;
}

$el = new CIBlockElement;

foreach ($data as $row) {
    $arFields = [
        'IBLOCK_ID'       => $IBLOCK_ID,
        'ACTIVE'          => 'Y',
        'NAME'            => $row['NAME'] ?? 'Без названия',
        'XML_ID'          => $row['ID'] ?? '',
        'SORT'            => 500,
        'PROPERTY_VALUES' => [],
    ];

    foreach ($row as $code => $value) {
        if (empty($value) || $code === 'ID' || $code === 'NAME') {
            continue;
        }

        $prop = CIBlockProperty::GetList([], [
            'IBLOCK_ID' => $IBLOCK_ID,
            'CODE'      => $code,
        ])->Fetch();

        if ($prop['PROPERTY_TYPE'] === 'L') {
            $enum = CIBlockPropertyEnum::GetList([], [
                'PROPERTY_ID' => $prop['ID'],
                'VALUE'       => $value,
            ])->Fetch();

            if ($enum) {
                $arFields['PROPERTY_VALUES'][$code] = $enum['ID'];
            } else {
                $enumId = CIBlockPropertyEnum::Add([
                    'PROPERTY_ID' => $prop['ID'],
                    'VALUE'       => $value,
                    'SORT'        => 500,
                    'DEF'         => 'N',
                ]);
                $arFields['PROPERTY_VALUES'][$code] = $enumId;
            }
        } else {
            $arFields['PROPERTY_VALUES'][$code] = $value;
        }
    }

    $elementId = false;
    if (!empty($row['ID'])) {
        $exist = CIBlockElement::GetList([], [
            'IBLOCK_ID' => $IBLOCK_ID,
            'XML_ID'    => $row['ID'],
        ], false, false, ['ID'])->Fetch();

        if ($exist) {
            $elementId = $exist['ID'];
        }
    }

    if (!$elementId) {
        $el->Add($arFields);
    }
}

echo 'Готово';