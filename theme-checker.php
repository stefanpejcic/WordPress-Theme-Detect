<?php
/**
 * Plugin Name: WordPress Theme Checker
 * Description: Allows you to use a shortcode to display a form where users can enter a WordPress website URL and check the active theme name on that website, including information from the WordPress.org repository if available.
 * Version: 1.0
 * Author: Stefan Pejcic
 */

function theme_checker_shortcode($atts) {
  $output = '<form method="post" action="">';
  $output .= '<label for="url">Enter WordPress website URL:</label>';
  $output .= '<input type="text" name="url" id="url" value="" required>';
  $output .= '<input type="submit" value="Check Theme">';
  $output .= '</form>';

  if (isset($_POST['url'])) {
    $url = esc_url($_POST['url']);

    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
      $output .= '<p>Unable to retrieve WordPress theme information.</p>';
    } else {
      $html = wp_remote_retrieve_body($response);
      if (stripos($html, 'wp-content/themes/') !== false) {
        preg_match('#wp-content/themes/([^/]+)#', $html, $matches);
        $theme = $matches[1];
        $output .= '<p>The active theme on this WordPress website is: ' . $theme . '</p>';

        $theme_info = wp_remote_get('https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . $theme);
        if (!is_wp_error($theme_info)) {
          $theme_info = json_decode(wp_remote_retrieve_body($theme_info));
          if (isset($theme_info->screenshot_url)) {
            $output .= '<p><img src="' . $theme_info->screenshot_url . '" alt="Screenshot"></p>';
          }
          if (isset($theme_info->name)) {
            $output .= '<p><strong>Name:</strong> ' . $theme_info->name . '</p>';
          }
          if (isset($theme_info->version)) {
            $output .= '<p><strong>Version:</strong> ' . $theme_info->version . '</p>';
          }
          if (isset($theme_info->author)) {
            $output .= '<p><strong>Author:</strong> ' . $theme_info->author . '</p>';
          }
          if (isset($theme_info->homepage)) {
            $output .= '<p><strong>Homepage:</strong> <a href="' . $theme_info->homepage . '" target="_blank">' . $theme_info->homepage . '</a></p>';
          }
          if (isset($theme_info->description)) {
            $output .= '<p><strong>Description:</strong> ' . $theme_info->description . '</p>';
          }
        }
      } else {
        $output .= '<p>Unable to retrieve theme information. Please check the URL and try again.</p>';
      }
    }
  }

  return $output;
}
add_shortcode('theme-checker', 'theme_checker_shortcode');
