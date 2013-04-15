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
            case 'radio':
                this.checked = false;
        }
    });

}