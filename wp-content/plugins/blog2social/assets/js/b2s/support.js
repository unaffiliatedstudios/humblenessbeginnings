jQuery.noConflict();


jQuery(document).ready(function () {
    getWidgetFaq();
    initTroubleshootTool();
    if (window.location.href.match('b2s-support-system-check') != null) {
        jQuery('.b2s-support-check-sytem').trigger('click');
    }
    if (window.location.href.match('b2s-support-sharing-debugger') != null) {
        jQuery('.b2s-support-sharing-debugger').trigger('click');
    }
    jQuery(document).on('click', '.b2s-btn-sharing-debugger', function () {
        var networkId = jQuery(this).attr('data-network-id');
        if (networkId != 2) {
            var url = jQuery(this).attr('b2s-url-query') + encodeURIComponent(jQuery('#b2s-debug-url[data-network-id="' + networkId + '"').val());
        } else {
            var url = jQuery(this).attr('b2s-url-query');
        }
        window.open(url, '_blank');
        return false;
    });
});

function getWidgetFaq() {
    jQuery('.b2s-faq-area').show();
    if (typeof wp.heartbeat == "undefined") {
        jQuery('#b2s-heartbeat-fail').show();
    }
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_get_faq_entries'
        },
        error: function () {
            jQuery('.b2s-faq-area').hide();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery('.b2s-loading-area-faq').hide();
                jQuery('.b2s-faq-content').html(data.content);
            } else {
                jQuery('.b2s-faq-area').hide();
            }
        }
    });
}



function initTroubleshootTool() {
    firstClick = false;
    jQuery(document).on('click', '.b2s-support-check-sytem', function () {
        if (!firstClick) {
            jQuery('#b2s-reload-debug-btn').trigger('click');
            firstClick = true;
        }
    });

    jQuery(document).on('click', '#b2s-reload-debug-btn', function () {
        jQuery('.b2s-server-connection-fail').hide();
        jQuery('.b2s-support-fail').hide();
        jQuery('#b2s-main-debug').hide();
        jQuery('.b2s-loading-area').show();

        jQuery.ajax({
            url: ajaxurl,
            type: "GET",
            dataType: "json",
            cache: false,
            data: {'action': 'b2s_support_systemrequirements'},
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                jQuery('.b2s-loading-area').hide();
                jQuery('#b2s-main-debug').show();
                return false;
            },
            success: function (data) {
                if (typeof data === 'undefined' || data === null) {
                    jQuery('.b2s-server-connection-fail').show();
                    jQuery('.b2s-loading-area').hide();
                    jQuery('#b2s-main-debug').show();
                    return false;
                } else if (data.result != true) {
                    if (data.error == 'admin') {
                        jQuery('.b2s-loading-area').hide();
                        jQuery('#b2s-support-no-admin').show();
                    } else {
                        jQuery('.b2s-server-connection-fail').show();
                        jQuery('.b2s-loading-area').hide();
                        jQuery('#b2s-main-debug').show();
                        return false;
                    }
                } else {
                    if (typeof data.htmlData !== 'undefined') {
                        jQuery('#b2s-debug-htmlData').html(data.htmlData);
                        if (typeof data.blogData !== 'undefined') {
                            jQuery('#b2s-debug-export').removeClass('b2s-support-link-not-active');
                            jQuery('#b2s-debug-export').attr(
                                    "href", "data:application/octet-stream;charset=utf-8;base64," +
                                    btoa(JSON.stringify(data.blogData, undefined, 2))
                                    );
                        } else {
                            jQuery('#b2s-debug-export').addClass('b2s-support-link-not-active');
                        }
                        jQuery('.b2s-loading-area').hide();
                        jQuery('#b2s-main-debug').show();
                        return true;
                    } else {
                        jQuery('.b2s-server-connection-fail').show();
                        jQuery('.b2s-loading-area').hide();
                        jQuery('#b2s-main-debug').show();
                        return false;
                    }
                }
            }
        });

    });
}