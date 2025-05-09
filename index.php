<?php 
/*
Plugin Name: ETC Multisite Dashboard Announcements
Plugin URI:  https://github.com/
Description: Add notifications to the dashboard with the "Announcement" category on root blog posts
Version:     1.1.0
Author:      Tom Woodward
Author URI:  https://bionicteaching.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: etc-custom

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


function etc_custom_dashboard_scripts() {                           
    $version= '1.0'; 
    wp_enqueue_style( 'etc-main-css', plugin_dir_url( __FILE__) . 'css/etc-main.css');
}
add_action('admin_enqueue_scripts', 'etc_custom_dashboard_scripts');


function etc_custom_dashboard_widgets() {
  global $wp_meta_boxes;
  add_meta_box('custom_announcements_widget', 'Latest Announcements', 'etc_custom_announcements', 'dashboard', 'normal', 'high');
}
add_action('wp_dashboard_setup', 'etc_custom_dashboard_widgets');


//--------------------------------------------------------------
// Dashboard metabox 
//--------------------------------------------------------------

function etc_custom_announcements() {

  switch_to_blog(1); // Switch to Site 1
  $site_title = get_bloginfo('name'); // Get the site title
  $site_tagline = get_bloginfo('description');
  $site_icon_url = get_site_icon_url(50, '', 1 ); // Get the site tagline
  restore_current_blog(); // Restore the original site
  ?>

  <div class="banner">
    <div class="content-container">
      <div style="display: flex; gap: 30px; align-items: center;">
        <?php if($site_icon_url){ ?>
        <img class="multisite-logo" src=" <?php echo $site_icon_url ?>" style="height: 100px; aspect-ratio: 1/1">
        <?php } ?>
        <div>
          <h2><?php echo $site_title; ?></h2>
          <subtitle><?php echo $site_tagline; ?></subtitle>
        </div>
      </div>
    </div>
  </div>
  <div class="content-container">
    <div class="row">
      <!--<div style="display:flex; flex-direciton: row; gap: 20px;"><img class="multisite-logo" src=" <?php //echo get_site_icon_url(50, '', 1 ); ?>" style="height: 50px; width: 50px"><h2>Latest Announcements</h2></div>-->
      <div class="announcements"> <?php echo get_announcements(); ?> </div>
      <div class="getting-started"> <?php echo get_custom_documentation(); ?> </div>
    </div>
  </div>
  <?php
}


//--------------------------------------------------------------
// Announcements Setup
//--------------------------------------------------------------

function get_announcements(){

  $announcements = "<h3>Latest Announcements</h3>";

  // WP_Query arguments
  switch_to_blog(1);//switch to the home blog but you could put another source URL here

  $args = array(
    'post_type'              => array( 'post' ),
    'post_status'            => array( 'public' ),
    'order'                  => 'DEC',
    'orderby'                => 'date',
    'category_name'          => 'Announcement',// using the category support slug here
    'posts_per_page'         => 5
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
      $date = get_the_date('F j, Y');
      
      // Create tag classes string
      $tag_classes = '';
      if ($tags) {
        foreach ($tags as $tag) {
          $tag_classes .= ' ' . $tag->slug;
        }
      }
     
      // Echo output with tag classes added
      $announcements .=  "<div class='post-item {$tag_classes}'>
                            <div class='post-title'>
                              <h4>{$title}</h4>
                              
                            </div>
                            <div class='post-inner'>
                            <p class='date'>$date</p>
                              <p>{$excerpt}</p>
                              <p><a href='{$link}'>Read More</a></p>
                            </div>
                          </div>";
      }
    } else {
      // no posts found
    }

  // Restore original Post Data
  wp_reset_postdata();
  restore_current_blog();
  
  return $announcements;

}


//--------------------------------------------------------------
// Documentation Setup
//--------------------------------------------------------------

// register custom field for getting started options page
add_action( 'acf/include_fields', function() {
	
  if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

  $site_id = get_current_blog_id(); 
  
  // Only register if we're on site ID 1
  if ($site_id != 1) {
      return;
  }


	acf_add_local_field_group( array(
	'key' => 'group_681a834183d8a',
	'title' => 'Documentation Editor',
	'fields' => array(
		array(
			'key' => 'field_681a834164b92',
			'label' => '',
			'name' => 'documentation_content',
			'aria-label' => '',
			'type' => 'wysiwyg',
			'instructions' => 'Fill out the textbox below with the content you want to display in the "Getting Started" box on all user dashboards.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'allow_in_bindings' => 0,
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
			'delay' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'dashboard-documentation',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 1,
) );


} );

// register options page for getting started
add_action( 'acf/init', function() {

  $site_id = get_current_blog_id(); 

  // Only register if we're on site ID 1
  if ($site_id != 1) {
      return;
  }

    acf_add_options_page( array(
      'page_title' => 'Getting Started Documentation',
      'menu_slug' => 'dashboard-documentation',
      'position' => '',
      'redirect' => false,
      'menu_icon' => array(
        'type' => 'dashicons',
        'value' => 'dashicons-text-page',
      ),
      'icon_url' => 'dashicons-text-page',
    ) );

} );


function get_custom_documentation(){

  $acf_option_name = 'options_documentation_content';
    
  // Get option directly from blog 1
  $documentation_content = get_blog_option(1, $acf_option_name);
  
  return "<h3>Getting Started</h3><div class=''><p>" . $documentation_content . "</p></div>"; 

}

//--------------------------------------------------------------
// Extras and QoL Items
//--------------------------------------------------------------

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

 
