<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<div class="news-list-grouped">
    <?php foreach ($arResult['ITEMS'] as $iblockId => $arItems): ?>
        <?php $iblock = $arResult['IBLOCKS'][$iblockId] ?? []; ?>
        
        <div class="iblock-group">
            <h2><?= htmlspecialchars($iblock['NAME'] ?? "Инфоблок №{$iblockId}") ?></h2>
            
            <div class="news-items">
                <?php foreach ($arItems as $arItem): ?>
                    <div class="news-item">
                        <?php if ($arItem['PREVIEW_PICTURE']): ?>
                            <img src="<?= $arItem['PREVIEW_PICTURE']['SRC'] ?>" alt="<?= $arItem['NAME'] ?>" style="max-width:300px;">
                        <?php endif; ?>
                        
                        <h3>
                            <a href="<?= $arItem['DETAIL_PAGE_URL'] ?>">
                                <?= $arItem['NAME'] ?>
                            </a>
                        </h3>
                        
                        <?php if ($arItem['ACTIVE_FROM']): ?>
                            <small><?= FormatDate($arParams['ACTIVE_DATE_FORMAT'], MakeTimeStamp($arItem['ACTIVE_FROM'])) ?></small>
                        <?php endif; ?>
                        
                        <?php if ($arItem['PREVIEW_TEXT']): ?>
                            <p><?= $arItem['PREVIEW_TEXT'] ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>