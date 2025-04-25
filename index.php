<?php 
/*
Plugin Name: ETC Multisite Dashboard Announcements
Plugin URI:  https://github.com/
Description: Add notifications to the dashboard with the "Announcement" category on root blog posts
Version:     1.0
Author:      Tom Woodward
Author URI:  https://bionicteaching.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: etc-custom

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action('admin_enqueue_scripts', 'etc_custom_dashboard_scripts');

function etc_custom_dashboard_scripts() {                           
    $version= '1.0'; 
    wp_enqueue_style( 'etc-main-css', plugin_dir_url( __FILE__) . 'css/etc-main.css');
}


add_action('wp_dashboard_setup', 'etc_custom_dashboard_widgets');
 
function etc_custom_dashboard_widgets() {
  global $wp_meta_boxes;
  wp_add_dashboard_widget('custom_etc_widget', '<img class="chicken-alert" " src="'.plugin_dir_url( __FILE__ ).'/imgs/etc.webp"><h1>Open ETC Announcements</h1>', 'etc_custom_dashboard_posts');
  }

function etc_custom_dashboard_posts() {
    echo '<p>Important messages from ETC will appear below.</p>';
    //echo network_home_url();
    // WP_Query arguments
    switch_to_blog(1);//switch to the home blog but you could put another source URL here
    $args = array(
      'post_type'              => array( 'post' ),
      'post_status'            => array( 'public' ),
      'order'                  => 'ASC',
      'orderby'                => 'date',
      'category_name'          => 'Announcement',// using the category support slug here
    );

    // The Query
    $query = new WP_Query( $args );

    // The Loop
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $post = $query->the_post();
        $link =  get_the_permalink();
        $title = get_the_title();
        $excerpt = get_the_excerpt();
        $tags = get_the_tags();
        
        // Create tag classes string
        $tag_classes = '';
        if ($tags) {
          foreach ($tags as $tag) {
            $tag_classes .= ' ' . $tag->slug;
          }
        }
       
        // Echo output with tag classes added
        echo "
              <div class='post-item {$tag_classes}'>
                <h2><a href='{$link}'>{$title}</a></h2>
                <p>{$excerpt}</p>
              </div>";
        }
      } else {
        // no posts found
      }
  // Restore original Post Data
  wp_reset_postdata();
  restore_current_blog();
}
/*
  Disable Default Dashboard Widgets
  @ https://digwp.com/2014/02/disable-default-dashboard-widgets/
*/
function disable_default_dashboard_widgets() {
  global $wp_meta_boxes;
  // wp..
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);
  unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
  unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
  // bbpress
  unset($wp_meta_boxes['dashboard']['normal']['core']['bbp-dashboard-right-now']);
  // yoast seo
  unset($wp_meta_boxes['dashboard']['normal']['core']['yoast_db_widget']);
  // gravity forms
  unset($wp_meta_boxes['dashboard']['normal']['core']['rg_forms_dashboard']);
}
add_action('wp_dashboard_setup', 'disable_default_dashboard_widgets', 999);


//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
