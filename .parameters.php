<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'PARAMETERS' => [
        'IBLOCK_CODE' => [
            'NAME'     => 'Символьный код инфоблока ',
            'TYPE'     => 'STRING',
            'DEFAULT'  => '',
        ],
         'IBLOCK_TYPE' => [
            'NAME'     => 'Тип инфоблока',
            'TYPE'     => 'STRING',
            'DEFAULT'  => '',
        ],
        'NEWS_COUNT' => [
            'NAME'     => 'Количество новостей',
            'TYPE'     => 'STRING',
            'DEFAULT'  => '20',
        ],
        'SORT_BY1' => [
            'NAME'     => 'Поле сортировки',
            'TYPE'     => 'STRING',
            'DEFAULT'  => 'ACTIVE_FROM',
        ],
        'SORT_ORDER1' => [
            'NAME'     => 'Направление сортировки',
            'TYPE'     => 'LIST',
            'VALUES'   => ['ASC' => 'По возрастанию', 'DESC' => 'По убыванию'],
            'DEFAULT'  => 'DESC',
        ],
        'CACHE_TIME' => ['DEFAULT' => 3600],
    ],
];