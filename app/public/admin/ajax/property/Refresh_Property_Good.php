<?php

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/src/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

// Упрощённая обезличенная копия реального legacy-обработчика.
function property($property)
{
    $place = '';
    if ($property['place_prop'] != '') {
        $place = '<div class="field-help">' . h($property['place_prop']) . '</div>';
    }

    $idProp = $property['id'];
    $allOption = '';

    if ($property['type_prop'] == '1') {
        $result = '<div class="property-field name_select_rielt" data-property="' . $idProp . '" data-property-id="' . $idProp . '">
            <div class="field-label name">' . h($property['name_prop']) . '</div>
            ' . $place . '
            <input type="text" class="text-input add-inp ag_pole_good" placeholder="' . h($property['name_prop']) . '">
        </div>';
    } elseif ($property['type_prop'] == '2') {
        $answersStmt = db()->prepare('SELECT * FROM property_answer_s WHERE id_prop = ? ORDER BY sort_answer');
        $answersStmt->execute(array($idProp));

        while ($answer = $answersStmt->fetch()) {
            $allOption .= '<option value="' . $answer['id'] . '">' . h($answer['answer_prop']) . '</option>';
        }

        $result = '<div class="property-field name_select_rielt" data-property="' . $idProp . '" data-property-id="' . $idProp . '">
            <div class="field-label name">' . h($property['name_prop']) . '</div>
            ' . $place . '
            <select class="text-input ag_pole_good">
                <option value="">Не выбрано</option>' . $allOption . '
            </select>
        </div>';
    } elseif ($property['type_prop'] == '3') {
        $answersStmt = db()->prepare('SELECT * FROM property_answer_s WHERE id_prop = ? ORDER BY sort_answer');
        $answersStmt->execute(array($idProp));
        $checkboxes = '';

        while ($answer = $answersStmt->fetch()) {
            $checkboxes .= '<label class="choice line_chek">
                <input type="checkbox">
                <span class="ckeck_param" data-val="' . $answer['id'] . '">' . h($answer['answer_prop']) . '</span>
            </label>';
        }

        $result = '<div class="property-field name_select_rielt" data-property="' . $idProp . '" data-property-id="' . $idProp . '">
            <div class="field-label name">' . h($property['name_prop']) . '</div>
            ' . $place . '
            <div class="choice-grid checkbox_property ag_pole_good">' . $checkboxes . '</div>
        </div>';
    } elseif ($property['type_prop'] == '4') {
        $result = '<div class="property-field name_select_rielt" data-property="' . $idProp . '" data-property-id="' . $idProp . '">
            <div class="field-label name">' . h($property['name_prop']) . '</div>
            ' . $place . '
            <input type="text" inputmode="decimal" class="text-input add-inp ag_pole_good" placeholder="Числовое значение">
        </div>';
    } else {
        $result = '';
    }

    return $result;
}

$category = isset($_POST['category']) ? $_POST['category'] : array();
$categoryIds = array();

if (is_array($category)) {
    foreach ($category as $categoryId) {
        if (is_scalar($categoryId) && preg_match('/^\d+$/', (string) $categoryId)) {
            $categoryIds[] = (int) $categoryId;
        }
    }
}

$result = '';
$properties = db()->query('SELECT * FROM property_s ORDER BY sort_prop');

while ($property = $properties->fetch()) {
    $catProp = trim($property['cat_prop']);

    if ($catProp === '') {
        $result .= property($property);
        continue;
    }

    $propertyCategoryIds = array_map('intval', explode(',', $catProp));

    if (array_intersect($categoryIds, $propertyCategoryIds)) {
        $result .= property($property);
    }
}

echo $result === '' ? 'no' : $result;
