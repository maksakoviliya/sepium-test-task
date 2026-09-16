(function ($) {
    'use strict';

    // Сохраняет введённые/выбранные значения полей перед перерисовкой блока характеристик.
    function collectFieldValues($properties) {
        var values = {};

        $properties.find('.name_select_rielt').each(function () {
            var $field = $(this);
            var propertyId = $field.attr('data-property-id');
            var $checkboxGroup = $field.find('.checkbox_property');

            if ($checkboxGroup.length) {
                var checked = [];

                $checkboxGroup.find('input[type="checkbox"]').each(function () {
                    if (this.checked) {
                        checked[checked.length] = $(this).siblings('.ckeck_param').attr('data-val');
                    }
                });

                values[propertyId] = { checkbox: true, value: checked };
                return;
            }

            var $control = $field.find('input.ag_pole_good, select.ag_pole_good').first();

            if ($control.length) {
                values[propertyId] = { checkbox: false, value: $control.val() };
            }
        });

        return values;
    }

    // Возвращает сохранённые значения в новую разметку характеристик.
    function restoreFieldValues($properties, values) {
        $properties.find('.name_select_rielt').each(function () {
            var $field = $(this);
            var saved = values[$field.attr('data-property-id')];

            if (!saved) {
                return;
            }

            if (saved.checkbox) {
                $field.find('.checkbox_property input[type="checkbox"]').each(function () {
                    var val = $(this).siblings('.ckeck_param').attr('data-val');
                    this.checked = $.inArray(val, saved.value) !== -1;
                });
                return;
            }

            $field.find('input.ag_pole_good, select.ag_pole_good').first().val(saved.value);
        });
    }

    // Выбор категории в товаре и обновление блока характеристик.
    $('body').on('change', '.js-category', function () {
        var category = [];
        var $properties = $('.property_all');
        var savedValues = collectFieldValues($properties);

        $(this).closest('.add_good_name_category')
            .toggleClass('category_checked is-selected', this.checked);

        $('.category_checked').each(function () {
            category[category.length] = $(this).attr('data-category-id');
        });

        $properties.addClass('is-loading').attr('aria-busy', 'true');

        $.ajax({
            type: 'POST',
            url: './admin/ajax/property/Refresh_Property_Good.php',
            dataType: 'html',
            data: { category: category },
            success: function (data) {
                if (data != 'no') {
                    $properties.html(data);
                    restoreFieldValues($properties, savedValues);
                }
            },
            error: function () {
                $properties.html('<div class="error-state">Не удалось обновить характеристики.</div>');
            },
            complete: function () {
                $properties.removeClass('is-loading').attr('aria-busy', 'false');
            }
        });
    });
}(jQuery));
