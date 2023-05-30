jQuery(document).ready(function($) {
    $('.service-calculator-delete-option').on('click', function() {
        var option = $(this).data('option');
        var key = $(this).data('key');
        var li = $(this).closest('li');
        li.remove();
    });
});
