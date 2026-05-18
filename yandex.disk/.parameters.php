<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    "PARAMETERS" => [
        "TOKEN" => [
            "NAME" => "OAuth токен Яндекс.Диска",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ],
        "ROOT_PATH" => [
            "NAME" => "Начальная папка",
            "TYPE" => "STRING",
            "DEFAULT" => "disk:/",
        ],
    ],
];