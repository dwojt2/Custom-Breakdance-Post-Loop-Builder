<?php
/**
 * Plugin Name: Custom Breakdance Post Loop Builder
 * Description: A plugin that creates a custom post loop builder with search functionality and lazy loading.
 * Version: 1.0.0
 * Author: Dominik Wojtysiak
 * Author URI: https://wojtysiak.one
 */


// Enqueue required scripts and styles
function cwpai_enqueue_scripts() {
  // Enqueue jQuery if not already enqueued
  wp_enqueue_script('jquery');
  
  // Enqueue script for post loop builder
  wp_enqueue_script('breakdance-post-loop-builder', plugin_dir_url(__FILE__) . 'js/post-loop-builder.js', array('jquery'), '1.0', true);
  
  // Enqueue styles for post loop builder
  wp_enqueue_style('breakdance-post-loop-builder', plugin_dir_url(__FILE__) . 'css/post-loop-builder.css', array(), '1.0');
}
add_action('wp_enqueue_scripts', 'cwpai_enqueue_scripts');

// Register the Breakdance element shortcode
function cwpai_register_breakdance_element_shortcode($atts) {
  ob_start();
  ?>
  <div class="breakdance-element">
    <input type="text" id="breakdance-search-input" placeholder="Enter your search query">
    <button id="breakdance-search-button">Search</button>
    <div id="breakdance-loader" style="display: none;">
      <img src="<?php echo plugin_dir_url(__FILE__); ?>images/loader.gif" alt="Loading">
    </div>
    <div id="breakdance-results" style="display: none;"></div>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('breakdance-element', 'cwpai_register_breakdance_element_shortcode');

// Add AJAX handler for performing the search
function cwpai_breakdance_search() {
  // Get the search query from the AJAX request
  $search_query = $_POST['search_query'];

  // Perform the search and get the results
  $args = array(
    'post_type' => 'post',
    's' => $search_query,
    'posts_per_page' => -1 // Show all matching posts
  );
  $query = new WP_Query($args);

  // Output the search results
  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();
      // Display the post content or any other desired output
    }
  } else {
    // Display message for no search results
  }

  // Reset the post data
  wp_reset_postdata();

  // End the AJAX request
  wp_die();
}
add_action('wp_ajax_cwpai_breakdance_search', 'cwpai_breakdance_search');
add_action('wp_ajax_nopriv_cwpai_breakdance_search', 'cwpai_breakdance_search');

// Add the necessary JavaScript to handle the search and lazy loading
function cwpai_add_breakdance_scripts() {
  ?>
    <script>
      jQuery(document).ready(function($) {
        // Handle search button click
        $('#breakdance-search-button').on('click', function() {
          var searchQuery = $('#breakdance-search-input').val();

          // Display the loader
          $('#breakdance-loader').show();

          // Clear any previous results
          $('#breakdance-results').html('');

          // Send AJAX request to perform the search and display results
          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'cwpai_breakdance_search',
              search_query: searchQuery
            },
            success: function(response) {
              // Hide the loader
              $('#breakdance-loader').hide();

              // Insert the search results
              $('#breakdance-results').html(response);

              // Show the results container
              $('#breakdance-results').show();
            },
            error: function(xhr, textStatus, errorThrown) {
              console.log('Error: ' + textStatus);
            }
          });
        });
      });
    </script>
  <?php
}
add_action('wp_footer', 'cwpai_add_breakdance_scripts');

require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/dwojt2/Custom-Breakdance-Post-Loop-Builder',
	__FILE__,
	'custom_breakdance_post_loop_builder'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');
?>