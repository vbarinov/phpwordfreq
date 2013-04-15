$(document).ready(function() {
    $('.more button').on('click', function() {
        clear_form_elements('#form');
        $('.fold').removeClass('fold');
        $('.results').hide();
    })

    $('#form').on('submit', function(e) {
        var self = $(this),
            btn = $('button[type="submit"]', self);

        if (!self.hasClass('processing')) {
            self.addClass('processing');
            btn.addClass('disabled').html('<i class="icon-filter icon-white"></i> Обработка...');
            return true;
        } else {
            e.preventDefault();
            return false;
        }
    })

    $('#morph').on('click', function() {
        guess_visible($(this), $('.typeofmorph'));
    })

    guess_visible($('#morph'), $('.typeofmorph'));
})

function clear_form_elements(ele) {
    $(ele).find(':input').each(function() {
        switch(this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
                this.checked = false;
                break;
            case 'radio':
        }
    });

}

function guess_visible(toggle, block) {
    if (toggle.is(':checked')) block.show();
    else block.hide();
}