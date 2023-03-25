jQuery(document).ready(function($) {
    function initAutocomplete() {
        $('.favorite-fighter-autocomplete').each(function() {
            var $inputField = $(this);
            var grade = $inputField.data('grade');
            var locked = $inputField.hasClass('locked');

            $inputField.autocomplete({
                source: function(request, response) {
                    var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), 'i');
                    response($.grep(fighterData, function(value) {
                        return (matcher.test(value.label) || matcher.test(value.team)) && value.grade === grade;
                    }));
                },
                minLength: 0,
                select: function(event, ui) {
                    // Set the selected fighter's ID as the input value
                    $(this).val(ui.item.label);
                    $(this).next('input[type="hidden"]').val(ui.item.value);
                    return false;
                },
                disabled: locked
            });

            if (locked) {
                $inputField.prop('disabled', true);
            }
        });
    }


    // Initialize autocomplete
    initAutocomplete();
});
