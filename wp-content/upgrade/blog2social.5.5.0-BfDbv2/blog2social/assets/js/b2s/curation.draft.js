jQuery.noConflict();

if (typeof wp.heartbeat !== "undefined") {
    jQuery(document).on('heartbeat-send', function (e, data) {
        data['b2s_heartbeat'] = 'b2s_listener';
    });
    wp.heartbeat.connectNow();
}
jQuery(window).on("load", function () {
    jQuery('#b2sPagination').val("1");
    b2sSortFormSubmit();
});

function b2sSortFormSubmit() {
    jQuery('.b2s-server-connection-fail').hide();
    jQuery('.b2s-loading-area').show();
    jQuery('.b2s-sort-result-area').hide();
    jQuery('.b2s-sort-result-item-area').html("").hide();
    jQuery('.b2s-sort-pagination-area').html("").hide();

    var currentType = jQuery('#b2sType').val();
    if (currentType != "undefined") {
        jQuery('.b2s-post-btn').removeClass('btn-primary').addClass('btn-link');
        jQuery('.b2s-post-' + currentType).removeClass('btn-link').addClass('btn-primary');
    }

    var data = {
        'action': 'b2s_sort_data',
        'b2sSortPostTitle': jQuery('#b2sSortPostTitle').val(),
        'b2sSortPostAuthor': jQuery('#b2sSortPostAuthor').val(),
        'b2sUserAuthId': jQuery('#b2sUserAuthId').val(),
        'b2sPostBlogId': jQuery('#b2sPostBlogId').val(),
        'b2sType': jQuery('#b2sType').val(),
        'b2sShowByDate': jQuery('#b2sShowByDate').val(),
        'b2sPagination': jQuery('#b2sPagination').val(),
        'b2sShowPagination': jQuery('#b2sShowPagination').length > 0 ? jQuery('#b2sShowPagination').val() : 1,
        'b2sUserLang': jQuery('#b2sUserLang').val()
    };

    if (jQuery('#b2sPostsPerPage').length > 0) {
        data['b2sPostsPerPage'] = jQuery('#b2sPostsPerPage').val();
    }

    var legacyMode = true;
    if (jQuery('#isLegacyMode').val() !== undefined) {
        if (jQuery('#isLegacyMode').val() == "1") {
            legacyMode = false; // loading is sync (stack)
        }
    }

    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        async: legacyMode,
        cache: false,
        data: data,
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (typeof data === 'undefined' || data === null) {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            }
            if (data.result == true) {
                jQuery('.b2s-loading-area').hide();
                jQuery('.b2s-sort-result-area').show();
                jQuery('.b2s-sort-result-item-area').html(data.content).show();
                jQuery('.b2s-sort-pagination-area').html(data.pagination).show();

                //extern - Routing from dashboard
                if (jQuery('#b2sPostBlogId').val() !== undefined) {
                    if (jQuery('#b2sPostBlogId').val() != "") {
                        jQuery('.b2sDetailsSchedPostBtn[data-post-id="' + jQuery('#b2sPostBlogId').val() + '"]').trigger('click');
                    }
                }
            } else {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            }
        }
    });
}

jQuery(document).on('click', '#b2s-sort-reset-btn', function () {
    jQuery('#b2sPagination').val("1");
    jQuery('#b2sSortPostTitle').val("");
    jQuery('#b2sSortPostAuthor').prop('selectedIndex', 0);
    jQuery('#b2sSortPostCat').prop('selectedIndex', 0);
    jQuery('#b2sSortPostType').prop('selectedIndex', 0);
    jQuery('#b2sSortPostSchedDate').prop('selectedIndex', 0);
    jQuery('#b2sShowByDate').val("");
    jQuery('#b2sUserAuthId').val("");
    jQuery('#b2sPostBlogId').val("");
    jQuery('#b2sShowByNetwork').val("0");
    jQuery('#b2sSortPostStatus').prop('selectedIndex', 0);
    jQuery('#b2sSortPostPublishDate').prop('selectedIndex', 0);
    b2sSortFormSubmit();
    return false;
});

jQuery(document).on('click', '#b2s-sort-submit-btn', function () {
    jQuery('#b2sPagination').val("1");
    b2sSortFormSubmit();
    return false;
});

jQuery(document).on('click', '.b2s-pagination-btn', function () {
    jQuery('#b2sPagination').val(jQuery(this).attr('data-page'));
    b2sSortFormSubmit();
    return false;
});

jQuery(document).on('change', '.b2s-select', function () {
    jQuery('#b2sPagination').val("1");
    b2sSortFormSubmit();
    return false;
});

jQuery(document).on('keypress', '#b2sSortPostTitle', function (event) {
    if (event.keyCode == 13) {  //Hide Enter
        return false;
    }
});