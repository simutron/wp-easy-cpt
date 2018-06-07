<?php

namespace Simutron\EasyCPT;

class EasyRole 
{

  protected $cpt_role_name;
  protected $cpt_role_post_types;
  protected $cpt_role_capabilities;
  protected $cpt_role_textdomain;

  private $single_caps = array(
    'edit_',
    'read_',
    'publish_',
    'delete_',
  );

  private $plural_caps = array(
    'edit_',
    'edit_others_',
    'edit_private_',
    'edit_published_',
    'delete_',
    'delete_others_',
    'delete_private_',
    'delete_published_',
    'publish_'
  );

  private $post_type_caps = array(
    'manage_terms',
    'edit_terms',
    'delete_terms',
    'assign_terms',
  );

  public function __construct( $name, $post_types = array(), $capabilities = array(), $textdomain = null)
  {
    if (is_string($name))
    {
      $this->cpt_role_name = strtolower( str_replace(' ', '_', $name ) );
      $this->cpt_role_post_types = (array)$post_types;
      $this->cpt_role_capabilities = $capabilities;
      $this->cpt_role_textdomain = $textdomain;

      $role = get_role($this->cpt_role_name );
      if (null !== $role)
        $this->deactivate_role();

      if (null === get_role($this->cpt_role_name ) )
      {
        add_action( 'init', array( $this, 'create_role'), 0 );
        add_action( 'deactivated_plugin', array( $this, 'deactivate_role'));        
      }
    }
  } // END __construct

  function create_role()
  {
    $default_caps = array(
      'read'            => true,
      'unfiltered_html' => true,
      'upload_files'    => true,
    );

    $caps = array_merge( $default_caps, $this->cpt_role_capabilities);
    $name = ucwords(str_replace('_', ' ', $this->cpt_role_name));

    add_role( $this->cpt_role_name, __($name, $this->cpt_role_textdomain), $caps );

    foreach (array($this->cpt_role_name, 'administrator') as $role_name) {
      $role = get_role($role_name);
      if (null !== $role) {
        foreach ( $this->cpt_role_post_types as $post_type)
        {
          foreach ( $this->single_caps as $single_cap )
          {
            $cap_key = $single_cap . $post_type;
            $role->add_cap($cap_key, true);
          }

          $post_type_plurals = $post_type . 's';
          foreach ( $this->plural_caps as $plural_cap )
          {
            $cap_key = $plural_cap . $post_type_plurals;
            $role->add_cap($cap_key, true);
          }

          if (is_array($this->post_caps))
          {
            foreach ( $this->post_caps as $post_cap )
            {
              $cap_val = 'edit_' . $post_type;
              $role->add_cap($post_cap, $cap_key);
            }
          }
        }
      }
    }
  } // END create_role

  function deactivate_role()
  {
    $name = ucwords(str_replace('_', ' ', $this->cpt_role_name));

    foreach (array($this->cpt_role_name, 'administrator') as $role_name) {
      $role = get_role($role_name);
      if (null !== $role) {
        foreach ( $this->cpt_role_post_types as $post_type)
        {
          foreach ( $this->single_caps as $single_cap )
          {
            $cap_key = $single_cap . $post_type;
            $role->remove_cap ($cap_key );
          }

          $post_type_plurals = $post_type . 's';
          foreach ( $this->plural_caps as $plural_cap )
          {
            $cap_key = $plural_cap . $post_type_plurals;
            $role->remove_cap( $cap_key );
          }

          if (is_array( $this->post_caps))
          {
            foreach ( $this->post_caps as $post_cap )
            {
              $cap_val = 'edit_' . $post_type;
              $role->remove_cap($post_cap);
            }
          }
        }
      }
    }
    remove_role( $this->cpt_role_name );
  } // END deactivate_role
}