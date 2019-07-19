<?php

class B2S_Tools {

    public static function showNotice() {
        return (defined("B2S_PLUGIN_NOTICE") || !defined("B2S_PLUGIN_TOKEN")) ? true : false;
    }

    public static function getToken($data = array()) {
        return B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $data, 30);
    }

    public static function setUserDetails() {
        if (defined("B2S_PLUGIN_TOKEN")) {
            delete_option('B2S_PLUGIN_USER_VERSION_' . B2S_PLUGIN_BLOG_USER_ID);
            delete_option('B2S_PLUGIN_PRIVACY_POLICY_USER_ACCEPT_' . B2S_PLUGIN_BLOG_USER_ID);
            $version = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, array('action' => 'getUserDetails', 'token' => B2S_PLUGIN_TOKEN, 'version' => B2S_PLUGIN_VERSION), 30));
            $tokenInfo['B2S_PLUGIN_USER_VERSION'] = (isset($version->version) ? $version->version : 0);
            $tokenInfo['B2S_PLUGIN_VERSION'] = B2S_PLUGIN_VERSION;
            if (!defined("B2S_PLUGIN_USER_VERSION")) {
                define('B2S_PLUGIN_USER_VERSION', $tokenInfo['B2S_PLUGIN_USER_VERSION']);
            }
            if (isset($version->trial) && $version->trial != "") {
                $tokenInfo['B2S_PLUGIN_TRAIL_END'] = $version->trial;

                if (!defined("B2S_PLUGIN_TRAIL_END")) {
                    define('B2S_PLUGIN_TRAIL_END', $tokenInfo['B2S_PLUGIN_TRAIL_END']);
                }
            }
            if (!isset($version->version)) {
                define('B2S_PLUGIN_NOTICE', 'CONNECTION');
            } else {
                $tokenInfo['B2S_PLUGIN_USER_VERSION_NEXT_REQUEST'] = time() + 3600;
                update_option('B2S_PLUGIN_USER_VERSION_' . B2S_PLUGIN_BLOG_USER_ID, $tokenInfo, false);
            }

            if (isset($version->show_privacy_policy) && !empty($version->show_privacy_policy)) {
                update_option('B2S_PLUGIN_PRIVACY_POLICY_USER_ACCEPT_' . B2S_PLUGIN_BLOG_USER_ID, $version->show_privacy_policy, false);
            }
        }
    }

    public static function checkUserBlogUrl() {
        $check = false;
        $blogUrl = get_option('home');
        global $wpdb;
        $sql = "SELECT token,state_url FROM b2s_user WHERE blog_user_id = %d";
        $result = $wpdb->get_results($wpdb->prepare($sql, B2S_PLUGIN_BLOG_USER_ID));
        if (is_array($result) && !empty($result) && isset($result[0]->token)) {
            if (isset($result[0]->state_url) && (int) $result[0]->state_url != 1) {
                $checkBlogUrl = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, array('action' => 'getBlogUrl', 'token' => $result[0]->token, 'blog_url' => strtolower($blogUrl), 'state_url' => (int) $result[0]->state_url)));
                if (isset($checkBlogUrl->result) && (int) $checkBlogUrl->result == 1) {
                    if (isset($checkBlogUrl->update) && (int) $checkBlogUrl->update == 1) {
                        $wpdb->update('b2s_user', array('state_url' => "1"), array('blog_user_id' => B2S_PLUGIN_BLOG_USER_ID), array('%d'), array('%d'));
                    }
                    $check = true;
                }
            } else {
                $check = true;
            }
        }
        define("B2S_PLUGIN_NOTICE_SITE_URL", $check);
    }

    public static function getRandomBestTimeSettings() {
        $lang = substr(B2S_LANGUAGE, 0, 2);
        $defaultTimes = unserialize(B2S_PLUGIN_SCHED_DEFAULT_TIMES);
        $allowPage = unserialize(B2S_PLUGIN_NETWORK_ALLOW_PAGE);
        $allowGroup = unserialize(B2S_PLUGIN_NETWORK_ALLOW_GROUP);
        $userTimes = array();
        if (is_array($defaultTimes) && !empty($defaultTimes)) {
            $slug = ($lang == 'en') ? 'h:i A' : 'H:i';
            foreach ($defaultTimes as $k => $v) {
                if (is_array($v) && !empty($v)) {
                    $endProfile = $v[1];
                    $getTimeForPage = in_array($k, $allowPage) ? true : false;
                    $getTimeForGroup = in_array($k, $allowGroup) ? true : false;
                    if ($getTimeForPage) {
                        $endProfile = date("H:i", strtotime('-30 minutes', strtotime($endProfile . ':00')));   //-30min
                    }
                    if ($getTimeForGroup) {
                        $endProfile = date("H:i", strtotime('-30 minutes', strtotime($endProfile . ':00')));   //-30min
                    }
                    $endProfile = (strpos($endProfile, ':') === false) ? $endProfile . ':00' : $endProfile;
                    $startProfle = (strpos($v[0], ':') === false) ? $v[0] . ':00' : $v[0];
                    $dateTime = date('Y-m-d ' . B2S_Util::getRandomTime($startProfle, $endProfile) . ':00');
                    //Profile
                    $userTimes[$k][0] = date($slug, strtotime($dateTime));
                    //Page
                    $dateTime = ($getTimeForPage) ? strtotime('+30 minutes', strtotime($dateTime)) : strtotime($dateTime);
                    $userTimes[$k][1] = ($getTimeForPage) ? date($slug, $dateTime) : "";
                    //Group
                    $dateTime = strtotime('+30 minutes', $dateTime);
                    $userTimes[$k][2] = ($getTimeForGroup) ? date($slug, $dateTime) : "";
                }
            }
        }
        return $userTimes;
    }

    public static function getSupportLink($type = 'howto') {
        $lang = substr(B2S_LANGUAGE, 0, 2);
        if ($type == 'howto') {
            return 'https://blog2social.com/docs/' . (($lang == 'en') ? 'blog2social-guide-step-by-step-en.pdf' : 'step-by-step-guide-zu-blog2social.pdf');
        }
        if ($type == 'faq') {
            return 'https://service.blog2social.com/support?url=' . get_option('home') . '&token=' . B2S_PLUGIN_TOKEN;
        }
        if ($type == 'faq_direct') {
            return 'https://www.blog2social.com/' . (($lang == 'en') ? 'en' : 'de') . "/faq/";
        }
        if ($type == 'affiliate') {
            $affiliateId = self::getAffiliateId();
            return 'https://service.blog2social.com/' . (((int) $affiliateId != 0) ? '?aid=' . $affiliateId : '');
        }
        if ($type == 'feature') {
            return 'https://blog2social.com/' . (($lang == 'en') ? 'en/plugin/wordpress/premium-trial/' : 'de/plugin/wordpress/premium-testen/');
        }
        if ($type == 'trial') {
            return 'https://service.blog2social.com/' . (($lang == 'en') ? 'en/trial' : 'de/trial');
        }
        if ($type == 'contact') {
            return 'https://service.blog2social.com/' . (($lang == 'en') ? 'en/trial' : 'de/trial');
        }
        if ($type == 'term') {
            return 'https://www.blog2social.com/' . (($lang == 'en') ? 'en/terms' : 'de/agb');
        }
        if ($type == 'privacy_policy') {
            return 'https://www.adenion.de/' . (($lang == 'en') ? 'privacy-policy' : 'datenschutz');
        }
        if ($type == 'userTimeSettings') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=artikel&cat=5&id=32&artlang=en' : 'https://www.blog2social.com/de/faq/index.php?action=artikel&cat=5&id=43&artlang=de';
        }
        //TOS Twitter 032018
        //BTN: More information Twitter
        if ($type == 'network_tos_faq_032018') {
            return (($lang == 'en') ? 'https://www.blog2social.com/en/faq/content/3/127/en/twitter-terms-of-service-update-february-2018-common-questions.html' : 'https://www.blog2social.com/de/faq/content/3/127/de/twitter-aenderung-der-allgemeinen-geschaeftsbedingungen-update-februar-2018-haeufig-gestellte-fragen.html');
        }
        //BTN: Learn more about this Twitter
        if ($type == 'network_tos_blog_032018') {
            return (($lang == 'en') ? 'https://www.blog2social.com/en/blog/how-new-twitter-rules-impact-your-social-media-marketing' : 'https://www.blog2social.com/de/blog/neue-twitter-regeln-social-media-marketing');
        }
        //TOS Facebook 072018
        //BTN: read more  Facebook
        if ($type == 'network_tos_faq_news_072018') {
            return (($lang == 'en') ? 'https://www.blog2social.com/en/faq/news/39/en/version-491-_-facebook-profile-changes-_-introducing-facebook-instant-sharing.html' : 'https://www.blog2social.com/de/faq/news/35/de/version-491-_-facebook_profil_aenderungen-_-neue-funktion-facebook-instant-sharing.html');
        }
        //TOS Xing 082018
        //BTN: read more Xing
        if ($type == 'network_tos_blog_082018') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/content/3/137/en/how-to-successfully-post-to-xing-groups.html' : 'https://www.blog2social.com/de/faq/content/3/135/de/so-gelingt-ihnen-das-erfolgreiche-teilen-in-xing_gruppen.html';
        }
        //BTN: read more Xing
        if ($type == 'network_tos_blog_032019') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=artikel&cat=2&id=146&artlang=en' : 'https://www.blog2social.com/de/faq/index.php?action=artikel&cat=2&id=145&artlang=de';
        }
        if ($type == 'system_requirements') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=artikel&cat=1&id=58&artlang=en' : 'https://www.blog2social.com/de/faq/index.php?action=artikel&cat=1&id=63&artlang=de';
        }
        if ($type == 'hotlink_protection') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=artikel&cat=9&id=80&artlang=en' : 'https://www.blog2social.com/de/faq/index.php?action=artikel&cat=9&id=84&artlang=de';
        }
        if ($type == 'faq_installation') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=1' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=1';
        }
        if ($type == 'faq_network') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=2' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=2';
        }
        if ($type == 'faq_sharing') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=3' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=3';
        }
        if ($type == 'faq_customize') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=4' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=4';
        }
        if ($type == 'faq_scheduling') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=5' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=5';
        }
        if ($type == 'faq_licence') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=7' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=7';
        }
        if ($type == 'faq_troubleshooting') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=9' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=9';
        }
        if ($type == 'faq_settings') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/faq/index.php?action=show&cat=11' : 'https://www.blog2social.com/de/faq/index.php?action=show&cat=11';
        }
        if ($type == 'browser_extension') {
            return ($lang == 'en') ? 'https://www.blog2social.com/en/webapp/extension/' : 'https://www.blog2social.com/de/webapp/extension/';
        }
        if ($type == 'xing_auto_posting') {
            return ($lang == 'en') ? 'https://faq.xing.com/en/groups/code-conduct-group-members' : 'https://faq.xing.com/de/gruppen/verhaltenskodex-f%C3%BCr-gruppenmitglieder';
        }
    }

    public static function getAffiliateId() {
        return (defined("B2S_PLUGIN_AFFILIATE_ID")) ? B2S_PLUGIN_AFFILIATE_ID : 0;
    }

    public static function getTokenById($user_id = 0) {
        if ($user_id == 0) {
            $user_id = get_current_user_id();
        }
        $user = get_user_by('id', $user_id);
        global $wpdb;
        $sql = $wpdb->prepare("SELECT token FROM `b2s_user` WHERE `blog_user_id` = %d", $user->data->ID);
        $userExist = $wpdb->get_row($sql);
        if (empty($userExist) || !isset($userExist->token)) {
            $postData = array('action' => 'getToken', 'blog_user_id' => $user->data->ID, 'blog_url' => get_option('home'), 'email' => $user->data->user_email);
            $result = json_decode(B2S_Tools::getToken($postData));
            if (isset($result->result) && (int) $result->result == 1 && isset($result->token)) {
                $state_url = (isset($result->state_url)) ? (int) $result->state_url : 0;
                $sqlInsertToken = $wpdb->prepare("INSERT INTO `b2s_user` (`token`, `blog_user_id`,`register_date`,`state_url`) VALUES (%s,%d,%s,%d);", $result->token, (int) $user->data->ID, date('Y-m-d H:i:s'), $state_url);
                $wpdb->query($sqlInsertToken);
                return $result->token;
            } else {
                return false;
            }
        } else {
            return $userExist->token;
        }
    }

    public static function searchUser($search = "", $selectId = 0) {
        $getUser = new WP_User_Query(array(
            'search' => '*' . esc_attr($search) . '*',
            'search_columns' => array(
                'display_name',
            ),
        ));
        $userResult = $getUser->get_results();
        $options = '<option value="0"></option>';
        if (!empty($userResult) && is_array($userResult)) {
            foreach ($userResult as $k => $user) {
                if (isset($user->data->ID) && isset($user->data->display_name) && isset($user->data->user_email)) {
                    $userDetails = get_option('B2S_PLUGIN_USER_VERSION_' . $user->data->ID);
                    $ver = "";
                    if (isset($userDetails['B2S_PLUGIN_USER_VERSION']) && (int) $userDetails['B2S_PLUGIN_USER_VERSION'] > 0) {
                        $ver = " (Blog2Social " . __('License', 'blog2social') . ": " . unserialize(B2S_PLUGIN_VERSION_TYPE)[$userDetails['B2S_PLUGIN_USER_VERSION']] . ")";
                    }
                    $options .= '<option value="' . $user->data->ID . '" ' . (($user->data->ID == $selectId) ? "selected" : "") . '>' . $user->data->display_name . " (" . $user->data->user_email . ")" . $ver . '</option>';
                }
            }
        }
        return $options;
    }

}
