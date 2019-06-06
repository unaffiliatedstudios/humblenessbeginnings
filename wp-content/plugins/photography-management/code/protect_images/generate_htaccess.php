<?php
require_once ($GLOBALS["HACKLIB_ROOT"]);
function Photography_Management_Base_Generate_Htaccess(
  $htaccess_path,
  $new_site_url = null
) {
  $protect_url =
    \hacklib_cast_as_boolean(is_null($new_site_url))
      ? get_site_url()
      : $new_site_url;
  $htaccess =
    "RewriteEngine On".
    PHP_EOL.
    "RewriteCond %{REQUEST_URI} !protect.php".
    PHP_EOL.
    "RewriteCond %{QUERY_STRING} ^(.*)".
    PHP_EOL.
    "RewriteRule ^(.+)$ ".
    $protect_url.
    "/?codeneric_load_image=1&%1&f=$1 [L,NC]";
  return insert_with_markers($htaccess_path, "CODENERIC PHMM", $htaccess);
}
