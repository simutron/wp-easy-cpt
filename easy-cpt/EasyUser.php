<?php

namespace Simutron\EasyCPT;

class EasyUser 
{

  protected $cpt_user_name;
  protected $cpt_user_fields;
  protected $cpt_user_textdomain;

  public function __construct($name, $fields = array(), $textdomain = null)
  {
    $this->cpt_user_name = $name;
    $this->cpt_user_fields = $fields;
    $this->cpt_textdomain = $textdomain;

    if (is_string( $this->cpt_user_name ))
    {
      add_action( 'show_user_profile', array( $this, 'show_user_extra_fields') );
      add_action( 'edit_user_profile', array( $this, 'show_user_extra_fields') );
  
      add_action( 'register_form', array( $this, 'user_register_form') );
      add_action( 'user_new_form', array( $this, 'admin_registration_form') );
  
      add_action( 'personal_options_update', array($this, 'user_register') );
      add_action( 'edit_user_profile_update', array($this, 'user_register') );  
    }

  }

  function show_user_extra_fields( $user )
  {
    foreach ($this->cpt_user_fields as $cap_key => $cap_field)
    {
      echo '<h3>' . __( $cap_key, $this->cpt_textdomain ) . '</h3>';

      foreach ($cap_field as $field_key => $field_value)
      {
        $value = get_user_meta($user->ID, $field_key, true);

        if ($field_value['type'] === 'select_taxonomy')
        {
          if (is_super_admin())
          {
            $tax_name = $field_value['taxonomy'];
            $taxonomies = get_terms(array(
              'taxonomy' => $tax_name,
              'hide_empty' => false,
            ));

            echo '<table class="form-table"><tr>';
            echo '<th><label for="' . $field_key . '">' . $field_value['label'] .'</label></th>';
            echo '<td><select name="' . $field_key . '" id="' . $field_key . '">';
            echo '<option value="">Bitte wählen</option>';
            foreach($taxonomies as $item) {
              echo '<option value="'.$item->term_id.'"',($value == $item->term_id) ? ' selected="selected"' : '','>'.$item->name.'</option>';
//              echo '<option value="'.$item->term_id.'" >'.$item->post_title.'</option>'; 
//              var_dump($item);
            } 
            echo '</select></td>';
            echo '</tr></table>';
          }
          else
          {
            echo '<input type="hidden" name=" '. $field_key . '" id=" '. $field_key . '" value="' . $value . '" />';
          }
        }
  
      }
    }
  } // END show_user_extra_fields

  function user_register_form() {

    foreach ($this->cpt_user_fields as $cap_key => $cap_field)
    {
      echo '<h3>' . __( $cap_key, $this->cpt_textdomain ) . '</h3>';

      foreach ($cap_field as $field_key => $field_value)
      {
        $value = get_user_meta($user->ID, $field_key, true);

        if ($field_value['type'] === 'select_taxonomy')
        {
          $tax_name = $field_value['taxonomy'];
          $taxonomies = get_terms(array(
            'taxonomy' => $tax_name,
            'hide_empty' => false,
          ));
      
          echo '<p><label for="' . $field_key . '">' . $field_value['label'] .'</label></p>';
          echo '<select name="' . $field_key . '" id="' . $field_key . '">';
          echo '<option value="">Bitte wählen</option>';
          foreach($taxonomies as $item) {
            echo '<option value="'.$item->term_id.'"',($value == $item->term_id) ? ' selected="selected"' : '','>'.$item->name.'</option>';
          } 
          echo '</select>';
        }
  
      }
    }
  
	} // END user_register_form

  function user_register( $user_id ) {

    $values = array();
    foreach ($this->cpt_user_fields as $cap_key => $cap_field)
    {
      foreach ($cap_field as $field_key => $field_value)
      {
        if ($field_value['type'] === 'select_taxonomy')
        {
          $values[$field_key] = isset ( $_POST[$field_key] ) ? $_POST[$field_key] : false;
        }
  
      }
    }

    foreach ($values as $key => $val)
    {
      update_user_meta( $user_id, $key, $val);
    }
	} // END user_register

  function admin_registration_form( $operation ) {
		if ( 'add-new-user' !== $operation ) {
			// $operation may also be 'add-existing-user'
			return;
		}
	
    foreach ($this->cpt_user_fields as $cap_key => $cap_field)
    {
      echo '<h3>' . __( $cap_key, $this->cpt_textdomain ) . '</h3>';

      foreach ($cap_field as $field_key => $field_value)
      {
        $value = get_user_meta($user->ID, $field_key, true);

        if ($field_value['type'] === 'select_taxonomy')
        {
          $tax_name = $field_value['taxonomy'];
          $taxonomies = get_terms(array(
            'taxonomy' => $tax_name,
            'hide_empty' => false,
          ));
      
          echo '<table class="form-table"><tr>';
          echo '<th><label for="' . $field_key . '">' . $field_value['label'] .'</label></th>';
          echo '<td><select name="' . $field_key . '" id="' . $field_key . '">';
          echo '<option value="">Bitte wählen</option>';
          foreach($taxonomies as $item) {
            echo '<option value="'.$item->term_id.'"',($value == $item->term_id) ? ' selected="selected"' : '','>'.$item->name.'</option>';
          } 
          echo '</select></td>';
          echo '</tr></table>';
        }
  
      }
    }
	} // END admin_registration_form

}
