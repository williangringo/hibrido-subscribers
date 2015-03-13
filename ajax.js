jQuery(document).ready(function ($) {

    var $form = $('form[data-hibrido-subscribers-form]');

    if ( ! $form.length) {
        return;
    }

    var $button = $form.find('button[type="submit"]');
    var originalButtonText = $button.text();
    var $responseWrapper = $('[data-hibrido-subscribers-response]');

    $form.ajaxForm({
        data: {
            action: hibrido_subscribers_ajax_object.action
        },
        dataType: 'json',
        beforeSend: function () {
            $responseWrapper.removeClass('success error').html('');
            $button.text(hibrido_subscribers_ajax_object.loadingButtonText).attr('disabled', '');
        },
        complete: function () {
            $button.text(originalButtonText).removeAttr('disabled');
        },
        success: function (response) {
            if (response.success) {
                $responseWrapper.addClass('success');
            } else {
                $responseWrapper.addClass('error');
            }

            $responseWrapper.html(response.msg);
        }
    });

});
