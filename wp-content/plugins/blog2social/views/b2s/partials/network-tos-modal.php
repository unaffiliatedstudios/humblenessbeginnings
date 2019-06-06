<!--Info TOS Facebook 072018 - Facebook Instant Sharing by manuell-->
<?php $b2sNetworkTosAccept = get_option('B2S_PLUGIN_NETWORK_TOS_ACCEPT_072018_USER_' . B2S_PLUGIN_BLOG_USER_ID); ?>
<input type="hidden" id="b2sNetworkTosAccept" value="<?php echo (($b2sNetworkTosAccept !== false || $showPrivacyPolicy !== false) ? 1 : 0); ?>">
<div class="modal fade" id="b2sNetworkTosAcceptModal" tabindex="-1" role="dialog" aria-labelledby="b2sNetworkTosAcceptModal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><?php _e('Posting on Facebook Profiles – Introducing Facebook Instant Sharing', 'blog2social'); ?></h4>
            </div>
            <div class="modal-body">
                <?php _e('As part of recent updates to the Facebook Platform Policies, Facebook introduced significant changes to the APIs that third-party programs, such as social media marketing tools, are using to access the platform.', 'blog2social') ?>
                <br>
                <br>
                <?php _e('Due to Facebook API changes, starting on 1st August 2018, access to personal Facebook Profiles has been severely restricted for all social media tools. Automated posting on personal Facebook Profiles is no longer allowed with any social media tool or app.', 'blog2social') ?>
                <a href="<?php echo B2S_Tools::getSupportLink('network_tos_faq_news_072018'); ?>" target="_blank"><?php _e('read more', 'blog2social'); ?></a>
                <br>
                <br>
                <strong><?php _e('What does this mean for your Social Media Marketing?', 'blog2social'); ?></strong>
                <br>
                <?php _e('Sharing your posts on your Facebook Profile will still be possible with Blog2Social! To help you keep sharing content with your followers, Blog2Social is now introducing Instant Sharing for Facebook.', 'blog2social'); ?>
                <br>
                <?php _e('Instant Sharing will not only let you share content on your Facebook Profile. You will also be able to share in Groups, Events and more!', 'blog2social'); ?>   
                <br>
                <a href="<?php echo B2S_Tools::getSupportLink('network_tos_faq_072018'); ?>" target="_blank"><?php _e('Learn how to use all the new features of Facebook Instant Sharing including @handles, emotions, and more customizing features!', 'blog2social'); ?></a>
                <br>
                <br>
                <strong<?php _e('Please note:', 'blog2social'); ?></strong>
                <br>
                <?php _e('Changes to the API currently only affect personal Facebook Profiles. Sharing your posts automatically on your Facebook Pages will also still be possible with social media tools. And of course, Blog2Social will continue to support automated posting on Facebook Pages.', 'blog2social'); ?>
                <br>
                <?php _e('If you are using your personal Facebook Profile for business, promotion, or publishing purposes, you might also consider converting your personal Facebook Profile to a Facebook Page.', 'blog2social'); ?>
                <br>
                <a href="<?php echo B2S_Tools::getSupportLink('network_tos_faq_2_072018'); ?>" target="_blank"><?php _e('Learn how to convert your Facebook Profile to a Facebook Page', 'blog2social'); ?></a>
                <br>
                <br>
                <div class="clearfix"></div>
                <div class="text-center">
                    <button type="button" id="b2s-network-tos-accept-btn" class="btn btn-primary"><?php _e("I understand the Facebook changes", "blog2social"); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>