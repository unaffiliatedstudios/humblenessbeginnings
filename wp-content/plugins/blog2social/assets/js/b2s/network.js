jQuery.noConflict();

jQuery(window).on("load", function () {

    init();

    if (jQuery('.b2s-network-mandant-select').val() == 0 || jQuery('.b2s-network-mandant-select').val() == -1) {
        jQuery('.b2s-network-mandant-btn-delete').hide();
    } else {
        jQuery('.b2s-network-mandant-btn-delete').show();
    }
    jQuery('.b2s-network-details-container-list').hide();
    jQuery('.b2s-network-details-container-list[data-mandant-id="' + jQuery('.b2s-network-mandant-select').val() + '"]').show();

    jQuery('.b2s-network-item-auth-list[data-network-count="true"]').each(function () {
        jQuery('.b2s-network-auth-count-current[data-network-id="' + jQuery(this).attr("data-network-id") + '"').text(jQuery(this).children('li').length - 1);
    });
});


function init() {
    var showMeridian = true;
    if (jQuery('#b2sUserLang').val() == 'de') {
        showMeridian = false;
    }
    jQuery('.b2s-settings-sched-item-input-time').timepicker({
        minuteStep: 15,
        appendWidgetTo: 'body',
        showSeconds: false,
        showMeridian: showMeridian,
        defaultTime: 'current'
    }).on('changeTime.timepicker', function (e) {
        var networkAuthId = jQuery(e.target).attr('data-network-auth-id');
        jQuery('.b2s-settings-sched-item-input-time[data-network-auth-id="' + networkAuthId + '"]').val(e.time.value);
        saveTimeSettings();
    });
}

var timeoutPlus;
var timeoutMinus;
jQuery(document).on('mousedown mouseup', '.b2s-sched-manager-item-input-day-btn-plus', function (e) {
    var networkAuthId = jQuery(this).attr('data-network-auth-id');
    var current = parseInt(jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val());
    if (e.type == "mousedown") {
        timeoutPlus = setInterval(function () {
            if (current < 99) {
                jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(current++);
            } else {
                jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(99);
            }
        }, 100);
    } else {
        clearInterval(timeoutPlus);
        saveTimeSettings();
    }
    return false;
});

jQuery(document).on('mousedown mouseup', '.b2s-sched-manager-item-input-day-btn-minus', function (e) {
    var networkAuthId = jQuery(this).attr('data-network-auth-id');
    var current = parseInt(jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val());
    if (e.type == "mousedown") {
        timeoutMinus = setInterval(function () {
            if (current > 0) {
                jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(current--);
            } else {
                jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(0);
            }
        }, 100);
    } else {
        clearInterval(timeoutMinus);
        saveTimeSettings();
    }
    return false;
});


jQuery(document).on('click', '.b2s-sched-manager-item-input-day-btn-minus', function () {
    var networkAuthId = jQuery(this).attr('data-network-auth-id');
    var current = parseInt(jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val());
    if (current > 0) {
        jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(current - 1);
        saveTimeSettings();
    } else {
        jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(0);
    }
});
jQuery(document).on('click', '.b2s-sched-manager-item-input-day-btn-plus', function () {
    var networkAuthId = jQuery(this).attr('data-network-auth-id');
    var current = parseInt(jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val());
    if (current < 99) {
        jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(current + 1);
        //TODO is show ALL other same items update
        saveTimeSettings();
    } else {
        jQuery('.b2s-sched-manager-item-input-day[data-network-auth-id="' + networkAuthId + '"]').val(99);
    }

});



jQuery(document).on('change', '.b2s-network-mandant-select', function () {
    jQuery('.b2s-network-auth-info').hide();
    if (jQuery(this).val() == 0 || jQuery(this).val() == -1) {
        jQuery('.b2s-network-mandant-btn-delete').hide();
    } else {
        jQuery('.b2s-network-mandant-btn-delete').show();
    }
    jQuery('.b2s-network-details-container-list').hide();
    jQuery('.b2s-network-details-container-list[data-mandant-id="' + jQuery(this).val() + '"]').show();
});

jQuery(document).on('click', '.b2s-network-mandant-btn-delete', function () {
    jQuery('.b2s-network-auth-info').hide();
    jQuery('#b2s-network-delete-mandant').modal('show');
});

jQuery(document).on('click', '.b2s-btn-network-delete-mandant-confirm', function () {
    jQuery('.b2s-network-auth-area').hide();
    jQuery('.b2s-loading-area').show();
    jQuery('#b2s-network-delete-mandant').modal('hide');
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_delete_mandant',
            'mandantId': jQuery('.b2s-network-mandant-select').val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery('.b2s-network-auth-info').hide();
            jQuery('.b2s-network-auth-area').show();
            jQuery('.b2s-loading-area').hide();
            if (data.result == true && data.mandantId >= 1) {
                jQuery('.b2s-network-details-container-list[data-mandant-id="' + data.mandantId + '"]').remove();
                jQuery(".b2s-network-mandant-select option[value='" + data.mandantId + "']").remove();
                jQuery(".b2s-network-mandant-select option[value='-1']").prop('selected', true);
                jQuery(".b2s-network-details-container-list[data-mandant-id='0']").show();
                jQuery('.b2s-network-remove-success').show();
                jQuery('.b2s-network-mandant-btn-delete').hide();
            } else {
                jQuery('.b2s-network-remove-fail').show();
            }
        }
    });
    return false;
});

jQuery(document).on('change', '#b2s-modify-board-and-group-network-selected', function () {
    if (jQuery(this).attr('data-network-id') == 8) {
        var name = jQuery(this.options[this.selectedIndex]).closest('optgroup').prop('label');
        jQuery('#b2s-modify-board-and-group-name').val(name);
    }
    return true;
});

jQuery(document).on('click', '.b2s-network-item-team-btn-manage', function () {
    jQuery('#b2s-manage-auth-team-modal').modal('show');

    if (jQuery('#b2sUserVersion').val() < 3) {
        return false;
    }

    jQuery('.b2s-move-connection').hide();
    jQuery('#b2s-move-connection-failed').hide();
    jQuery('.b2s-connection-assign').hide();
    jQuery('.b2s-assignment-area').hide();
    jQuery('.b2s-connection-owner').hide();
    jQuery('.b2s-assign-error').hide();
    jQuery('#b2s-no-assign-user').hide();
    jQuery('.b2s-loading-area-manage-auth-team-modal').show();
    jQuery('#b2s-assign-info').show();



    jQuery('#b2sUserAuthId').val(jQuery(this).attr('data-network-auth-id'));
    jQuery('#b2sOldMandantId').val(jQuery(this).attr('data-network-mandant-id'));
    jQuery('#b2sNetworkId').val(jQuery(this).attr('data-network-id'));
    jQuery('#b2sNetworkType').val(jQuery(this).attr('data-network-type'));

    if (jQuery('#b2s-move-connection-select').length) {
        jQuery('#b2s-move-connection-select').html(jQuery('.b2s-network-mandant-select').html());
        jQuery("#b2s-move-connection-select option[value='-1']").remove();
        jQuery("#b2s-move-connection-select option[value='" + jQuery(this).attr('data-network-mandant-id') + "']").remove();
        if (jQuery(this).attr('data-network-mandant-id') <= 0) {
            if (jQuery('#b2s-network-select-more-client').length) {
                var temp_options = jQuery('#b2s-move-connection-select #b2s-network-select-more-client').html();
                jQuery("#b2s-move-connection-select optiongroup").remove();
                jQuery("#b2s-move-connection-select").html(temp_options);
            } else {
                jQuery('#b2s-move-connection-input').hide();
                jQuery('#b2s-move-connection-error').show();
            }
        } else {
            if (!jQuery('#b2s-move-connection-select #b2s-network-select-more-client option').length) {
                var temp_options = jQuery("#b2s-move-connection-select option[value='0']");
                jQuery("#b2s-move-connection-select optiongroup").remove();
                jQuery("#b2s-move-connection-select").html(temp_options);
            }
        }
    }

    jQuery.ajax({
        url: ajaxurl,
        type: "GET",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_get_select_mandant_user',
            'networkAuthId': jQuery(this).attr('data-network-auth-id'),
            'owner': jQuery(this).attr('data-connection-owner'),
            'networkId': jQuery('#b2sNetworkId').val(),
            'networkType': jQuery('#b2sNetworkType').val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                if (typeof data.ownerName != 'undefined') {
                    jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
                    jQuery('#b2s-connection-owner-name').text(data.ownerName);
                    jQuery('.b2s-move-connection').show();
                    jQuery('.b2s-connection-owner').show();
                    jQuery('.b2s-assignment-area').show();
                } else if (typeof data.userSelect != 'undefined' && typeof data.assignList != 'undefined') {
                    jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
                    jQuery('#b2s-connection-assign-select').html(data.userSelect);
                    jQuery('.b2s-network-assign-list').html(data.assignList);
                    if (jQuery('#b2s-approved-user-list li').length <= 1) {
                        jQuery('#b2s-approved-user-list').hide();
                    } else {
                        jQuery('#b2s-approved-user-list').show();
                    }
                    if (jQuery('#b2s-select-assign-user').html() == '') {
                        jQuery('#b2s-select-assign-user').attr('disabled', true);
                        jQuery('#b2s-assign-network-user-auth').attr('disabled', true);
                        jQuery('#b2s-assign-info').hide();
                        jQuery('#b2s-no-assign-user').show();
                    } else {
                        jQuery('#b2s-select-assign-user').removeAttr('disabled');
                        jQuery('#b2s-assign-network-user-auth').removeAttr('disabled');
                        jQuery('#b2s-no-assign-user').hide();
                        jQuery('#b2s-assign-info').show();
                    }
                    jQuery('.b2s-move-connection').show();
                    jQuery('.b2s-connection-assign').show();
                    jQuery('.b2s-assignment-area').show();
                } else {
                    jQuery('.b2s-assign-error[data-error-reason="default"]').show();
                    jQuery('.b2s-connection-assign').show();
                    jQuery('.b2s-assignment-area').show();
                }

            } else {
                jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
                jQuery('.b2s-assign-error[data-error-reason="default"]').show();
                jQuery('.b2s-connection-assign').show();
                jQuery('.b2s-assignment-area').show();
            }
            return false;
        }
    });
    return false;
});

jQuery(document).on('click', '#b2s-move-user-auth-to-profile', function () {
    jQuery('.b2s-move-connection').hide();
    jQuery('#b2s-move-connection-failed').hide();
    jQuery('.b2s-connection-assign').hide();
    jQuery('.b2s-assignment-area').hide();
    jQuery('.b2s-assign-error').hide();
    jQuery('.b2s-loading-area-manage-auth-team-modal').show();

    var authId = jQuery('#b2sUserAuthId').val();
    var mandantId = jQuery('#b2s-move-connection-select').val();
    var oldMandantId = jQuery('#b2sOldMandantId').val();
    var networkId = jQuery('#b2sNetworkId').val();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_move_user_auth_to_profile',
            'networkAuthId': authId,
            'mandantId': mandantId,
        },
        error: function () {
            jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
                jQuery('#b2s-manage-auth-team-modal').modal('hide');
                //change show all entry
                jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + ']').attr('data-network-mandant-id', mandantId);
                jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + '] .b2s-network-item-team-btn-manage').attr('data-network-mandant-id', mandantId);
                jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + '] .b2s-network-mandant-name').html('(' + jQuery('#b2s-move-connection-select option:selected').text() + ')');

                //sort entry in show all
                var allArray = [];
                var first = true;
                jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list[data-network-id=' + networkId + '] li').each(function (index, element) {
                    if (first !== true) {
                        allArray.push(element);
                    } else {
                        first = element;
                    }
                });

                for (i = 0; i < allArray.length; i++) {
                    for (j = 0; j < (allArray.length - 1); j++) {
                        if ((jQuery(allArray[j + 1]).attr('data-network-mandant-id') < jQuery(allArray[j]).attr('data-network-mandant-id')) || (jQuery(allArray[j + 1]).attr('data-network-mandant-id') == jQuery(allArray[j]).attr('data-network-mandant-id') && jQuery(allArray[j + 1]).attr('data-network-type') < jQuery(allArray[j]).attr('data-network-type')) || (jQuery(allArray[j + 1]).attr('data-network-mandant-id') == jQuery(allArray[j]).attr('data-network-mandant-id') && jQuery(allArray[j + 1]).attr('data-network-type') == jQuery(allArray[j]).attr('data-network-type') && jQuery(allArray[j + 1]).attr('data-network-auth-id') < jQuery(allArray[j]).attr('data-network-auth-id'))) {
                            var temp = allArray[j + 1];
                            allArray[j + 1] = allArray[j];
                            allArray[j] = temp;
                        }
                    }
                }
                jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').html('');
                jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').append(first);
                for (i = 0; i < allArray.length; i++) {
                    jQuery('.b2s-network-details-container-list[data-mandant-id=-1] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').append(allArray[i]);
                }


                //copy and remove entry in profile
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + oldMandantId + '] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + ']').attr('data-network-mandant-id', mandantId);
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + oldMandantId + '] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + '] .b2s-network-item-team-btn-manage').attr('data-network-mandant-id', mandantId);
                var temp_list_entry = jQuery('.b2s-network-details-container-list[data-mandant-id=' + oldMandantId + '] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + ']');
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + oldMandantId + '] .b2s-network-item-auth-list-li[data-network-auth-id=' + authId + ']').remove();
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + mandantId + '] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').append(temp_list_entry);

                //sort entry in new profile
                var mandantArray = [];
                var mandantFirst = true;
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + mandantId + '] .b2s-network-item-auth-list[data-network-id=' + networkId + '] li').each(function (index, element) {
                    if (mandantFirst !== true) {
                        mandantArray.push(element);
                    } else {
                        mandantFirst = element;
                    }
                });

                for (i = 0; i < mandantArray.length; i++) {
                    for (j = 0; j < (mandantArray.length - 1); j++) {
                        if ((jQuery(mandantArray[j + 1]).attr('data-network-mandant-id') < jQuery(mandantArray[j]).attr('data-network-mandant-id')) || (jQuery(mandantArray[j + 1]).attr('data-network-mandant-id') == jQuery(mandantArray[j]).attr('data-network-mandant-id') && jQuery(mandantArray[j + 1]).attr('data-network-type') < jQuery(mandantArray[j]).attr('data-network-type')) || (jQuery(mandantArray[j + 1]).attr('data-network-mandant-id') == jQuery(mandantArray[j]).attr('data-network-mandant-id') && jQuery(mandantArray[j + 1]).attr('data-network-type') == jQuery(mandantArray[j]).attr('data-network-type') && jQuery(mandantArray[j + 1]).attr('data-network-auth-id') < jQuery(mandantArray[j]).attr('data-network-auth-id'))) {
                            var temp = mandantArray[j + 1];
                            mandantArray[j + 1] = mandantArray[j];
                            mandantArray[j] = temp;
                        }
                    }
                }
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + mandantId + '] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').html('');
                jQuery('.b2s-network-details-container-list[data-mandant-id=' + mandantId + '] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').append(mandantFirst);
                
                for (i = 0; i < mandantArray.length; i++) {
                    jQuery('.b2s-network-details-container-list[data-mandant-id=' + mandantId + '] .b2s-network-item-auth-list[data-network-id=' + networkId + ']').append(mandantArray[i]);
                }
            } else {
                jQuery('#b2s-move-connection-failed').show();
            }
            return false;
        }
    });
    return false;
});

jQuery(document).on('click', '#b2s-assign-network-user-auth', function () {
    if (jQuery('#b2s-select-assign-user').val() <= 0) {
        return false;
    }
    jQuery('.b2s-move-connection').hide();
    jQuery('#b2s-move-connection-failed').hide();
    jQuery('.b2s-connection-assign').hide();
    jQuery('.b2s-assignment-area').hide();
    jQuery('.b2s-assign-error').hide();
    jQuery('.b2s-loading-area-manage-auth-team-modal').show();

    var authId = jQuery('#b2sUserAuthId').val();
    var assignBlogUserId = jQuery('#b2s-select-assign-user').val();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_assign_network_user_auth',
            'networkAuthId': authId,
            'assignBlogUserId': assignBlogUserId,
        },
        error: function () {
            jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
            jQuery('.b2s-assign-error[data-error-reason="default"]').show();
            jQuery('.b2s-move-connection').show();
            jQuery('.b2s-connection-assign').show();
            jQuery('.b2s-assignment-area').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
                jQuery('#b2s-approved-user-list').append(data.newListEntry);
                jQuery('#b2s-approved-user-list').show();
                jQuery('#b2s-select-assign-user option[value="' + assignBlogUserId + '"]').remove();
                if (jQuery('#b2s-select-assign-user').html() == '') {
                    jQuery('#b2s-select-assign-user').attr('disabled', true);
                    jQuery('#b2s-assign-info').hide();
                    jQuery('#b2s-no-assign-user').show();
                }
            } else {
                jQuery('.b2s-assign-error[data-error-reason="' + data.error_reason + '"]').show();
            }
            jQuery('.b2s-loading-area-manage-auth-team-modal').hide();
            jQuery('.b2s-move-connection').show();
            jQuery('.b2s-connection-assign').show();
            jQuery('.b2s-assignment-area').show();
            return false;
        }
    });
    return false;
});

jQuery(document).on('click', '.b2s-modify-board-and-group-network-btn', function () {
    jQuery('.b2s-modify-board-and-group-network-loading-area').show();
    jQuery('.b2s-network-auth-info').hide();
    jQuery('#b2s-modify-board-and-group-network-modal').modal('show');
    jQuery('#b2s-modify-board-and-group-network-modal-title').html(jQuery(this).attr('data-modal-title'));
    jQuery('#b2s-modify-board-and-group-network-auth-id').val(jQuery(this).attr('data-network-auth-id'));
    jQuery('#b2s-modify-board-and-group-network-id').val(jQuery(this).attr('data-network-id'));
    jQuery('#b2s-modify-board-and-group-network-type').val(jQuery(this).attr('data-network-type'));
    jQuery('.b2s-server-connection-fail').hide();
    jQuery('.b2s-modify-board-and-group-network-save-btn').hide();
    jQuery('#b2s-modify-board-and-group-network-no-data').hide();
    jQuery('.b2s-modify-board-and-group-network-data').html("");
    jQuery('.b2s-modify-board-and-group-network-data').show();
    jQuery('#b2s-modify-board-and-group-network-save-success').hide();
    jQuery('#b2s-modify-board-and-group-network-save-error').hide();

    var networkId = jQuery(this).attr('data-network-id');
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_get_network_board_and_group',
            'networkAuthId': jQuery(this).attr('data-network-auth-id'),
            'networkType': jQuery(this).attr('data-network-type'),
            'networkId': networkId,
            'lang': jQuery('#b2sUserLang').val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery('.b2s-modify-board-and-group-network-loading-area').hide();
            if (data.result == true) {
                jQuery('.b2s-modify-board-and-group-network-data').html(data.content);
                jQuery('.b2s-modify-board-and-group-network-save-btn').show();
            } else {
                jQuery('#b2s-modify-board-and-group-network-no-data').show();
            }
        }
    });
    return false;

});


jQuery(document).on('click', '.b2s-modify-board-and-group-network-save-btn', function () {

    jQuery('.b2s-modify-board-and-group-network-save-btn').hide();
    jQuery('.b2s-modify-board-and-group-network-data').hide();
    jQuery('.b2s-modify-board-and-group-network-loading-area').show();

    var networkAuthId = jQuery('#b2s-modify-board-and-group-network-auth-id').val();
    var networkType = jQuery('#b2s-modify-board-and-group-network-type').val();
    var networkId = jQuery('#b2s-modify-board-and-group-network-id').val();
    var name = jQuery('#b2s-modify-board-and-group-name').val();

    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_save_network_board_and_group',
            'networkAuthId': networkAuthId,
            'networkType': networkType,
            'networkId': networkId,
            'boardAndGroup': jQuery('#b2s-modify-board-and-group-network-selected').val(),
            'boardAndGroupName': name,
            'lang': jQuery('#b2sUserLang').val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery('.b2s-modify-board-and-group-network-loading-area').hide();
            if (data.result == true) {
                jQuery('#b2s-modify-board-and-group-network-save-success').show();
            } else {
                jQuery('#b2s-modify-board-and-group-network-save-error').show();
            }
        }
    });
    return false;
});


jQuery(document).on('click', '#b2s-delete-network-sched-post', function () {
    if (!jQuery(this).is(":checked")) {
        jQuery('.b2s-btn-network-delete-auth-confirm-btn').prop('disabled', true);
        jQuery('#b2s-delete-network-sched-post').val('0');
    } else {
        jQuery('.b2s-btn-network-delete-auth-confirm-btn').removeAttr('disabled');
        jQuery('#b2s-delete-network-sched-post').val('1');
    }
});

jQuery(document).on('click', '.b2s-network-item-auth-list-btn-delete', function () {
    jQuery('.b2s-loading-area').show();
    jQuery('.b2s-network-auth-info').hide();
    jQuery('#b2s-network-delete-auth').modal('show');
    jQuery('#b2s-delete-network-auth-id').val(jQuery(this).attr('data-network-auth-id'));
    jQuery('#b2s-delete-network-id').val(jQuery(this).attr('data-network-id'));
    jQuery('#b2s-delete-network-type').val(jQuery(this).attr('data-network-type'));
    jQuery('#b2s-delete-assign-network-auth-id').val(jQuery(this).attr('data-assign-network-auth-id'));
    jQuery('#b2s-delete-blog-user-id').val(jQuery(this).attr('data-blog-user-id'));
    jQuery('.b2s-server-connection-fail').hide();
    jQuery('#b2s-delete-all-assign-text').hide();
    jQuery('.b2s-btn-network-delete-auth-show-post-text').hide();
    jQuery('.b2s-btn-network-delete-auth-show-post-btn').hide();
    jQuery('.b2s-btn-network-delete-auth-confirm-text').hide();
    jQuery('.b2s-btn-network-delete-auth-confirm-btn').hide();
    jQuery('.b2s-btn-network-delete-sched-text').hide();
    jQuery('.b2s-btn-network-delete-assign-text').hide();
    jQuery('.b2s-btn-network-delete-assign-sched-text').hide();
    var countSchedId = jQuery(this).attr('data-network-auth-id');
    if (typeof jQuery(this).attr('data-assign-network-auth-id') != 'undefined' && jQuery(this).attr('data-assign-network-auth-id') > 0) {
        countSchedId = jQuery(this).attr('data-assign-network-auth-id');
    }

    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_get_sched_posts_by_user_auth',
            'networkAuthId': countSchedId
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery('.b2s-loading-area').hide();
            if (data.result == true) {
                jQuery('.b2s-btn-network-delete-auth-confirm-btn').removeAttr('disabled');
                if (data.count >= 1) {
                    jQuery('.b2s-btn-network-delete-auth-show-post-text').show();
                    jQuery('.b2s-btn-network-delete-sched-text').show();
                    jQuery('.b2s-btn-network-delete-auth-show-post-btn').show();
                    jQuery('#b2s-btn-network-delete-auth-show-post-count').text(data.count);
                    jQuery('.b2s-btn-network-delete-auth-confirm-btn').prop('disabled', true);
                }
                if (data.assignListCount >= 1) {
                    jQuery('.b2s-btn-network-delete-assign-text').show();
                    jQuery('#b2s-delete-assignment').val('all');
                    jQuery('.b2s-btn-network-delete-auth-confirm-btn').prop('disabled', true);
                    jQuery('.b2s-btn-network-delete-auth-show-post-text').show();
                } else {
                    jQuery('#b2s-delete-assignment').val('');
                }
                if (data.assignCount >= 1) {
                    jQuery('.b2s-btn-network-delete-assign-sched-text').show();
                    jQuery('.b2s-btn-network-delete-auth-confirm-btn').prop('disabled', true);
                    jQuery('.b2s-btn-network-delete-auth-show-post-text').show();
                }
                jQuery('#b2s-delete-assign-list').val(data.assignList);
            }
            jQuery('.b2s-btn-network-delete-auth-confirm-text').show();
            jQuery('.b2s-btn-network-delete-auth-confirm-btn').show();
            jQuery('#b2s-delete-network-sched-post').prop('checked', false);
            jQuery('#b2s-delete-network-sched-post').val('0');
        }
    });
    return false;
});

jQuery(document).on('click', '.b2s-btn-network-delete-auth-show-post-btn', function () {
    window.location.href = jQuery('#b2s-redirect-url-sched-post').val() + "&b2sUserAuthId=" + jQuery('#b2s-delete-network-auth-id').val();
    return false;
});

jQuery(document).on('click', '.b2s-btn-network-delete-auth-confirm-btn', function () {
    jQuery('.b2s-network-auth-area').hide();
    jQuery('.b2s-loading-area').show();
    jQuery('#b2s-manage-auth-team-modal').modal('hide');
    jQuery('#b2s-network-delete-auth').modal('hide');
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_delete_user_auth',
            'networkAuthId': jQuery('#b2s-delete-network-auth-id').val(),
            'networkId': jQuery('#b2s-delete-network-id').val(),
            'networkType': jQuery('#b2s-delete-network-type').val(),
            'deleteSchedPost': jQuery('#b2s-delete-network-sched-post').val(),
            'assignNetworkAuthId': jQuery('#b2s-delete-assign-network-auth-id').val(),
            'blogUserId': jQuery('#b2s-delete-blog-user-id').val(),
            'deleteAssignment': jQuery('#b2s-delete-assignment').val(),
            'assignList': jQuery('#b2s-delete-assign-list').val()
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            jQuery('.b2s-network-auth-info').hide();
            jQuery('.b2s-network-auth-area').show();
            jQuery('.b2s-loading-area').hide();
            if (data.result == true && data.networkAuthId >= 1 && data.networkId >= 1) {
                jQuery('.b2s-network-item-auth-list-btn-delete[data-network-auth-id="' + data.networkAuthId + '"]').parent('div').parent('li').remove();
                var networkCount = jQuery('.b2s-network-auth-count-current[data-network-count-trigger="true"][data-network-id="' + data.networkId + '"').text();
                if (networkCount != "0") {
                    var newCount = parseInt(networkCount) - 1;
                    jQuery('.b2s-network-auth-count-current[data-network-id="' + data.networkId + '"').text(newCount);
                }
                jQuery('.b2s-network-remove-success').show();
            } else {
                jQuery('.b2s-network-remove-fail').show();
            }
        }
    });
    return false;
});

jQuery(document).on('click', '.b2s-network-add-mandant-btn-save', function () {
    if (!jQuery('.b2s-network-add-mandant-input').val()) {
        jQuery('.b2s-network-add-mandant-input').addClass('error');
    } else {
        jQuery('.b2s-network-add-mandant-btn-loading').show();
        jQuery('.b2s-network-add-mandant-btn-save').hide();
        jQuery('.b2s-network-add-mandant-input').removeClass('error');
        jQuery('.b2s-server-connection-fail').hide();
        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            dataType: "json",
            cache: false,
            data: {
                'action': 'b2s_save_user_mandant',
                'mandant': jQuery('.b2s-network-add-mandant-input').val()
            },
            error: function () {
                jQuery('.b2s-server-connection-fail').show();
                return false;
            },
            success: function (data) {
                jQuery('.b2s-network-auth-info').hide();
                jQuery('.b2s-network-add-mandant-btn-loading').hide();
                jQuery('.b2s-network-add-mandant-btn-save').show();
                if (data.result == true) {
                    jQuery('.b2s-network-mandant-select optgroup[id="b2s-network-select-more-client"]').append('<option value="' + data.mandantId + '">' + data.mandantName + '</option>');
                    jQuery('.b2s-network-details-container').append(data.content);
                    jQuery('.b2s-network-mandant-select option[value="' + data.mandantId + '"]').prop('selected', true);
                    jQuery('.b2s-network-details-container-list').hide();
                    jQuery('.b2s-network-details-container-list[data-mandant-id="' + data.mandantId + '"]').show();
                    jQuery('.b2s-network-add-mandant-success').show();
                } else {
                    jQuery('.b2s-network-add-mandant-error').show();
                }
                jQuery('#b2s-network-add-mandant').modal('hide');

                jQuery('.b2s-network-item-auth-list[data-network-count="true"]').each(function () {
                    jQuery('.b2s-network-auth-count-current[data-network-id="' + jQuery(this).attr("data-network-id") + '"').text(jQuery(this).children('li').length);
                });

            }
        });
    }
});

jQuery(document).on('change', '.b2s-network-add-mandant-input', function () {
    if (jQuery(this).val() != "") {
        jQuery(this).removeClass('error');
    }
});

window.addEventListener('message', function (e) {
    if (e.origin == jQuery('#b2sServerUrl').val()) {
        var data = JSON.parse(e.data);
        loginSuccess(data.networkId, data.networkType, data.displayName, data.networkAuthId, data.mandandId);
    }
});

function loginSuccess(networkId, networkType, displayName, networkAuthId, mandandId) {
    jQuery('.b2s-network-auth-info').hide();
    jQuery('.b2s-network-auth-success').show();
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_network_save_auth_to_settings',
            'mandandId': mandandId,
            'networkAuthId': networkAuthId
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {}
    });

    var typ = new Array('Profile', 'Page', 'Group');
    var days = 'Days';
    if (jQuery('#b2sUserLang').val() == 'de') {
        typ = new Array('Profil', 'Seite', 'Gruppe');
        days = 'Tage';
    }
    //NEW
    if (jQuery('.b2s-network-item-auth-list-li[data-network-auth-id="' + networkAuthId + '"]').length == 0) {
        var html = "<li class='b2s-network-item-auth-list-li b2s-label-success-border-left' data-network-auth-id='" + networkAuthId + "' data-network-mandant-id='" + mandandId + "' data-network-id='" + networkId + "' data-network-type='" + networkType + "'>";
        html += '<div class="pull-left"><span class="b2s-network-item-auth-type">' + typ[networkType] + '</span>: ';
        html += '<span class="b2s-network-item-auth-user-name">' + displayName + '</span>';
        if (mandandId >= 0) {
            var mandantName = jQuery(".b2s-network-mandant-select option:selected").text();
            if (mandandId <= 0) {
                mandantName = jQuery(".b2s-network-mandant-select option[value='0']").text();
            }
            html += ' <span class="b2s-network-mandant-name">(' + mandantName + ')</span>';
        }

        var today = new Date();
        if (today.getMinutes() >= 30) {
            today.setHours(today.getHours() + 1);
            today.setMinutes(0);
        } else {
            today.setMinutes(30);
        }
        var time = formatAMPM(today);
        if (jQuery('#b2sUserLang').val() == 'de') {
            time = padDate(today.getHours()) + ':' + padDate(today.getMinutes());
        }

        html += '</div>';
        html += '<div class="pull-right">';
        html += '<a class="b2s-network-item-auth-list-btn-delete b2s-add-padding-network-delete pull-right" data-network-type="' + networkType + '" data-network-id="' + networkId + '" data-network-auth-id="' + networkAuthId + '" href="#"><span class="glyphicon  glyphicon-trash glyphicon-grey"></span></a>';

        if (jQuery('#b2sUserVersion').val() == '0') {
            html += '<span class="b2s-sched-manager-premium-area pull-right hidden-xs"  style="width: 240px;"><span class="label label-success"><a href="#" class="btn-label-premium" data-toggle="modal" data-target="#b2sInfoSchedTimesModal">PREMIUM</a></span></span>';
        } else {
            html += '<span class="b2s-sched-manager-time-area pull-right b2s-sched-manager-add-padding hidden-xs" style="margin-right:30px !important;">';
            html += '<input class="form-control b2s-box-sched-time-input b2s-settings-sched-item-input-time" type="text" value="' + time + '" readonly data-network-auth-id="' + networkAuthId + '" data-network-mandant-id="' + mandandId + '" data-network-id="' + networkId + '" data-network-type="' + networkType + '" name="b2s-user-sched-data[time][' + networkAuthId + ']">';
            html += '</span>';
            html += '<span class="b2s-sched-manager-day-area pull-right hidden-xs" style=""><span class="b2s-sched-manager-item-input-day-btn-minus" data-network-auth-id="' + networkAuthId + '">-</span> <span class="b2s-text-middle">+</span>';
            html += '<input type="text" class="b2s-sched-manager-item-input-day" data-network-auth-id="' + networkAuthId + '" data-network-mandant-id="' + mandandId + '" data-network-id="' + networkId + '" data-network-type="' + networkType + '" name="b2s-user-sched-data[delay_day][' + networkAuthId + ']" value="0" readonly> <span class="b2s-text-middle">' + days + '</span> <span class="b2s-sched-manager-item-input-day-btn-plus" data-network-auth-id="' + networkAuthId + '">+</span></span>';
        }
        html += '</div>';
        html += '<div class="clearfix"></div>';
        html += '</li>';

        jQuery(html).insertAfter('.b2s-network-item-auth-list-li[data-network-mandant-id="' + mandandId + '"][data-network-id="' + networkId + '"][data-view="all"]:first');
        jQuery(html).insertAfter('.b2s-network-item-auth-list-li[data-network-mandant-id="' + mandandId + '"][data-network-id="' + networkId + '"][data-view="selected"]:first');
        jQuery('.b2s-settings-sched-item-input-time[data-network-auth-id="' + networkAuthId + '"]').val(time);
        var networkCount = jQuery('.b2s-network-auth-count-current[data-network-count-trigger="true"][data-network-id="' + networkId + '"').text();
        var newCount = parseInt(networkCount) + 1;
        jQuery('.b2s-network-auth-count-current[data-network-id="' + networkId + '"').text(newCount);
        init();
        saveTimeSettings();

        //Update
    } else {
        jQuery('.b2s-network-auth-update-btn[data-network-auth-id="' + networkAuthId + '"').show();
        if (jQuery('.b2s-network-item-auth-list-li[data-network-auth-id="' + networkAuthId + '"][data-network-mandant-id="' + mandandId + '"][data-network-id="' + networkId + '"][data-network-type="' + networkType + '"]').length > 0) {
            var html = '<span class="b2s-network-item-auth-type">' + typ[networkType] + '</span>: ';
            html += '<span class="b2s-network-item-auth-user-name">' + displayName + '</span>';
            if (mandandId >= 0) {
                var mandantName = jQuery(".b2s-network-mandant-select option:selected").text();
                if (mandandId <= 0) {
                    mandantName = jQuery(".b2s-network-mandant-select option[value='0']").text();
                }
                html += ' <span class="b2s-network-mandant-name">(' + mandantName + ')</span>';
            }
            jQuery('.b2s-network-item-auth-list-li[data-network-auth-id="' + networkAuthId + '"][data-network-mandant-id="' + mandandId + '"][data-network-id="' + networkId + '"][data-network-type="' + networkType + '"] div:first').html(html);
            jQuery('.b2s-network-item-auth-list-li[data-network-auth-id="' + networkAuthId + '"][data-network-mandant-id="' + mandandId + '"][data-network-id="' + networkId + '"][data-network-type="' + networkType + '"]').removeClass('b2s-label-danger-border-left').addClass('b2s-label-success-border-left');    
        }
    }
}


jQuery(document).on('click', '.b2s-get-settings-sched-time-default', function () {
    jQuery('.b2s-server-connection-fail').hide();
    jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: {
            'action': 'b2s_get_settings_sched_time_default',
        },
        error: function () {
            jQuery('.b2s-server-connection-fail').show();
            return false;
        },
        success: function (data) {
            if (data.result == true) {
                jQuery.each(data.times, function (network_id, time) {
                    time.forEach(function (network_type_time, count) {
                        if (network_type_time != "") {
                            jQuery('.b2s-settings-sched-item-input-time[data-network-id="' + network_id + '"][data-network-type="' + count + '"]').val(network_type_time);
                            count++;
                        }
                    });
                });
                saveTimeSettings();
            }
        }
    });
    return false;
});

function saveTimeSettings() {
    jQuery('.b2s-settings-user-error').hide();
    jQuery.ajax({
        processData: false,
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        cache: false,
        data: jQuery('#b2sSaveTimeSettings').serialize(),
        error: function () {
            return false;
        },
        success: function (data) {
            if (data.result !== true) {
                jQuery('.b2s-settings-user-error').show();
            }
        }
    });
}

function wop(url, name) {
    jQuery('.b2s-network-auth-info').hide();
    jQuery('.b2s-network-auth-success').hide();
    var location = encodeURI(window.location.protocol + '//' + window.location.hostname);
    window.open(url + '&location=' + location, name, "width=650,height=900,scrollbars=yes,toolbar=no,status=no,resizable=no,menubar=no,location=no,directories=no,top=20,left=20");
}

function showFilter(typ) {
    if (typ == 'show') {
        jQuery('.filterShow').hide();
        jQuery('.form-inline').show();
        jQuery('.filterHide').show();
    } else {
        jQuery('.filterShow').show();
        jQuery('.form-inline').hide();
        jQuery('.filterHide').hide();
    }
}


function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}

function padDate(n) {
    return ("0" + n).slice(-2);
}
