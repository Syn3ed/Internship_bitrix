<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<div class="contact-form">
    <?php if ($arResult['isFormErrors'] === 'Y'): ?>
        <div class="contact-form__errors">
            <?= $arResult['FORM_ERRORS_TEXT'] ?>
        </div>
    <?php endif; ?>

    <?php if ($arResult['isFormNote'] !== 'Y'): ?>
        <?= $arResult['FORM_HEADER'] ?>

        <div class="contact-form__head">
            <div class="contact-form__head-title">
                <?= $arResult['FORM_TITLE'] ?>
            </div>

            <?php if ($arResult['isFormDescription'] === 'Y' && !empty($arResult['FORM_DESCRIPTION'])): ?>
                <div class="contact-form__head-text">
                    <?= $arResult['FORM_DESCRIPTION'] ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="contact-form__form">
            <?= bitrix_sessid_post() ?>

            <?php foreach ($arResult['QUESTIONS'] as $FIELD_SID => $arQuestion): ?>
                <div class="input contact-form__input">
                    <label class="input__label">
                        <div class="input__label-text">
                            <?= $arQuestion['CAPTION'] ?>
                            <?php if ($arQuestion['REQUIRED'] === 'Y'): ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </div>

                        <?= $arQuestion['HTML_CODE'] ?>

                        <?php if (!empty($arQuestion['COMMENTS'])): ?>
                            <div class="input__notification">
                                <?= $arQuestion['COMMENTS'] ?>
                            </div>
                        <?php endif; ?>
                    </label>
                </div>
            <?php endforeach; ?>

            <div class="contact-form__bottom">
                <div class="contact-form__bottom-policy">
                    <?= GetMessage('AGREEMENT_TEXT') ?>
                </div>

                <input
                    <?= (intval($arResult['F_RIGHT']) < 10 ? 'disabled="disabled"' : '') ?>
                    type="submit"
                    name="web_form_submit"
                    class="form-button contact-form__bottom-button"
                    value="<?= htmlspecialcharsbx(
                        trim($arResult['arForm']['BUTTON']) === ''
                            ? GetMessage('FORM_ADD')
                            : $arResult['arForm']['BUTTON']
                    ) ?>"
                />
            </div>
        </div>

        <?= $arResult['FORM_FOOTER'] ?>
    <?php else: ?>
        <div class="contact-form__success">
            <?= $arResult['FORM_NOTE'] ?>
        </div>
    <?php endif; ?>
</div>