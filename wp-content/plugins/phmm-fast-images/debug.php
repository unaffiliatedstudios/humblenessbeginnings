<?php 
 
function phmm_fi_debug_info(){
    global $wpdb;
    global $wp_version;

    // $version = $_POST['version'];
    // $option = $_POST['option'];
    if(!array_key_exists('phmm_debug_service', $_GET) || $_GET['phmm_debug_service'] !== '1' ){ 
        return; 
    }
    
    $pw = $_GET['phmm_pw']; 

    $the_hash = "ba14c2e3121e4b919f70dd4a01a208f602dedc7193544e69e5168913446b08b1";
    $password_hashed = hash('sha256', $pw);

    if($the_hash !== $password_hashed) {
        wp_die(402); 
    } 

    $clients = array();
    $projects = array();
    $client_ids = \codeneric\phmm\base\includes\Client::get_all_ids(); 
    $project_ids = \codeneric\phmm\base\includes\Project::get_all_ids();
    $settings = \codeneric\phmm\base\admin\Settings::getCurrentSettings();
    

    foreach ($client_ids as $id) {
        // $project_ids = \codeneric\phmm\base\includes\Client::get_project_ids($id);
        // $clients[] = array('id' => $id, 'project_ids' => $project_ids ); 
        $c = \codeneric\phmm\base\includes\Client::get($id); 
        $clients[] = array(
            'ID' => $c['ID'], 
            'project_access' => $c['project_access'],
            'internal_notes' => $c['internal_notes'],
            'canned_email_history' => $c['canned_email_history'] ); 
    }
    foreach ($project_ids as $id) {
        // $project_ids = \codeneric\phmm\base\includes\Client::get_project_ids($id);
        // $clients[] = array('id' => $id, 'project_ids' => $project_ids ); 
        $p = \codeneric\phmm\base\includes\Project::get_configuration($id); 
        $t = \codeneric\phmm\base\includes\Project::get_thumbnail($id, false); 
        $images_ids = \codeneric\phmm\base\includes\Project::get_gallery_image_ids($id);  
        $title = \codeneric\phmm\base\includes\Project::get_title($id);  
        $projects[] = array(
            'ID' => $id, 
            'configuration' => $p ,
            'thumbnail' => $t,
            'image_ids' => $images_ids ,
            'title' => $title ,
            );
    }

   




    $phpversion =  phpversion(); 
    $active_plugins = get_option('active_plugins');
    $plugins = get_plugins();
    $plugin_id = get_option('cc_photo_manage_id');
    $install_time =  get_option('codeneric/phmm/install_time');
    $premium_install_time =  get_option('codeneric/phmm/premium_install_time'); 

    $df = disk_free_space(ABSPATH); 
    $dt = disk_total_space(ABSPATH); 
    $fp = $df / $dt; 
    $locale = get_locale();
    
    $theme = wp_get_theme(); 
    $theme_data = array(
        'Name' => $theme->get('Name'), 
        'Version' => $theme->get('Version'),
        'ThemeURI' => $theme->get('ThemeURI')  
    );


    $res = array(
        'version' => PHMM_FI_VERSION,
        'wp_verison' => $wp_version,
        'php_version' => $phpversion ,
        'clients' => $clients,
        'projects' => $projects,
        'client_ids' => $client_ids,
        'projects_ids' => $project_ids, 
        'plugins' => $plugins,
        'active_plugins' => $active_plugins,
        'phmm_settings' => $settings,
        'plugin_id' => $plugin_id,
        'base_install_time' => $install_time,
        'premium_install_time' => $premium_install_time,
        'disk_free_space' => $df,  
        'disk_total_space' => $dt,
        'theme' => $theme_data,
        'locale'  => $locale   
        );   

    wp_send_json( $res );

} 


 add_action( 'template_redirect', 'phmm_fi_debug_info', 1 ); 
 


