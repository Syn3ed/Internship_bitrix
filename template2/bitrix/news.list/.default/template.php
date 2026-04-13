<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

?>
<div id="barba-wrapper">
    <div class="article-list">
        <?php foreach ($arResult['ITEMS'] as $arItem): ?>
            <?php
            $url = !empty($arItem['PROPERTIES']['URL_BUS']['VALUE'])
                ? $arItem['PROPERTIES']['URL_BUS']['VALUE']
                : $arItem['DETAIL_PAGE_URL'];
            ?>

            <a class="article-item article-list__item"
               href="<?= $url ?>"
               data-anim="anim-3">
                <div class="article-item__background">
                    <img src="<?= $arItem['PREVIEW_PICTURE']['SRC'] ?>">
                </div>
                <div class="article-item__wrapper">
                    <div class="article-item__title">
                        <?= $arItem['NAME'] ?>
                    </div>
                    <div class="article-item__content">
                        <?= $arItem['PREVIEW_TEXT'] ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>