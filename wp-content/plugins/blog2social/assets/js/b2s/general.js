jQuery(window).on("load", function () {
    if (typeof wp.heartbeat == "undefined") {
        jQuery('.b2s-heartbeat-fail').show();
    } else {
        jQuery('.b2s-heartbeat-fail').hide();
    }
    var b2sPolicy = jQuery('#b2sUserAcceptPrivacyPolicy').val();
    if (typeof b2sPolicy !== typeof undefined && b2sPolicy !== false) {
        if (b2sPolicy === 'true') {
            jQuery('#b2sModalPrivacyPolicy').modal('show');
        }
    }
});

jQuery(document).on('click', '.b2s-show-feedback-modal', function () {
    jQuery('#b2sTrailFeedbackModal').modal('show');
});

jQuery(document).on('click', '.b2s-send-trail-feedback', function () {
    jQuery('.b2s-network-auth-info').hide();
    if (jQuery('#b2s-trial_message').val() == "") {
        jQuery('.b2s-feedback-success').fail();
        return false;
    }
    jQuery('#b2sTrailFeedbackModal').modal('hide');
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_send_trail_feedback',
            'feedback': jQuery('#b2s-trial_message').val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery('.b2s-feedback-success').show();
            }
        }
    });
});


jQuery(document).on('click', '.b2s-modal-privacy-policy-accept-btn', function () {
    jQuery('#b2sModalPrivacyPolicy').modal('hide');
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_accept_privacy_policy',
            'accept': true
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
        }
    });
    return false;
});

jQuery(document).on('click', '.b2s-key-area-btn-submit', function () {
    jQuery('.b2s-key-area-success').hide();
    jQuery('.b2s-key-area-fail').hide();
    jQuery('.b2s-key-area-fail-max-use').hide();
    jQuery('.b2s-key-area-fail-no-token').hide();

    if (jQuery('.b2s-key-area-input').val() == "") {
        jQuery('.b2s-key-area-input').addClass('error');
    } else {
        jQuery('.b2s-key-area-btn-submit').prop('disabled', true);
        jQuery('.b2s-key-area-input').removeClass('error');
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                'action': 'b2s_update_user_version',
                'key': jQuery('.b2s-key-area-input').val(),
                'user_id': jQuery('#b2s-license-user').val(),
            },
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery('#b2sInfoKeyModal').modal('show');
                jQuery('.b2s-key-area-btn-submit').prop('disabled', false);
                jQuery('.b2s-trail-premium-info-area').hide();
                if (data.result == true) {
                    jQuery('.b2s-key-area-success').show();
                    if(data.licenseName != false) {
                        jQuery('.b2s-key-area-key-name').html(data.licenseName);
                        jQuery('.b2s-key-name').html(data.licenseName);
                    }
                    jQuery('#b2s-license-user-select').empty();
                    jQuery('#b2s-license-user-select').append(jQuery('<option value="0"></option>'));
                    jQuery('#b2s-license-user-select').trigger("chosen:updated");
                } else {
                    if (data.reason != null && data.reason == 1) {
                        jQuery('.b2s-key-area-fail-max-use').show();
                    } else if (data.reason != null && data.reason == 2) {
                        jQuery('.b2s-key-area-fail-no-token').show();
                    } else {
                        jQuery('.b2s-key-area-fail').show();
                    }

                }
            }
        });
        return false;
    }
});

jQuery(document).on('click', '.b2s-trail-show', function () {
    jQuery('#b2sPreFeatureModal').modal('hide');
    jQuery('#b2sProFeatureModal').modal('hide');
    jQuery('#b2s-trial-modal').modal('show');
});

jQuery(document).on('click', '.b2s-trail-btn-start', function () {
    var checkFail = false;

    if (jQuery('#trial_vorname').val() == "") {
        checkFail = true;
        jQuery('#trial_vorname').addClass('error');
    } else {
        jQuery('#trial_vorname').removeClass('error');
    }

    if (jQuery('#trial_nachname').val() == "") {
        checkFail = true;
        jQuery('#trial_nachname').addClass('error');
    } else {
        jQuery('#trial_nachname').removeClass('error');
    }

    if (!isEmail(jQuery('#trial_email').val())) {
        checkFail = true;
        jQuery('#trial_email').addClass('error');
    } else {
        jQuery('#trial_email').removeClass('error');
    }

    if (checkFail == false) {
        jQuery('.b2s-trail-btn-start').prop('disabled', true);
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                'action': 'b2s_create_trail',
                'vorname': jQuery('#trial_vorname').val(),
                'nachname': jQuery('#trial_nachname').val(),
                'email': jQuery('#trial_email').val(),
                'url': jQuery('#trial_url').val()
            },
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery('.b2s-trail-btn-start').prop('disabled', false);
                if (data.result == true) {
                    jQuery('#b2s-trial-modal').modal('hide');
                    jQuery('.b2s-key-area-success').show();
                    jQuery('.b2s-trail-tracking').show();
                    jQuery('#b2s-trail-tracking-src').attr('src', 'https://www.googleadservices.com/pagead/conversion/1072559458/?label=HB4lCM27oHMQ4uq3_wM&amp;guid=ON&amp;script=0');
                    jQuery('.b2s-key-area-key-name').html(data.lizenzName);
                    jQuery('.b2s-key-name').html(data.lizenzName);
                    jQuery('.b2s-trail-premium-info-area').hide();
                } else {
                    jQuery('.b2s-trail-modal-fail').show();
                }

            }
        });
    }
});

//PREMIUM
jQuery('#b2sPreFeatureModal').on('show.bs.modal', function (e) {
    jQuery(this).find('.modal-title').html(jQuery(e.relatedTarget).attr('data-title'));
});

//PREMIUM-PRO
jQuery('#b2sProFeatureModal').on('show.bs.modal', function (e) {
    jQuery(this).find('.modal-title').html(jQuery(e.relatedTarget).attr('data-title'));
    jQuery(this).find('.modal-body').hide();
    jQuery(this).find('.' + jQuery(e.relatedTarget).attr('data-type')).show();
});

jQuery(document).on('heartbeat-send', function (e, data) {
    data['client'] = 'b2s';
});

jQuery(document).on('click', '.b2s-modal-close', function () {
    jQuery(jQuery(this).attr('data-modal-name')).modal('hide');
    jQuery(jQuery(this).attr('data-modal-name')).hide();
    jQuery('body').removeClass('modal-open');
    jQuery('body').removeAttr('style');
    return false;
});


jQuery(document).on('click', '.b2s-load-info-meta-tag-modal', function () {
    var dataType = jQuery(this).attr('data-meta-type');
    var dataOrigin = jQuery(this).attr('data-meta-origin');
    jQuery('.modal-meta-content').hide();
    jQuery('.meta-body[data-meta-type=' + dataType + '][data-meta-origin=' + dataOrigin + ']').show();
    jQuery('.meta-title[data-meta-origin=' + dataOrigin + ']').show();
    jQuery('#b2s-info-meta-tag-modal').modal('show');
    return false;
});

function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function hideRating(forever)
{
    var data = {
        'action': 'b2s_hide_rating'
    };

    if (forever) {
        data.forever = true;
    }

    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: data
    });
}

jQuery(document).on("click", ".b2s-hide-rating", function (e) {
    e.preventDefault();
    hideRating(false);
    jQuery(this).closest('.panel').remove();
});

jQuery(document).on("click", ".b2s-hide-rating-forever", function (e) {
    e.preventDefault();
    hideRating(true);
    jQuery(this).closest('.panel').remove();
});

jQuery(document).on("click", ".b2s-allow-rating", function (e) {
    hideRating(false);
    jQuery(this).closest('.panel').remove();
});

jQuery(document).on("click", ".b2s-hide-premium-message", function (e) {
    e.preventDefault();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {action: 'b2s_hide_premium_message'}
    });
    jQuery(this).closest('.panel').remove();
});

jQuery(document).on("click", ".b2s-hide-trail-message", function (e) {
    e.preventDefault();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {action: 'b2s_hide_trail_message'}
    });
    jQuery(this).closest('.panel').remove();
});

jQuery(document).on("click", ".b2s-hide-trail-ended-modal", function (e) {
    e.preventDefault();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {action: 'b2s_hide_trail_ended_message'}
    });
    jQuery(this).closest('.panel').remove();
});

jQuery('.b2s-modal-privacy-policy-scroll-content').on('scroll', function () {
    if (jQuery(this).scrollTop() + jQuery(this).innerHeight() >= jQuery(this)[0].scrollHeight) {
        jQuery('.b2s-scroll-modal-down').hide();
    }
});

jQuery(document).on("click", ".b2s-scroll-modal-down", function (e) {
    var total = jQuery('.b2s-modal-privacy-policy-scroll-content')[0].scrollHeight;
    var current = jQuery('.b2s-modal-privacy-policy-scroll-content').scrollTop() + jQuery('.b2s-modal-privacy-policy-scroll-content').innerHeight();
    if (current >= total) {
        jQuery('.b2s-scroll-modal-down').hide();
    } else {
        jQuery('.b2s-modal-privacy-policy-scroll-content').animate({scrollTop: current + 30}, 'slow');
    }
    return false;
});

