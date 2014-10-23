<?php
/**
 * @package WpRedirect_Plugin
 * @version 1.0
 */
/*
Plugin Name: WP Redirect Plugin
Plugin URI: http://victorholt.me/
Description: This plugin handles performing redirects from a given redirect.csv file.
Author: Victor Holt
Author URI: http://www.victorholt.me/
Version: 1.0
*/

// Initialize the wp-redirect plugin.
function wpredirect_plugin_init() {
  // Make sure we're not checking anything if we're in the admin.
  if (!preg_match('/^\/wp-admin/', $_SERVER['REQUEST_URI'])) {

    // Ensure the file exists and read the file.
    if (file_exists("./wp-content/plugins/wp-redirect/redirects.csv")) {
      $file = fopen("./wp-content/plugins/wp-redirect/redirects.csv","r");
      $redirects = array();

      while(!feof($file))
      {
        $row = fgetcsv($file);
        if (count($row) == 2) {
          $redirects[$row[0]] = $row[1];
          $cleanUrl = $row[0];

          // If this has an IdS version check if we have a non-IdS version.
          if (preg_match('/IdS=.*?\&/', $row[0])) {
            $cleanUrl = preg_replace('/IdS=.*?\&/', '', $cleanUrl);
            if (empty($redirects[$cleanUrl])) {
              $redirects[$cleanUrl] = $row[1];
            }
          }

          // Remove the =~
          if (preg_match('/\&~=$/', $row[0])) {
            $cleanUrl = preg_replace('/&~=$/', '', $cleanUrl);
            if (empty($redirects[$cleanUrl])) {
              $redirects[$cleanUrl] = $row[1];
            }
          }

          // Check the lower-case version of the clean url.
          $parts = explode('?', $cleanUrl);
          if (count($parts) == 2) {
            $cleanUrl = strtolower($parts[0]) . '?' . $parts[1];
            if (empty($redirects[$cleanUrl])) {
              $redirects[$cleanUrl] = $row[1];
            }
          }
        }
      }

      fclose($file);

      // Check if the uri is one of the redirects.
      if (!empty($redirects[$_SERVER['REQUEST_URI']])) {
        header('Location: ' . $redirects[$_SERVER['REQUEST_URI']], true, 301);
        exit;
      }
    }

    // For testing purposes
    /*if ($_SERVER['REQUEST_URI'] == '/wpredirect_redirect_test') {
      echo "<!-- \n";
      print_r($redirects);
      echo "\n -->\n";
      exit;
    }*/

  }
}

// Now we set that function up to execute when the admin_notices action is called
add_action('init', 'wpredirect_plugin_init');

?>
