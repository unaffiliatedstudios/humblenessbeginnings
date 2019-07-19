<?php

function _pfi_post_event($event) {
  $config = \codeneric\phmm\Configuration::get();

  return wp_remote_post(
    $config['wpps_url'].'/event/2.0/',
    array(
      'headers' => array(
        'Content-Type' => 'application/json; charset=utf-8'
      ),
      'body' => json_encode($event),
      'method' => 'POST',
      'data_format' => 'body'
    )
  ); 
}


function pfi_send_event($type, $data, $version=1){
     $site_url = get_site_url();

    $event =  array(
    "site_url" => $site_url,
    "type" => $type,
    "context" => "phmm",
    "version" => $version, 
    "data" => $data 
  ); 
  _pfi_post_event($event); 
} 

function pfi_register() {
  $ajaxurl = admin_url('admin-ajax.php');
  $plugin_id = get_option('cc_photo_manage_id');

  pfi_send_event("register", array("ajaxurl" => $ajaxurl, "plugin_id" => $plugin_id,
    "phmm_fi_version" => PHMM_FI_VERSION  ), 2 );

} 
