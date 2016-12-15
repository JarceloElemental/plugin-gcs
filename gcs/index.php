<?php
/*
Plugin Name: GCS
Plugin URI: https://github.com/nshttpd/plugin-gcs/
Description: This plugin allows you to upload users' images to Google Cloud Storage
Version: 0.0.0
Author: nshttpd
Author URI: https://github.com/nshttpd/
Short Name: gcs
*/

  require_once osc_plugins_path() . osc_plugin_folder(__FILE__) . 'vendor/autoload.php';

  use Google\Cloud\Storage\StorageClient;

  function gcs_install() {
    $conn = getConnection();
    osc_set_preference('bucket', '', 'gcs', 'STRING');
    osc_set_preference('project', '', 'gcs', 'STRING');
    $conn->autocommit(true);
  }

  function gcs_uninstall() {
    osc_delete_preference('bucket', 'gcs');
    osc_delete_preference('project', 'gcs');
  }

  function gcs_upload($resource) {
    $bucket = get_gcs_bucket();
    if (osc_keep_original_image()) {
      $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_original.' . $resource['s_extension'], 'r'));
    }
    $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.' . $resource['s_extension'], 'r'));
    $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '._preview.' . $resource['s_extension'], 'r'));
    $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.thumbnail.' . $resource['s_extension'], 'r'));
    gcs_unlink_resource($resource);
  }

  # http://storage.googleapis.com/uspto-pair/applications/05900002.zip
  function gcs_resource_path($path) {
    return "http://storage.googleapis.com/". osc_get_preference('bucket', 'gcs') ."/". str_replace(osc_base_url().osc_resource_field("s_path"), '', $path);
  }

  function get_gcs_bucket() {
    $storage = new StorageClient([
      'projectId' => osc_get_preference('project', 'gcs')
    ]);
    return $storage->bucket(osc_get_preference('bucket', 'gcs'));
  }

  function gcs_regenerate_image($resource) {
    $bucket = get_gcs_bucket();
    $path = $resource['pk_id_id']. "_original" . $resource['s_extenson'];
    $img = $bucket->object($path);
    if(!$img.exists()) {
      $path = $resource['pk_i_id']. "." . $resource['s_extension'];
      $img = $bucket->object($path);
    }
    if(!$img.exists()) {
      $path = $resource['pk_i_id']. "_thumbnail." . $resource['s_extension'];
      $img = $bucket->object($path);
    }
    if($img.exists()) {
      $stream = $img->downloadToFile(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . "." . $resource['s_extension']);
      $i = $bucket->object($resource['pk_i_id']. "_original." . $resource['s_extension']);
      $i->delete();
      $i = $bucket->object($resource['pk_i_id']. "." . $resource['s_extension']);
      $i->delete();
      $i = $bucket->object($resource['pk_i_id']. "_preview." . $resource['s_extension']);
      $i->delete();
      $i = $bucket->object($resource['pk_i_id']. "_thumbnail." . $resource['s_extension']);
      $i->delete();
    }
  }

  function gcs_unlink_resource($resource) {
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_original.' . $resource['s_extension']);
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.' . $resource['s_extension']);
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_preview.' . $resource['s_extension']);
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_thumbnail.' . $resource['s_extension']);
  }

  function gcs_delete_from_bucket($resource) {
    $bucket = get_gcs_bucket();
    $i = $bucket->object($resource['pk_i_id']. "_original." . $resource['s_extension']);
    $i->delete();
    $i = $bucket->object($resource['pk_i_id']. "." . $resource['s_extension']);
    $i->delete();
    $i = $bucket->object($resource['pk_i_id']. "_preview." . $resource['s_extension']);
    $i->delete();
    $i = $bucket->object($resource['pk_i_id']. "_thumbnail." . $resource['s_extension']);
    $i->delete();
  }

  function gcs_admin_menu() {
    if (osc_version()<320) {
      echo '<h3><a href="#">Google Cloud Storage</a></h3>
      <ul> 
          <li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'admin/conf.php') . '">&raquo; ' . __('Settings', 'gcs') . '</a></li>
      </ul>';
    } else {
      osc_add_admin_submenu_divider('plugins', 'Google Cloud Storage plugin', 'gcs_divider', 'administrator');
      osc_add_admin_submenu_page('plugins', __('Google Cloud Storage options', 'gcs'), osc_route_admin_url('gcs-admin-conf'), 'gcs_settings', 'administrator');
    }
  }

  function gcs_configure_link() {
    if(osc_version()<320) {
        osc_redirect_to(osc_admin_render_plugin_url(osc_plugin_folder(__FILE__)).'admin/conf.php');
    } else {
        osc_redirect_to(osc_route_admin_url('gcs-admin-conf'));
    }
  }
    
  if(osc_version()>=320) {
      /**
        * ADD ROUTES (VERSION 3.2+)
        */
      osc_add_route('gcs-admin-conf', 'gcs/admin/conf', 'gcs/admin/conf', osc_plugin_folder(__FILE__).'admin/conf.php');
  }      

  osc_register_plugin(osc_plugin_path(__FILE__), 'gcs_install');
  osc_add_hook(osc_plugin_path(__FILE__)."_uninstall", 'gcs_uninstall');
  osc_add_hook(osc_plugin_path(__FILE__)."_configure", 'gcs_configure_link');
  osc_add_hook('uploaded_file', 'gcs_upload');
  osc_add_filter('resource_path', 'gcs_resource_path');
  osc_add_hook('regenerate_image', 'gcs_regenerate_image');
  osc_add_hook('regenerated_image', 'gcs_upload');
  osc_add_hook('delete_resource', 'gcs_delete_from_bucket');
  if(osc_version()<320) {
      osc_add_hook('admin_menu', 'gcs_admin_menu');
  } else {
      osc_add_hook('admin_menu_init', 'gcs_admin_menu');
  }

?>
