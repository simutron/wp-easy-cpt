<?php

namespace Simutron\EasyCPT;

class EasyTaxonomy 
{

  protected $cpt_taxonomy_name;
  protected $cpt_taxonomy_plural_name;
  protected $cpt_taxonomy_textdomain;
  protected $cpt_taxonomy_args;
  protected $cpt_taxonomy_labels;
  protected $cpt_taxonomy_post_type;


  public function __construct($cpt, $name, $plural_name = null, $args = array(), $labels = array(), $textdomain = null)
  {
    $this->cpt_taxonomy_name = strtolower( str_replace(' ', '_', $name ) );
    $this->cpt_taxonomy_plural_name = ( $plural_name ) ? strtolower( str_replace(' ', '_', $name ) ) : null;
    $this->cpt_taxonomy_textdomain = ( $textdomain ) ? strtolower( str_replace(' ', '_', $textdomain ) ) : null;
    $this->cpt_taxonomy_args = $args ;
    $this->cpt_taxonomy_labels = $labels;
    $this->cpt_taxonomy_post_type = ( is_string( $cpt ) ) ? $cpt : $cpt->get_name();
        
    if (!taxonomy_exists($this->cpt_taxonomy_name)) {
      add_action('init', [$this, 'register_taxonomy']);
    }
  }

  function register_taxonomy()
  {
    if (!taxonomy_exists($this->cpt_taxonomy_name)) {
        
      $name = ucwords(str_replace('_', ' ', $this->cpt_taxonomy_name));
        
      // Check if we have a plural name set
      if ($this->cpt_taxonomy_plural_name === null) {
          $this->cpt_taxonomy_plural_name = $name . 's';
      }

      $default_labels = array(
        'name'              => _x($this->cpt_taxonomy_plural_name, 'taxonomy general name', $this->cpt_taxonomy_textdomain),
        'singular_name'     => _x($name, 'taxonomy singular name', $this->cpt_taxonomy_textdomain),
        'search_items'      => __('Search ' . $this->cpt_taxonomy_plural_name, $this->cpt_taxonomy_textdomain),
        'all_items'         => __('All ' . $this->cpt_taxonomy_plural_name, $this->cpt_taxonomy_textdomain),
        'parent_item'       => __('Parent ' . $name, $this->cpt_taxonomy_textdomain),
        'parent_item_colon' => __('Parent ' . $name . ':', $this->cpt_taxonomy_textdomain),
        'edit_item'         => __('Edit ' . $name, $this->cpt_taxonomy_textdomain),
        'update_item'       => __('Update ' . $name, $this->cpt_taxonomy_textdomain),
        'add_new_item'      => __('Add New ' . $name, $this->cpt_taxonomy_textdomain),
        'new_item_name'     => __('New ' . $name . ' Name', $this->cpt_taxonomy_textdomain),
        'menu_name'         => __($this->cpt_taxonomy_plural_name, $this->cpt_taxonomy_textdomain),
      );
      $labels = array_merge( $default_labels, $this->cpt_taxonomy_labels);
      
      $default_args = array(
        'label'             => $this->cpt_taxonomy_plural_name,
        'labels'            => $labels,
        'public'            => true,
        'show_ui'           => true,
        'show_in_nav_menus' => true,
        '_builtin'          => false,
      );
      $args = array_merge( $default_args, $this->cpt_taxonomy_args );

      register_taxonomy($this->cpt_taxonomy_name, $this->cpt_taxonomy_post_type, $args);
    } 
    else 
    {
      // Taxonomy is already registered
      // just attach it to the post type
      register_taxonomy_for_object_type($this->cpt_taxonomy_name, $this->cpt_taxonomy_post_type);
    }
  } // END register_taxonomy

  public function add_post_type($cpt)
  {
    if (taxonomy_exists($this->cpt_taxonomy_name)) 
    {
      $taxonomy_name = $this->cpt_taxonomy_name;
      add_action( 'init', function() use ($taxonomy_name) {
        register_taxonomy_for_object_type($taxonomy_name, $cpt->get_name());
      });
    }
  } // END add_post_type
} // END class