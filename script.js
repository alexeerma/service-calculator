jQuery(document).ready(function($) {
    $('.service-calculator-add-option').on('click', function() {
        var option = $(this).data('option');
        var list = $('#service-calculator-' + option + '-list');

        var index = list.find('li').length;
        var li = $('<li>');

        var nameInput = $('<input>');
        nameInput.attr('type', 'text');
        nameInput.attr('name', option + '[' + index + '][name]');
        nameInput.attr('value', '');
        li.append(nameInput);

        var separator = $('<span>').text(' : ');
        li.append(separator);

        var priceInput = $('<input>');
        priceInput.attr('type', 'number');
        priceInput.attr('name', option + '[' + index + '][price]');
        priceInput.attr('value', '');
        li.append(priceInput);

        var deleteButton = $('<button>');
        deleteButton.attr('type', 'button');
        deleteButton.addClass('service-calculator-delete-option');
        deleteButton.data('option', option);
        deleteButton.data('key', index);
        deleteButton.text('Delete');
        li.append(deleteButton);

        list.append(li);
    });

    $(document).on('click', '.service-calculator-delete-option', function() {
        var option = $(this).data('option');
        var key = $(this).data('key');
        var li = $(this).closest('li');
        li.remove();
    });

    $('#service-calculator-calculate').on('click', function() {
        var formData = $('#service-calculator-form').serialize();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: 'action=service_calculator_calculate_price&' + formData,
            success: function(response) {
                $('#service-calculator-result').html(response);
            }
        });
    });
});

