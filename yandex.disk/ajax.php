<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

/**
 * Проверка авторизации и sessid
 */
if (!check_bitrix_sessid() || !$USER->IsAuthorized()) {
    die(json_encode(['error' => 'Access denied']));
}

$autoloadPath = $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

$action = $_REQUEST['action'] ?? '';
$token = $_REQUEST['token'] ?? '';
$path = $_REQUEST['path'] ?? 'disk:/';

if (empty($token)) {
    die(json_encode(['error' => 'Токен не указан']));
}

$result = [];

try {
    $disk = new \Arhitector\Yandex\Disk($token);

    switch ($action) {
        /**
         * Получение списка файлов и папок
         */
        case 'list':
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                'https://cloud-api.yandex.net/v1/disk/resources?path=' .
                urlencode($path) .
                '&limit=1000'
            );

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Authorization: OAuth ' . $token,
                ]
            );

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception(
                    'API error: ' .
                    $httpCode .
                    ' - ' .
                    $response
                );
            }

            $data = json_decode($response, true);

            $items = $data['_embedded']['items'] ?? [];

            $result = [
                '_embedded' => [
                    'items' => $items,
                ],
            ];

            break;

        /**
         * Загрузка файла на диск
         */
        case 'upload':
            if (empty($_FILES['file']['tmp_name'])) {
                $result['error'] = 'Файл не передан';

                break;
            }

            $fileName = basename($_FILES['file']['name']);

            $targetPath = rtrim($path, '/') . '/' . $fileName;

            $resource = $disk->getResource($targetPath);

            $resource->upload(
                $_FILES['file']['tmp_name'],
                true
            );

            $result = [
                'success' => true,
                'message' => 'Файл успешно загружен: ' . $fileName,
            ];

            break;

        /**
         * Получение ссылки для скачивания файла
         */
        case 'download':
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                'https://cloud-api.yandex.net/v1/disk/resources/download?path=' .
                urlencode($path)
            );

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Authorization: OAuth ' . $token,
                ]
            );

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode !== 200) {
                $result['error'] = 'Не удалось получить ссылку';

                break;
            }

            $data = json_decode($response, true);

            $result = [
                'href' => $data['href'] ?? '',
            ];

            break;

        /**
         * Удаление файла или папки
         */
        case 'delete':
            $resource = $disk->getResource($path);

            $resource->delete(true);

            $result = [
                'success' => true,
            ];

            break;

        /**
         * Получение содержимого файла
         */
        case 'view':
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                'https://cloud-api.yandex.net/v1/disk/resources/download?path=' .
                urlencode($path)
            );

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Authorization: OAuth ' . $token,
                ]
            );

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            curl_close($ch);

            $data = json_decode($response, true);

            if (isset($data['href'])) {
                $content = file_get_contents($data['href']);

                $result = [
                    'success' => true,
                    'content' => $content,
                    'name' => basename($path),
                ];
            } else {
                $result['error'] = 'Не удалось получить файл';
            }

            break;

        /**
         * Обновление содержимого файла
         */
        case 'update':
            $content = $_REQUEST['content'] ?? '';

            $tempFile = tempnam(
                sys_get_temp_dir(),
                'yadisk_'
            );

            file_put_contents($tempFile, $content);

            $resource = $disk->getResource($path);

            $resource->upload($tempFile, true);

            unlink($tempFile);

            $result = [
                'success' => true,
                'message' => 'Файл успешно обновлен',
            ];

            break;

        /**
         * Превью изображения
         */
        case 'preview':
            $ch = curl_init();

            curl_setopt(
                $ch,
                CURLOPT_URL,
                'https://cloud-api.yandex.net/v1/disk/resources/download?path=' .
                urlencode($path)
            );

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                [
                    'Authorization: OAuth ' . $token,
                ]
            );

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);

            curl_close($ch);

            $data = json_decode($response, true);

            if (empty($data['href'])) {
                die('Preview error');
            }

            $imageContent = file_get_contents($data['href']);

            $mime = mime_content_type($data['href']) ?: 'image/jpeg';

            header('Content-Type: ' . $mime);

            echo $imageContent;

            exit;

        /**
         * Обработка неизвестного действия
         */
        default:
            $result['error'] = 'Неизвестное действие';
    }
} catch (Exception $e) {
    $result = [
        'error' => $e->getMessage(),
    ];
}

header('Content-Type: application/json');

echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE
);