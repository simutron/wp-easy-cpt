<?php

namespace Simutron\EasyCPT;

class EasyCPT 
{
  /**
   * The meta boxes for the CPT
   */
  protected $cpt_meta_boxes = array();
  protected $cpt_fields = array();

  protected $cpt_name;
  protected $cpt_plural_name;
  protected $cpt_textdomain;
  protected $cpt_args;
  protected $cpt_labels;
  protected $cpt_rest_fields;

  public function __construct($name, $plural_name = null, $args = array(), $labels = array(), $rest_fields = array(), $textdomain = null)
  {
    $this->cpt_name = strtolower( str_replace(' ', '_', $name ) );
    $this->cpt_plural_name = ( $plural_name ) ? strtolower( str_replace(' ', '_', $plural_name ) ) : null;
    $this->cpt_textdomain = ( $textdomain ) ? strtolower( str_replace(' ', '_', $textdomain ) ) : null;
    $this->cpt_args = $args ;
    $this->cpt_labels = $labels;
    $this->cpt_rest_fields = $rest_fields;

    if (!post_type_exists($this->cpt_name)) {
      add_action('init', [$this, 'register_post_type']);

      add_action('edit_form_after_title', function() {
        global $post, $wp_meta_boxes;
        do_meta_boxes(get_current_screen(), 'advanced', $post);
        unset($wp_meta_boxes[get_post_type($post)]['advanced']);
      });
  
      if (is_array($this->cpt_rest_fields ) )
      {
        add_action( 'init', array( $this, 'register_rest_fields') );
      }
    }
  }

  function register_post_type()
  {
    $post_type_name = ucwords(str_replace('_', ' ', $this->cpt_name));
    $post_type_name_plural = ( $this->cpt_plural_name === null ) ? $post_type_name . 's' : $this->cpt_plural_name;
    $slug = strtolower(str_replace([' ', '_'], '-', $post_type_name));

    $default_labels = array(
      'name'                  => _x( $post_type_name_plural, 'Post Type General Name', $this->cpt_textdomain ),
      'singular_name'         => _x( $post_type_name, 'Post Type Singular Name', $this->cpt_textdomain ),
      'menu_name'             => __($post_type_name_plural, $this->cpt_textdomain ),
      'name_admin_bar'        => __( $post_type_name_plural, $this->cpt_textdomain ),
      'parent_item_colon'     => __( '',  $this->cpt_textdomain ),
      'add_new'               => __('Add New ' . strtolower($post_type_name), $this->cpt_textdomain ),
      'add_new_item'          => __('Add New ' . $post_type_name, $this->cpt_textdomain ),
      'edit_item'             => __('Edit ' . $post_type_name, $this->cpt_textdomain ),
      'new_item'              => __('New ' . $post_type_name, $this->cpt_textdomain ),
      'all_items'             => __('All ' . $post_type_name_plural, $this->cpt_textdomain ),
      'update_item'           => __('Update' . strtolower($post_type_name), $this->cpt_textdomain),
      'view_item'             => __('View ' . $post_type_name, $this->cpt_textdomain ),
      'search_items'          => __('Search ' . $post_type_name_plural, $this->cpt_textdomain ),
      'not_found'             => __('No ' . strtolower($post_type_name_plural) . ' found', $this->cpt_textdomain ),
      'not_found_in_trash'    => __('No ' . strtolower($post_type_name_plural) . ' found in Trash', $this->cpt_textdomain ),
    );

    $labels = array_merge( $default_labels, $this->cpt_labels );

    $default_args = array(
      'label' => _x($post_type_name, $this->cpt_textdomain),
      'labels' => $labels,
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'show_in_rest'					=> is_array($this->cpt_rest_fields),
      'can_export'            => true,
      'has_archive'           => false,		
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'supports'              => ['thumbnail', 'editor'],
      'capability_type'       => $this->cpt_name,
      'map_meta_map'          => true,
      'rewrite' 							=> array( 'slug' => $slug ),
    
    );
    $args = array_merge( $default_args, $this->cpt_args );

    // Register the post type
    register_post_type($this->cpt_name, $args);
  }

  public function get_name()
  {
    return $this->cpt_name;
  }

  function register_rest_fields()
  {
    foreach ($this->cpt_rest_fields as $rest_key => $rest_field)
    {
      if (is_string($rest_field)) 
      {
        register_rest_field( $this->cpt_name, $rest_key, array(
          'get_callback' => function( $post ) use( $rest_field ) {
              $value = get_post_meta( $post['id'], $rest_field, true );
              return $value; //get_post_meta( $post['id'], $rest_field, true );
          }
        ) );
      }
      else 
      {

      }
    }
  } // END register_rest_fields
}