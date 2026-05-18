/**
 * Состояния загрузки
 */
let isLoading = false;
let isDeleting = false;
let isUploading = false;

/**
 * Универсальный AJAX запрос
 */
function apiRequest(action, data = {}, callback = null) {
    $.post(
        window.AJAX_URL,
        {
            action,
            token: window.TOKEN,
            path: window.currentPath,
            sessid: BX.bitrix_sessid(),
            ...data,
        },
        function (res) {
            if (res.success !== false) {
                callback?.(res);
            } else {
                showError(res.error || 'Ошибка');
            }
        }
    ).fail(() => {
        showError('Ошибка соединения');
    });
}

/**
 * Показ ошибки
 */
function showError(text) {
    $('#filesList').html(`<div class="error">${text}</div>`);
}

/**
 * Загрузка файлов
 */
function loadFiles(path = window.currentPath) {
    if (isLoading) {
        return;
    }

    isLoading = true;

    $('#filesList').html(
        '<div class="loading">Загрузка...</div>'
    );

    apiRequest(
        'list',
        { path },
        function (res) {
            isLoading = false;

            window.currentPath = path;

            renderFiles(
                res._embedded?.items || []
            );

            renderNavigation(path);
        }
    );
}

/**
 * Рендер списка файлов
 */
function renderFiles(items) {
    if (!items.length) {
        $('#filesList').html(
            '<div class="empty">Папка пуста</div>'
        );

        return;
    }

    let html = '<div class="files">';

    items.forEach((item) => {
        const isDir = item.type === 'dir';

        const safePath = item.path.replace(
            /'/g,
            "\\'"
        );

        const safeName = item.name.replace(
            /'/g,
            "\\'"
        );

        html += `
            <div
                class="file-item"
                data-path="${safePath}"
                data-type="${item.type}"
            >
                <div class="file-info">
                    <span class="file-icon">
                        ${isDir ? '📁' : '📄'}
                    </span>

                    <span class="file-name">
                        ${item.name}
                    </span>
                </div>

                <div class="file-actions">
                    ${!isDir
                ? `
                                <button
                                    class="btn-view"
                                    data-path="${safePath}"
                                >
                                    Просмотр
                                </button>
                            `
                : ''
            }

                    ${!isDir
                ? `
                                <button
                                    class="btn-edit"
                                    data-path="${safePath}"
                                    data-name="${safeName}"
                                >
                                    Редактирование
                                </button>
                            `
                : ''
            }

                    <button
                        class="btn-delete"
                        data-path="${safePath}"
                    >
                        Удаление
                    </button>
                </div>
            </div>
        `;
    });

    html += '</div>';

    $('#filesList').html(html);
}

/**
 * Навигация
 */
function renderNavigation(path) {
    const parts = path
        .replace('disk:', '')
        .split('/')
        .filter(Boolean);

    let currentPath = 'disk:/';

    const items = [
        `
            <span
                class="nav-item"
                data-path="disk:/"
            >
                Корень
            </span>
        `,
    ];

    parts.forEach((part) => {
        currentPath += part;

        items.push(`
            →
            <span
                class="nav-item"
                data-path="${currentPath}"
            >
                ${part}
            </span>
        `);

        currentPath += '/';
    });

    $('#navPath').html(items.join(''));
}

/**
 * Загрузка файла
 */
function uploadFile(file) {
    if (isUploading) {
        return;
    }

    isUploading = true;

    $('#uploadProgress').show();

    $('#progressPercent').text('0%');

    const formData = new FormData();

    formData.append('action', 'upload');
    formData.append('token', window.TOKEN);
    formData.append('path', window.currentPath);
    formData.append('file', file);
    formData.append(
        'sessid',
        BX.bitrix_sessid()
    );

    $.ajax({
        url: window.AJAX_URL,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,

        xhr: () => {
            const xhr = new XMLHttpRequest();

            xhr.upload.onprogress = (e) => {
                if (!e.lengthComputable) {
                    return;
                }

                const percent = Math.round(
                    (e.loaded / e.total) * 100
                );

                $('#progressPercent').text(
                    percent + '%'
                );
            };

            return xhr;
        },

        success: (res) => {
            isUploading = false;

            $('#uploadProgress').hide();

            if (res.success) {
                $('#fileInput').val('');

                loadFiles(window.currentPath);
            }
        },

        error: () => {
            isUploading = false;

            $('#uploadProgress').hide();

            showError('Ошибка загрузки');
        },
    });
}

/**
 * Просмотр файла
 */
function viewFile(path) {
    const extension = path
        .split('.')
        .pop()
        .toLowerCase();

    const imageExtensions = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg',
        'bmp',
        'ico',
    ];

    /**
     * Просмотр изображения
     */
    if (imageExtensions.includes(extension)) {
        $('#modalTitle').text(
            'Изображение: ' +
            path.split('/').pop()
        );

        $('#modalBody').html(`
        <div class="image-preview">
            <img
                src="${window.AJAX_URL}?action=preview&token=${encodeURIComponent(window.TOKEN)}&path=${encodeURIComponent(path)}&sessid=${BX.bitrix_sessid()}"
                alt=""
                class="preview-image"
            >
        </div>
    `);

        $('#saveBtn').hide();

        $('#modal').show();

        return;
    }

    /**
     * Просмотр текстового файла
     */
    apiRequest(
        'view',
        { path },
        (res) => {
            $('#modalTitle').text(
                'Просмотр: ' +
                path.split('/').pop()
            );

            $('#modalBody').html(`
                <div
                    id="file-content"
                    class="file-content"
                >
                    ${escapeHtml(res.content)}
                </div>
            `);

            $('#saveBtn').hide();

            $('#modal').show();
        }
    );
}

/**
 * Редактирование файла
 */
function editFile(path, name) {
    apiRequest(
        'view',
        { path },
        (res) => {
            window.editPath = path;

            $('#modalTitle').text(
                'Редактирование: ' + name
            );

            $('#modalBody').html(`
                <textarea
                    id="editTextarea"
                    class="edit-textarea"
                >${escapeHtml(res.content)}</textarea>
            `);

            $('#saveBtn').show();

            $('#modal').show();

            setTimeout(() => {
                $('#editTextarea').focus();
            }, 100);
        }
    );
}

/**
 * Сохранение файла
 */
function saveContent() {
    apiRequest(
        'update',
        {
            path: window.editPath,
            content: $('#editTextarea').val(),
        },
        () => {
            closeModal();

            loadFiles(window.currentPath);
        }
    );
}

/**
 * Удаление файла
 */
function deleteFile(path) {
    if (isDeleting) {
        return;
    }

    isDeleting = true;

    apiRequest(
        'delete',
        { path },
        () => {
            loadFiles(window.currentPath);
        }
    );

    setTimeout(() => {
        isDeleting = false;
    }, 500);
}

/**
 * Закрытие модалки
 */
function closeModal() {
    $('#modal').hide();

    $('#modalBody').empty();
}

/**
 * Экранирование HTML
 */
function escapeHtml(text = '') {
    return String(text).replace(
        /[&<>]/g,
        (match) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
        })[match]
    );
}

/**
 * Инициализация
 */
$(function () {
    if (!window.TOKEN) {
        showError('Токен не указан');

        return;
    }

    /**
     * Загрузка файла
     */
    $('#uploadBtn').on('click', () => {
        $('#fileInput').click();
    });

    $('#fileInput').on(
        'change',
        (e) => {
            const file = e.target.files[0];

            if (file) {
                uploadFile(file);
            }
        }
    );

    /**
     * Навигация
     */
    $(document).on(
        'click',
        '.nav-item',
        function (e) {
            e.stopPropagation();

            loadFiles(
                $(this).data('path')
            );
        }
    );

    /**
     * Просмотр файла
     */
    $(document).on(
        'click',
        '.btn-view',
        function (e) {
            e.stopPropagation();

            viewFile(
                $(this).data('path')
            );
        }
    );

    /**
     * Редактирование
     */
    $(document).on(
        'click',
        '.btn-edit',
        function (e) {
            e.stopPropagation();

            editFile(
                $(this).data('path'),
                $(this).data('name')
            );
        }
    );

    /**
     * Удаление
     */
    $(document).on(
        'click',
        '.btn-delete',
        function (e) {
            e.stopPropagation();

            deleteFile(
                $(this).data('path')
            );
        }
    );

    /**
     * Открытие папки
     */
    $(document).on(
        'click',
        '.file-item',
        function () {
            if (
                $(this).data('type') === 'dir'
            ) {
                loadFiles(
                    $(this).data('path')
                );
            }
        }
    );

    /**
     * Первая загрузка
     */
    loadFiles();
});