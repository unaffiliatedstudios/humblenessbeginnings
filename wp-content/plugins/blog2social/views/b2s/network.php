<?php
require_once B2S_PLUGIN_DIR . 'includes/B2S/Network/Item.php';
$b2sSiteUrl = get_option('siteurl') . ((substr(get_option('siteurl'), -1, 1) == '/') ? '' : '/');
$displayName = stripslashes(get_user_by('id', B2S_PLUGIN_BLOG_USER_ID)->display_name);
$displayName = ((empty($displayName) || $displayName == false) ? __("Unknown username", "blog2social") : $displayName);
$networkItem = new B2S_Network_Item();
$networkData = $networkItem->getData();
?>

<div class="b2s-container">
    <div class=" b2s-inbox col-md-12 del-padding-left">
        <div class="col-md-9 del-padding-left del-padding-right">
            <!--Header|Start - Include-->
            <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/header.phtml'); ?>
            <!--Header|End-->
            <div class="clearfix"></div>
            <!--Content|Start-->
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="col-md-12">
                        <div class="b2s-post">
                            <div class="grid-body">
                                <div class="hidden-lg hidden-md hidden-sm filterShow"><a href="#" onclick="showFilter('show');return false;"><i class="glyphicon glyphicon-chevron-down"></i> <?php _e('filter', 'blog2social') ?></a></div>
                                <div class="hidden-lg hidden-md hidden-sm filterHide"><a href="#" onclick="showFilter('hide');return false;"><i class="glyphicon glyphicon-chevron-up"></i> <?php _e('filter', 'blog2social') ?></a></div>
                                <div class="form-inline" role="form">
                                    <?php echo $networkItem->getSelectMandantHtml($networkData['mandanten']); ?>
                                    <div class="form-group b2s-network-mandant-area">
                                        <?php if (B2S_PLUGIN_USER_VERSION > 1) { ?>
                                            <button href="#" class="btn btn-primary btn-sm " data-toggle="modal" data-target="#b2s-network-add-mandant">
                                                <span class="glyphicon glyphicon-plus"></span> <?php _e('Create new network collection', 'blog2social') ?> <span class="label label-success"></button>
                                        <?php } else { ?>
                                            <button href="#" class="btn btn-primary btn-sm b2s-btn-disabled" data-toggle="modal" data-type="create-network-profile" data-title="<?php _e('You want to define a new combination of networks?', 'blog2social') ?>" data-target="#b2sProFeatureModal">
                                                <span class="glyphicon glyphicon-plus"></span> <?php _e('Create new network collection', 'blog2social') ?> <span class="label label-success"> <?php _e("PREMIUM", "blog2social") ?></span></button>
                                        <?php } ?>

                                        <button href="#" class="btn btn-danger btn-sm b2s-network-mandant-btn-delete" style="display:none;">
                                            <span class="glyphicon glyphicon-trash"></span> <?php _e('Delete', 'blog2social') ?>
                                        </button>
                                    </div>
                                    <div class="form-group b2s-network-time-manager-area pull-right hidden-xs">
                                        <?php if (B2S_PLUGIN_USER_VERSION > 0) { ?>
                                            <a href="#" class="btn btn-primary btn-sm b2s-get-settings-sched-time-default">
                                            <?php } else { ?>
                                                <a href="#" class="btn btn-primary btn-sm b2s-btn-disabled" data-title = "<?php _e('You want to schedule your posts and use the Best Time Scheduler?', 'blog2social') ?>" data-toggle ="modal" data-target ="#b2sInfoSchedTimesModal">
                                                <?php } ?>  <span class="glyphicon glyphicon-time"></span> <?php _e('Load Best Times', 'blog2social'); ?></a>
                                    </div>
                                </div>
                                <br>
                            </div>
                        </div>
                        <div class="row b2s-network-auth-area">
                            <?php echo $networkItem->getPortale($networkData['mandanten'], $networkData['auth'], $networkData['portale'], $networkData['auth_count']); ?>
                        </div>
                        <div class="row b2s-loading-area width-100" style="display: none">
                            <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
                            <div class="clearfix"></div>
                            <?php _e('Loading...', 'blog2social') ?>
                        </div>
                        <?php
                        $noLegend = 1;
                        require_once (B2S_PLUGIN_DIR . 'views/b2s/html/footer.phtml');
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/sidebar.phtml'); ?>
    </div>
</div>
<input type="hidden" id="lang" value="<?php echo substr(B2S_LANGUAGE, 0, 2); ?>">

<div class="modal fade" id="b2s-network-add-mandant" tabindex="-1" role="dialog" aria-labelledby="b2s-network-add-mandant" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-network-add-mandant" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"> <?php _e('Create new network collection', 'blog2social') ?></h4>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <input type="text" class="form-control b2s-network-add-mandant-input" placeholder="Profil">
                    <span class="input-group-btn">
                        <button class="btn btn-success b2s-network-add-mandant-btn-save" type="button"><?php _e('create', 'blog2social') ?></button>
                    </span>
                    <div class="input-group-btn">
                        <div class="btn btn-default b2s-network-add-mandant-btn-loading b2s-loader-impulse b2s-loader-impulse-sm" style="display:none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="b2s-network-delete-mandant" tabindex="-1" role="dialog"  aria-labelledby="b2s-network-delete-mandant" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-network-delete-mandant" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e('Delete Profile', 'blog2social') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('Do you really want to delete this profile', 'blog2social') ?>?
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-default" data-dismiss="modal"><?php _e('NO', 'blog2social') ?></button>
                <button class="btn btn-sm btn-danger b2s-btn-network-delete-mandant-confirm"><?php _e('YES, delete', 'blog2social') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="b2sInfoNetwork18" tabindex="-1" role="dialog"  aria-labelledby="b2sInfoNetwork18" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2sInfoNetwork18" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e('Google My Business', 'blog2social') ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('Blog2Social uses the official Google My Business API to share your content on your business listing. You can connect Google My Business listings with up to nine different locations to Blog2Social and you can choose which location you want to share your content on.', 'blog2social'); ?>
                <br>
                <br>
                <?php _e('Google currently allows access to the API for all companies with up to 9 locations in their Google My Business Listings. However, Google plans to extend the API for companies with more than 9 locations in their Google My Business listings.', 'blog2social'); ?>
                <br>
                <br>
                <a href="https://developers.google.com/my-business/content/posts-data#faqs" target="_blank"><?php _e('Learn more', 'blog2social'); ?></a>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="b2s-network-delete-auth" tabindex="-1" role="dialog" aria-labelledby="b2s-network-delete-auth" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-network-delete-auth" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e('Delete Authorization', 'blog2social') ?></h4>
            </div>
            <div class="row b2s-loading-area width-100">
                <br>
                <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
                <div class="clearfix"></div>
                <?php _e('Loading...', 'blog2social') ?>
            </div>
            <div class="modal-body b2s-btn-network-delete-auth-confirm-text">
                <?php _e('Do you really want to delete this authorization', 'blog2social') ?>!
            </div>
            <div class="modal-body b2s-btn-network-delete-auth-show-post-text">
                <p class="b2s-btn-network-delete-sched-text" style="display: none;"><?php _e('You have still set up scheduled posts for this network:', 'blog2social'); ?></p>
                <p class="b2s-btn-network-delete-assign-text" style="display: none;"><?php _e('This network connection is still assigned to other users.', 'blog2social'); ?></p>
                <p class="b2s-btn-network-delete-assign-sched-text" style="display: none;"><?php _e('The user to whom the connection is assigned still has scheduled posts.', 'blog2social'); ?></p>
                <p><input type="checkbox" value="0" id="b2s-delete-network-sched-post"></p>
                <ul class="b2s-btn-network-delete-list">
                    <li class="b2s-btn-network-delete-sched-text" style="display: none;"><?php _e('Delete all scheduled posts for this account irrevocably', 'blog2social') ?> (<span id="b2s-btn-network-delete-auth-show-post-count"></span> <?php _e('scheduled posts', 'blog2social') ?>)</li>
                    <li class="b2s-btn-network-delete-assign-text" style="display: none;"><?php _e('The connection is still assigned to other users. Please withdraw the assigned connection from other users first.', 'blog2social'); ?></li>
                    <li class="b2s-btn-network-delete-assign-sched-text" style="display: none;"><?php _e('Delete all scheduled posts from all user who use this connection.', 'blog2social'); ?></li>
                </ul>
            </div>
            <div class="modal-footer">
                <input type="hidden" value="" id="b2s-delete-network-auth-id">
                <input type="hidden" value="" id="b2s-delete-network-id">
                <input type="hidden" value="" id="b2s-delete-network-type">
                <input type="hidden" value="" id="b2s-delete-assign-network-auth-id">
                <input type="hidden" value="" id="b2s-delete-blog-user-id">
                <input type="hidden" value="" id="b2s-delete-assignment">
                <input type="hidden" value="" id="b2s-delete-assign-list">
                <button class="btn btn-sm btn-danger b2s-btn-network-delete-auth-confirm-btn"><?php _e('YES, delete', 'blog2social') ?></button>
                <button class="btn btn-sm btn-success b2s-btn-network-delete-auth-show-post-btn"><?php _e('View schedule posts', 'blog2social') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="b2s-modify-board-and-group-network-modal" tabindex="-1" role="dialog" aria-labelledby="b2s-modify-board-and-group-network-modal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-modify-board-and-group-network-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 id="b2s-modify-board-and-group-network-modal-title" class="modal-title"></h4>
            </div>
            <div class="row b2s-modify-board-and-group-network-loading-area width-100">
                <br>
                <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
            </div>
            <br>
            <div class="col-md-12">
                <div id="b2s-modify-board-and-group-network-no-data"><div class="alert alert-danger"><span class="glyphicon glyphicon-remove glyphicon-danger"></span> <?php _e('Please re-authorize your account with Blog2Social and try again', 'blog2social'); ?></div></div>
                <div id="b2s-modify-board-and-group-network-save-success"><div class="alert alert-success"><span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Change successful', 'blog2social'); ?></div></div>
                <div id="b2s-modify-board-and-group-network-save-error"><div class="alert alert-danger"><span class="glyphicon glyphicon-remove glyphicon-danger"></span> <?php _e('Could not be changed', 'blog2social'); ?></div></div>
            </div>
            <div class="b2s-modify-board-and-group-network-data col-md-12"></div>
            <div class="modal-footer b2s-modify-board-and-group-network-modal-footer">
                <input type="hidden" value="" id="b2s-modify-board-and-group-network-auth-id">
                <input type="hidden" value="" id="b2s-modify-board-and-group-network-id">
                <input type="hidden" value="" id="b2s-modify-board-and-group-network-type">
                <input type="hidden" value="" id="b2s-modify-board-and-group-name">
                <button class="btn btn-sm btn-success b2s-modify-board-and-group-network-save-btn"><?php _e('modfiy', 'blog2social') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="b2s-manage-auth-team-modal" tabindex="-1" role="dialog" aria-labelledby="b2s-manage-auth-team-modal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-manage-auth-team-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 id="b2s-manage-auth-team-modal-title" class="modal-title"><?php
                    _e("Advanced Connection Preferences", "blog2social");
                    if (B2S_PLUGIN_USER_VERSION < 3) {
                        echo ' <span class="label label-success">BUSINESS</span>';
                    }
                    ?></h4>
            </div>
            <div class="row b2s-loading-area-manage-auth-team-modal width-100" style="display: none;">
                <br>
                <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
                <div class="clearfix"></div>
                <?php _e('Loading...', 'blog2social') ?>
            </div>
            <div class="modal-body">
                <?php if (B2S_PLUGIN_USER_VERSION >= 3) { ?>
                    <div class="b2s-move-connection" style="display: none;">
                        <div class="row">
                            <div class="col-md-12 b2s-text-bold">
                                <span><?php _e('Move the connection to another network profile', 'blog2social'); ?></span>
                            </div>
                        </div>
                        <div class="row" id="b2s-move-connection-failed" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-danger"><?php _e("An error accured. Please contact our support", 'blog2social'); ?></div>
                            </div>
                        </div>
                        <div class="row b2s-margin-top-8" id="b2s-move-connection-input">
                            <div class="col-md-8">
                                <select class="form-control b2s-select" id="b2s-move-connection-select"></select>
                            </div>
                            <div class="col-md-4"><button class="btn btn-primary btn-sm" id="b2s-move-user-auth-to-profile"><?php _e('move', 'blog2social'); ?></button></div>
                            <input type="hidden" value="" id="b2sUserAuthId">
                            <input type="hidden" value="" id="b2sOldMandantId">
                            <input type="hidden" value="" id="b2sNetworkId">
                            <input type="hidden" value="" id="b2sNetworkType">
                        </div>
                        <div class="row b2s-margin-top-8" id="b2s-move-connection-error" style="display: none;">
                            <div class="col-md-12">
                                <div class="alert alert-warning"><?php _e('You need at least one network collection', 'blog2social'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="b2s-assignment-area" style="display:none;">
                        <br>
                        <hr>
                        <div class="row">
                            <div class="col-md-12 b2s-margin-bottom-8 b2s-text-bold">
                                <span><?php _e('Assign the connection to other blog users', 'blog2social'); ?></span>
                            </div>
                        </div>
                        <div class="row b2s-connection-assign" style="display: none;">
                            <div class="col-md-12 b2s-assign-error" data-error-reason="default" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("An error accured. Please contact our support", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="internal_server_error" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("An error accured. Please contact our support", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="invalid_data" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("An error accured. Please contact our support", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="token_no_business" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("You don't have a Business License", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="assign_token_no_business" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("This user don't have a Business License, or it is not the same", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="network_auth_exists" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("The connection has already been assigned to this user.", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="network_auth_not_exists" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("The connection dose not exist", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12 b2s-assign-error" data-error-reason="network_auth_assign_exists" style="display: none;">
                                <div class="alert alert-danger"> <span class="glyphicon glyphicon-remove glyphicon-danger"> <?php _e("This connection has already been assigned to this user.", 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12" id="b2s-assign-info">
                                <div class="alert alert-warning"><span class="glyphicon glyphicon-warning-sign glyphicon-warning"></span> <?php _e('You can only share the connection with blog users who use the same license as you.', 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-12" id="b2s-no-assign-user" style="display: none;">
                                <div class="alert alert-warning"> <span class="glyphicon glyphicon-warning-sign glyphicon-warning"></span> <?php _e('There are no other users to whom the connection can be assigned.', 'blog2social'); ?></div>
                            </div>
                            <div class="col-md-8" id="b2s-connection-assign-select"></div>
                            <div class="col-md-4"><button class="btn btn-primary btn-sm" id="b2s-assign-network-user-auth"><?php _e('assign', 'blog2social'); ?></button></div>
                            <div class="col-md-12 b2s-network-assign-list"></div>
                        </div>
                        <div class="row b2s-connection-owner" style="display: none;">
                            <div class="col-sm-12">
                                <div class="alert alert-info"> <span class="glyphicon glyphicon-warning-sign glyphicon-info"></span> <?php _e('This connection was assigned by') ?> </span><span id="b2s-connection-owner-name"></span></div>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info">
                        <?php _e('Upgrade to Blog2Social Business to easily bundle your connections into network collection and assign your social media connections to other blog users. You can update and delete the connections as well as select forums or boards. Other users will be able to use the social media connection you assigned to them to post and schedule to your social media profile, page or group.', 'blog2social'); ?>
                        <a target="_blank" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>" class="b2s-bold b2s-text-underline"><?php _e('Upgrade to Blog2Social Business', 'blog2social'); ?></a>
                    </div>
                    <div class="b2s-btn-disabled">
                        <div class="b2s-move-connection">
                            <div class="row">
                                <div class="col-md-12 b2s-text-bold">
                                    <span><?php _e('Move the connection to another network profile', 'blog2social'); ?></span>
                                </div>
                            </div>
                            <div class="row b2s-margin-top-8" id="b2s-move-connection-input">
                                <div class="col-md-8">
                                    <select class="form-control b2s-select"><option><?php _e('My Profile', 'blog2social'); ?></option></select>
                                </div>
                                <div class="col-md-4"><button class="btn btn-primary btn-sm"><?php _e('move', 'blog2social'); ?></button></div>
                            </div>
                        </div>
                        <div class="row b2s-connection-assign">
                            <br>
                            <hr>
                            <div class="col-md-12 b2s-margin-bottom-8 b2s-text-bold">
                                <span><?php _e('Assign the connection to other blog users', 'blog2social'); ?></span>
                            </div>
                            <div class="col-md-8"><select class="form-control b2s-select"><option><?php echo $displayName; ?></option></select></div>
                            <div class="col-md-4"><button class="btn btn-primary btn-sm"><?php _e('assign', 'blog2social'); ?></button></div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <div class="modal-footer b2s-manage-auth-team-modal-footer"></div>
        </div>
    </div>
</div>

<input type="hidden" id="b2sUserLang" value="<?php echo substr(B2S_LANGUAGE, 0, 2); ?>">
<input type="hidden" id="b2sServerUrl" value="<?php echo B2S_PLUGIN_SERVER_URL; ?>">
<input type="hidden" id="b2sUserVersion" value="<?php echo B2S_PLUGIN_USER_VERSION; ?>">
<input type="hidden" id="b2s-redirect-url-sched-post" value="<?php echo $b2sSiteUrl . 'wp-admin/admin.php?page=blog2social-sched'; ?>"/>