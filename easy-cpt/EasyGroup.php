<?php

namespace Simutron\EasyCPT;

class EasyGroup 
{
  protected $cpt_group_types = array();

  protected $cpt_group_title;
  protected $cpt_group_taxonomy;


  public function __construct($title, $taxonomy, $types = [])
  {
    if (! empty( $title ) && ! empty( $taxonomy ) )
    {
      $this->cpt_group_title = $title;
      foreach ($types as $raw_type)
      {
        if (is_string( $raw_type ))
        {
          $this->cpt_group_types[] = $raw_type;    
        }  

        if ($raw_type instanceof EasyCPT)
        {
          $this->cpt_group_types[] = $raw_type->get_name();    
        }
      }

      $this->cpt_group_taxonomy = $taxonomy;

      // save the group values
      add_action('save_post', array( $this, 'save_group' ));

      add_action( 'pre_get_posts', array( $this, 'get_posts_for_group'));
     // add_filter('ajax_query_attachments_args', 'get_media_for_group');
      
    }
  } // END __construct

  function save_group( $post_id )
  {
    
    $group_user = wp_get_current_user();
    $group_val = get_user_meta( $group_user->ID, $this->cpt_group_title, true);
    $group_term = get_term( $group_val, $this->cpt_group_taxonomy);

    if ( in_array( $_POST['post_type'], $this->cpt_group_types ) )
    {
      if ( in_array( $this->cpt_group_taxonomy, get_post_taxonomies()))
      {
        wp_set_post_terms( $post_id, $group_term->name, $this->cpt_group_taxonomy, false ); 
      }
    }

    return $post_id;
  } // END save_group

  function get_posts_for_group( $query ) {
    if (! is_super_admin())
    {
      global $pagenow;


      $group_user = wp_get_current_user();
      $group_val = get_user_meta( $group_user->ID, $this->cpt_group_title, true);
      $group_term = get_term( $group_val, $this->cpt_group_taxonomy);
  
      if (isset($_GET['post_type'])) {
          $type = $_GET['post_type'];
      }
    
      if ( in_array( $type,  $this->cpt_group_types ) && is_admin() && ( $pagenow == 'edit.php' || $pagenow == 'upload.php') ) 
      {
        $tax_query = array(
          array(
            'taxonomy' => $this->cpt_group_taxonomy,
            'field' => 'name',
            'terms' => $group_term->name,
          ),
        );
  
        $query->query_vars['tax_query'] = $tax_query;
    
      }	
    }

    return $query;
  } // END get_artists_for_org

  function get_media_for_group( $query ) {
    if (! is_super_admin())
    {
      
      $type = 'attachement';

      $group_user = wp_get_current_user();
      $group_val = get_user_meta( $group_user->ID, $this->cpt_group_title, true);
      $group_term = get_term( $group_val, $this->cpt_group_taxonomy);
  
      $tax_query = array(
        array(
          'taxonomy' => $this->cpt_group_taxonomy,
          'field' => 'name',
          'terms' => $group_term->name,
        ),
      );

      /*
      $query->query_vars['post_status'] = 'inherit';
      $query->query_vars['tax_query'] = $tax_query;  
      */
    }
  
    return $query;
  } // END get_media_for_group

} // END class