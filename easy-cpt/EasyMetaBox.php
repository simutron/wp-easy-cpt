<?php

namespace Simutron\EasyCPT;

class EasyMetaBox 
{
  protected $cpt_mb_fields = array();

  protected $cpt_mb_title;
  protected $cpt_mb_context;
  protected $cpt_mb_priority;
  protected $cpt_mb_meta_box_id;
  protected $cpt_mb_textdomain;
  protected $cpt;

  public function __construct($cpt, $title, $fields = [], $context = 'normal', $priority = 'default', $textdomain = null)
  {
    if (! empty( $title ) && ! empty( $cpt ) && is_admin())
    {
      $this->cpt_mb_title = $title;
      $this->cpt_mb_context = $context;
      $this->cpt_mb_priority = $priority;
      $this->cpt_mb_fields = $this->add_fields($fields);
      $this->cpt_mb_textdomain = $textdomain;
      $this->cpt_mb_meta_box_id = strtolower(str_replace(' ', '_', $title));
      $this->cpt = $cpt;

      // add the meta box
      add_action('admin_init', array( $this, 'add_meta_box' ));

      // save the meta values
      add_action('save_post', array( $this, 'save_meta_box' ));
      add_filter( 'wp_insert_post_data', array($this, 'change_title' ), 99, 2);		

    }
  } // END __construct

  function add_meta_box()
  {
    add_meta_box(
      $this->cpt_mb_meta_box_id,
      __($this->cpt_mb_title, $this->cpt_mb_textdomain),
      array( $this, 'display_meta_box' ),
      $this->cpt->get_name(),
      $this->cpt_mb_context,
      $this->cpt_mb_priority
    );
  } // END add_meta_box

  function add_fields($field)
  {
    $fields = array();
    foreach ($field as $field_key => $field_value)
    {
      $default_field = array(
        'type' => 'text',
        'label' => __( $field_key . ':', $this->cpt_mb_textdomain),
        'is_title' => false,
      );

      $merged_field = array_merge($default_field, $field_value);      
      $fields[$field_key] = $merged_field;
    }

    return $fields;
  } // END add_fields

  function display_meta_box( $post )
  {
    foreach ($this->cpt_mb_fields as $field_key => $field_value)
    {
      $value = get_post_meta($post->ID, $field_key, true);

      if ($field_value['type'] === 'nonce')
      {
        wp_nonce_field( $this->cpt->get_name(), $field_key );
      }
      elseif ($field_value['type'] === 'text')
      {
        echo '<p><strong><label for="' . $field_key . '">' . __($field_value['label'], $this->cpt_mb_textdomain ) .'</label></strong></p>';
        echo '<p><input type="text" class="widefat" id="' . $field_key . '" name="' . $field_key . '" value="' . esc_html($value) . '"></p>';	
      }
      elseif ($field_value['type'] === 'hidden')
      {
        echo '<input type="hidden" id="' . $field_key . '" name="' . $field_key . '" value="' . esc_html($value) . '">';	
      }
      elseif ($field_value['type'] === 'wysiwyg')
      {
        echo '<p><strong><label for="' . $field_value['label'] . '">' . __($field_value['label'], $this->cpt_mb_textdomain ) .'</label></strong></p>';
        wp_editor($value, $field_key, array(
          'textarea_rows' => 8,
        ));
      }
      elseif ($field_value['type'] === 'select_cpt')
      {
        $cpt_name = $field_value['cpt'];
        $cpts = get_posts(array(
          'post_type' => $cpt_name,
          'posts_per_page' => -1,
        ));
    
        echo '<p><strong><label for="' . $field_value['label'] . '">' . __($field_value['label'], $this->cpt_mb_textdomain ) .'</label></strong></p>';
        echo '<p><select name="' . $field_key . '" id="' . $field_key . '">';
        echo '<option value="">Bitte wählen</option>';
        foreach($cpts as $item) {
            echo '<option value="'.$item->ID.'"',$value == $item->ID ? ' selected="selected"' : '','>'.$item->post_title.'</option>';
        } 
        echo '</select></p>';
      }

    }
  } // END display_meta_box

  function save_meta_box( $post_id )
  {
    $values = array();
    $custom_title = "";

    foreach ($this->cpt_mb_fields as $field_key => $field_value)
    {
      if ( $field_value['is_title'])
      $custom_title .= $_POST[$field_key] . ' ';

      if ( $field_value['type'] === 'nonce')
      {
        $nonce = $_POST[$field_key];
      }
      elseif ( $field_value['type'] === 'text')
      {
        $values[$field_key] = isset ( $_POST[$field_key] ) ? sanitize_text_field( $_POST[$field_key] ) : false;
      }
      elseif ( $field_value['type'] === 'wysiwyg')
      {
        $values[$field_key] = isset ( $_POST[$field_key] ) ? $_POST[$field_key] : false;
      }
      elseif ($field_value['type'] === 'select_cpt')
      {
        $values[$field_key] = isset ( $_POST[$field_key] ) ? $_POST[$field_key] : false;
      }
    }

    if (! isset( $nonce ))
      return $post_id;

		if ( ! wp_verify_nonce( $nonce, $this->cpt->get_name() ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

    if ( $this->cpt->get_name() == $_POST['post_type'] ) 
    {
  		if ( ! current_user_can( 'edit_' . $this->cpt->get_name(), $post_id ) )
				return $post_id;

      foreach ($values as $key => $val)
      {
        update_post_meta( $post_id, $key, $val);
      }

	  }
    
  } // END save_meta_box

  function change_title($data, $postarr) {
    // Important to encapsulate this function to only run
    // on the specific post type, use a hidden field or
    // unique field to determine when to run
    $custom_title = "";

    foreach ($this->cpt_mb_fields as $field_key => $field_value)
    {
      if ( $field_value['is_title'])
      $custom_title .= $_POST[$field_key] . ' ';
    }


    if (! empty( trim($custom_title ))) 
    {
      $title = substr($custom_title, 0, -1);

      // Record the manually created post title to $data['post_title'] so
      // WordPress will save it as post title
      $data['post_title'] = $title;
  
      // Create manually post_name using data from title
      $slug = sanitize_title_with_dashes($title);
      $data['post_name'] = wp_unique_post_slug($slug, $postarr['ID'],  $postarr['post_status'], $postarr['post_type'], $postarr['post_parent']);
    }
    // Remember this is a "filter", need to return the data back!
    return $data;
  }
}