<?php

use Bitrix\Main\Localization\Loc;


class CIBlockPropertyCProp 
{
    private static $cssShown = false;
    private static $showedJs = false;
    public static function GetUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'COMPLEX_PROP',
            'DESCRIPTION' => Loc::getMessage('COMPLEX_PROP_DESC'),
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'PrepareSettings' => [__CLASS__, 'PrepareSettings'],
            'GetSettingsHTML' => [__CLASS__, 'GetSettingsHTML'],
            'GetPublicViewHTML' => [__CLASS__, 'GetPublicViewHTML'],
        ];
    }


    public static function PrepareSettings(array $arProperty): array
    {
        return $arProperty['USER_TYPE_SETTINGS'] ?? [];
    }

    public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
    {
        $arPropertyFields = [
            'USER_TYPE_SETTINGS_TITLE' => Loc::getMessage('COMPLEX_CPROP_SETTINGS_TITLE'),
            'HIDE' => [
                'ROW_COUNT', 'COL_COUNT', 'DEFAULT_VALUE', 'SEARCHABLE',
                'SMART_FILTER', 'WITH_DESCRIPTION', 'FILTRABLE', 'MULTIPLE_CNT', 'IS_REQUIRED'
            ],
            'SET' => ['MULTIPLE_CNT' => 1, 'SMART_FILTER' => 'N', 'FILTRABLE' => 'N'],
        ];

        self::renderSettingsAssets($strHTMLControlName);

        $settings = self::normalizeSettings($arProperty['USER_TYPE_SETTINGS'] ?? []);
        $rows = '';

        foreach ($settings as $code => $item) {
            $codeEnc = htmlspecialcharsbx($code);
            $titleEnc = htmlspecialcharsbx($item['TITLE'] ?? '');
            $sortVal = (int)($item['SORT'] ?? 500);
            $typeVal = htmlspecialcharsbx($item['TYPE'] ?? 'string');
            $namePrefix = $strHTMLControlName['NAME'] ?? $strHTMLControlName;

            $rows .= '<tr>
                <td><input type="text" class="inp-code" size="20" value="' . $codeEnc . '"></td>
                <td><input type="text" class="inp-title" size="35" name="' . $namePrefix . '[' . $codeEnc . '_TITLE]" value="' . $titleEnc . '"></td>
                <td><input type="text" class="inp-sort" size="5" name="' . $namePrefix . '[' . $codeEnc . '_SORT]" value="' . $sortVal . '"></td>
                <td><select class="inp-type" name="' . $namePrefix . '[' . $codeEnc . '_TYPE]">' . self::typeOptions($typeVal) . '</select></td>
            </tr>';
        }

        $btnAdd = Loc::getMessage('COMPLEX_CPROP_SETTING_BTN_ADD');
        $titleXml = Loc::getMessage('COMPLEX_CPROP_SETTING_FIELD_TITLE');
        $titleSort = Loc::getMessage('COMPLEX_CPROP_SETTING_FIELD_SORT');
        $titleType = Loc::getMessage('COMPLEX_CPROP_SETTING_FIELD_TYPE');
        $namePrefix = $strHTMLControlName['NAME'] ?? $strHTMLControlName;

        return '<td colspan="2" align="center">
            <table id="mf-settings-table" class="internal" data-name="' . htmlspecialcharsbx($namePrefix) . '">
                <tr class="heading">
                    <td>XML_ID</td>
                    <td>' . $titleXml . '</td>
                    <td>' . $titleSort . '</td>
                    <td>' . $titleType . '</td>
                </tr>
                ' . $rows . '
                <tr>
                    <td colspan="4" align="center">
                        <input type="button" value="' . $btnAdd . '" onclick="addSettingRow()">
                    </td>
                </tr>
            </table>
        </td>';
    }

   

    private static function typeOptions(string $selected = 'string'): string
    {
        $types = [
            'string' => Loc::getMessage('COMPLEX_CPROP_FIELD_TYPE_STRING'),
            'file' => Loc::getMessage('COMPLEX_CPROP_FIELD_TYPE_FILE'),
            'text' => Loc::getMessage('COMPLEX_CPROP_FIELD_TYPE_TEXT'),
            'date' => Loc::getMessage('COMPLEX_CPROP_FIELD_TYPE_DATE'),
            'customhtml' => Loc::getMessage('COMPLEX_CPROP_FIELD_TYPE_HTML')
        ];

        $options = '';
        foreach ($types as $val => $label) {
            $selectedAttr = $val === $selected ? 'selected' : '';
            $options .= '<option value="' . $val . '" ' . $selectedAttr . '>' . htmlspecialcharsbx($label) . '</option>';
        }
        return $options;
    }

    private static function normalizeSettings(?array $settings): array
    {
        $result = [];
        if (empty($settings)) {
            return $result;
        }

        foreach ($settings as $key => $val) {
            if (str_ends_with($key, '_TITLE')) {
                $code = substr($key, 0, -6);
                $result[$code]['TITLE'] = $val;
            } elseif (str_ends_with($key, '_SORT')) {
                $code = substr($key, 0, -5);
                $result[$code]['SORT'] = (int)$val;
            } elseif (str_ends_with($key, '_TYPE')) {
                $code = substr($key, 0, -5);
                $result[$code]['TYPE'] = $val;
            }
        }

        uasort($result, fn($a, $b) => ($a['SORT'] ?? 500) <=> ($b['SORT'] ?? 500));

        return $result;
    }

    private static function renderSettingsAssets($strHTMLControlName): void
    {
        $typeOptions = self::typeOptions();
        ?>
        <script>
        function addSettingRow() {
            const table = document.getElementById('mf-settings-table');
            const row = table.insertRow(-1);
            const randomCode = 'FIELD_' + Math.random().toString(36).substr(2, 8);
            const namePrefix = table.dataset.name;

            row.insertCell(0).innerHTML = '<input type="text" class="inp-code" size="20" value="' + randomCode + '">';
            row.insertCell(1).innerHTML = '<input type="text" class="inp-title" size="35" name="' + namePrefix + '[' + randomCode + '_TITLE]">';
            row.insertCell(2).innerHTML = '<input type="text" class="inp-sort" size="5" value="500" name="' + namePrefix + '[' + randomCode + '_SORT]">';
            row.insertCell(3).innerHTML = '<select class="inp-type" name="' + namePrefix + '[' + randomCode + '_TYPE]"><?php echo $typeOptions; ?></select>';
        }
        </script>
        <style>
        .internal td { text-align: center; }
        .inp-sort { text-align: center; }
        </style>
        <?php
    }

    public static function GetAdminListViewHTML(
        array $arProperty,
        array $value,
        array $strHTMLControlName
    ): string {
        if (empty($value['VALUE'])) {
            return '&nbsp;';
        }

        $data = is_array($value['VALUE'])
            ? $value['VALUE']
            : json_decode($value['VALUE'], true);

        if (!is_array($data)) {
            return htmlspecialcharsbx($value['VALUE']);
        }

        $parts = [];
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                $parts[] = htmlspecialcharsbx($k) . ': ' . htmlspecialcharsbx($v);
            }
        }

        return empty($parts) ? '&nbsp;' : implode('<br>', $parts);
    }

    public static function GetPropertyFieldHtml(
        array $arProperty,
        array $value,
        array $strHTMLControlName
    ): string {
        $fields = self::normalizeSettings($arProperty['USER_TYPE_SETTINGS'] ?? []);

        if (empty($fields)) {
            return '<span>' . Loc::getMessage('COMPLEX_CPROP_ERROR_INCORRECT_SETTINGS') . '</span>';
        }

        self::renderFieldAssets();

        $existing = is_array($value['VALUE'])
            ? $value['VALUE']
            : (json_decode($value['VALUE'], true) ?: []);

        $rows = '';
        foreach ($fields as $code => $field) {
            $rows .= self::renderField(
                $code,
                $field['TITLE'],
                $field['TYPE'],
                $existing[$code] ?? '',
                $strHTMLControlName['VALUE']
            );
        }

        $toggleText = Loc::getMessage('COMPLEX_CPROP_HIDE_TEXT');
        $clearText = Loc::getMessage('COMPLEX_CPROP_CLEAR_TEXT');
        $multiple = $arProperty['MULTIPLE'] === 'Y';

        return '<div class="mf-wrapper">
            <div class="mf-actions">
                <a class="mf-toggle">' . $toggleText . '</a>
                ' . ($multiple ? ' | <a class="mf-delete">' . $clearText . '</a>' : '') . '
            </div>
            <table class="mf-fields">' . $rows . '</table>
        </div>';
    }

  

    private static function renderField(
        string $code,
        string $title,
        string $type,
        string $value,
        string $inputName
    ): string {
        $name = $inputName . '[' . $code . ']';
        $titleEnc = htmlspecialcharsbx($title);
        $valueEnc = htmlspecialcharsbx($value);

        switch ($type) {
            case 'string':
                return '<tr>
                    <td align="right" valign="top">' . $titleEnc . ':</td>
                    <td><input type="text" name="' . $name . '" value="' . $valueEnc . '" size="50"></td>
                <table>';

            case 'text':
                return '<tr>
                    <td align="right" valign="top">' . $titleEnc . ':</td>
                    <td><textarea name="' . $name . '" rows="5" cols="50">' . $valueEnc . '</textarea></td>
                </tr>';

            case 'date':
                return '<tr>
                    <td align="right" valign="top">' . $titleEnc . ':</td>
                    <td>
                        <div class="adm-input-wrap adm-input-wrap-calendar">
                            <input type="text" class="adm-input adm-input-calendar" name="' . $name . '" value="' . $valueEnc . '" size="23">
                            <span class="adm-calendar-icon" onclick="BX.calendar({node:this,field:\'' . $name . '\',bTime:true})"></span>
                        </div>
                    </td>
                </tr>';


            case 'customhtml':
                $editorId = 'html_' . md5($name);
                ob_start();
                CFileMan::AddHTMLEditorFrame($editorId, $value, $editorId . '_TYPE', 'html', ['height' => 250]);
                $editor = ob_get_clean();

                return '</tr>
                    <td align="right" valign="top">' . $titleEnc . ':</td>
                    <td>
                        ' . $editor . '
                        <textarea name="' . $name . '" id="' . $editorId . '_sync" style="display:none">' . $valueEnc . '</textarea>
                        <script>
                        setTimeout(function() {
                            var sync = document.getElementById("' . $editorId . '_sync");
                            if (sync && sync.form) {
                                sync.form.addEventListener("submit", function() {
                                    var ed = BXHtmlEditor && BXHtmlEditor.Get("' . $editorId . '");
                                    if (ed) sync.value = ed.GetContent();
                                });
                            }
                        }, 500);
                        </script>
                    </td>
                </tr>';

            case 'file':
                $fileHtml = '';
                if ($value && !is_array($value)) {
                    $f = CFile::GetFileArray($value);
                    if ($f) {
                        $ext = strtolower(pathinfo($f['FILE_NAME'], PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                        $imgTag = $isImage ? '<img src="' . $f['SRC'] . '" style="max-width:200px;max-height:100px"><br>' : '';
                        $fileHtml = '<div>' . $imgTag . '
                            <label><input type="checkbox" name="' . $name . '[DEL]" value="Y"> ' . Loc::getMessage('COMPLEX_CPROP_FILE_DELETE') . '</label>
                            <input type="hidden" name="' . $name . '[OLD]" value="' . $value . '">
                        </div>';
                    }
                }

                if (!$fileHtml) {
                    return '<tr>
                        <td align="right">' . $titleEnc . ':</td>
                        <td><input type="file" name="' . $name . '"></td>
                    </tr>';
                }

                return '<tr>
                    <td align="right" valign="top">' . $titleEnc . ':</td>
                    <td>' . $fileHtml . '</td>
                </tr>';

            default:
                return '<tr>
                    <td align="right">' . $titleEnc . ':</td>
                    <td><input type="text" name="' . $name . '" value="' . $valueEnc . '"></td>
                </tr>';
        }
    }

    private static function renderFieldAssets(): void
    {
        if (self::$cssShown) {
            return;
        }
        self::$cssShown = true;

        $showText = Loc::getMessage('COMPLEX_CPROP_SHOW_TEXT');
        $hideText = Loc::getMessage('COMPLEX_CPROP_HIDE_TEXT');

        echo '<style>
            .mf-wrapper { margin-bottom: 15px; }
            .mf-actions { color: #666; margin-bottom: 5px; }
            .mf-actions a { cursor: pointer; color: #0050b0; text-decoration: underline; }
            .mf-fields { display: none; border: 1px solid #e0e8ea; padding: 10px; margin-top: 5px; }
            .mf-fields.active { display: block; }
            .mf-fields td { padding: 5px; }
        </style>
        <script>
        document.addEventListener("click", function(e) {
            var target = e.target;
            if (target.classList && target.classList.contains("mf-toggle")) {
                e.preventDefault();
                var wrapper = target.closest(".mf-wrapper");
                if (wrapper) {
                    var tbl = wrapper.querySelector(".mf-fields");
                    if (tbl) {
                        tbl.classList.toggle("active");
                        target.textContent = tbl.classList.contains("active") ? "' . $hideText . '" : "' . $showText . '";
                    }
                }
            }
            if (target.classList && target.classList.contains("mf-delete")) {
                e.preventDefault();
                var wrapper = target.closest(".mf-wrapper");
                if (wrapper) {
                    wrapper.querySelectorAll("input,textarea,select").forEach(function(el) {
                        if (el.type !== "checkbox" && el.type !== "file") {
                            el.value = "";
                        }
                        if (el.type === "checkbox") {
                            el.checked = false;
                        }
                    });
                    wrapper.style.display = "none";
                }
            }
        });
        </script>';
    }

    public static function ConvertToDB(array $arProperty, array $arValue): array
    {
        if (empty($arValue['VALUE']) || !is_array($arValue['VALUE'])) {
            return ['VALUE' => '', 'DESCRIPTION' => ''];
        }

        $fields = self::normalizeSettings($arProperty['USER_TYPE_SETTINGS'] ?? []);
        $toSave = [];

        foreach ($arValue['VALUE'] as $code => $val) {
            if (!isset($fields[$code])) {
                continue;
            }

            if ($fields[$code]['TYPE'] === 'file' && is_array($val)) {
                if (!empty($val['DEL']) && $val['DEL'] === 'Y' && !empty($val['OLD'])) {
                    CFile::Delete($val['OLD']);
                } elseif (!empty($val['name']) && is_uploaded_file($val['tmp_name'])) {
                    $fid = CFile::SaveFile($val, 'complex_prop');
                    if ($fid) {
                        $toSave[$code] = $fid;
                    }
                } elseif (!empty($val['OLD'])) {
                    $toSave[$code] = $val['OLD'];
                }
            } elseif ($fields[$code]['TYPE'] !== 'file') {
                $val = trim($val);
                if ($val !== '') {
                    $toSave[$code] = $val;
                }
            }
        }

        $toSave = array_filter($toSave, fn($v) => !empty($v));

        return empty($toSave)
            ? ['VALUE' => '', 'DESCRIPTION' => '']
            : ['VALUE' => json_encode($toSave, JSON_UNESCAPED_UNICODE), 'DESCRIPTION' => ''];
    }

    public static function ConvertFromDB(array $arProperty, array $arValue): array
    {
        $data = json_decode($arValue['VALUE'], true);
        return is_array($data) ? ['VALUE' => $data] : ['VALUE' => ''];
    }

  
    

    public static function GetPublicViewHTML(
        array $arProperty,
        array $value,
        array $strHTMLControlName
    ): string {
        return self::getPublicView($arProperty['USER_TYPE_SETTINGS'] ?? [], $value['VALUE'] ?? '');
    }

   

    private static function getPublicView(array $settings, $value): string
    {
        $data = is_array($value) ? $value : (json_decode($value, true) ?: []);
        
        if (empty($data)) {
            return '';
        }

        $fields = self::normalizeSettings($settings);
        $items = [];

        foreach ($data as $code => $val) {
            if (!isset($fields[$code]) || empty($val)) {
                continue;
            }

            $title = htmlspecialcharsbx($fields[$code]['TITLE']);
            $type = $fields[$code]['TYPE'];
            $display = '';

            if ($type === 'file') {
                $f = CFile::GetFileArray($val);
                if ($f) {
                    $display = '<a href="' . htmlspecialcharsbx($f['SRC']) . '" target="_blank">'
                        . htmlspecialcharsbx($f['ORIGINAL_NAME']) . '</a>';
                }
            } elseif ($type === 'date') {
                $ts = MakeTimeStamp($val);
                if ($ts) {
                    $display = FormatDate('d.m.Y', $ts);
                }
            } elseif ($type === 'customhtml') {
                $display = $val;
            } else {
                $display = nl2br(htmlspecialcharsbx($val));
            }

            if ($display) {
                $items[] = '<div class="complex-prop-item"><strong>' . $title . ':</strong> ' . $display . '</div>';
            }
        }

        if (empty($items)) {
            return '';
        }

        return '<div class="complex-prop">' . implode('', $items) . '</div>
        <style>
        .complex-prop { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fafafa; }
        .complex-prop-item { margin: 5px 0; line-height: 1.4; }
        .complex-prop-item strong { color: #333; }
        </style>';
    }
}