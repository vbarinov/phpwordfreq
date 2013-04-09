$(document).ready(function() {
    $('.more button').on('click', function() {
        clear_form_elements('#form');
        $('.fold').removeClass('fold');
        $('.results').hide();
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