<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$componentPath = $this->getComponent()->getPath();

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">


    <link
        rel="stylesheet"
        href="<?= $templateFolder ?>/style.css?v=<?= time() ?>"
    >
</head>

<body>
    <div class="disk-container">
        <div class="nav">
            <div id="navPath"></div>
        </div>

        <div class="upload-area">
            <input
                type="file"
                id="fileInput"
                style="display: none"
            >

            <button
                id="uploadBtn"
                class="upload-btn"
            >
                Выбрать файл
            </button>

            <div
                id="uploadProgress"
                class="progress"
                style="display: none"
            >
                Загрузка...
                <span id="progressPercent">0%</span>
            </div>
        </div>

        <div id="filesList"></div>
    </div>

    <div
        id="modal"
        class="modal"
        style="display: none"
    >
        <div class="modal-content">
            <div class="modal-header">
                <span id="modalTitle"></span>

                <span
                    class="close"
                    onclick="closeModal()"
                >
                    ✕
                </span>
            </div>

            <div id="modalBody"></div>

            <div class="modal-footer">
                <button
                    id="saveBtn"
                    onclick="saveContent()"
                >
                    Сохранить
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-4.0.0.js"></script>

    <script>
        window.TOKEN = '<?= addslashes($arParams['TOKEN']) ?>';
        window.AJAX_URL = '<?= $componentPath ?>/ajax.php';
        window.currentPath = 'disk:/';
    </script>

    <script src="<?= $templateFolder ?>/script.js?v=<?= time() ?>"></script>
</body>
</html>