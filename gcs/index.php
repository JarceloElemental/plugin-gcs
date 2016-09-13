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
    $storage = new StorageClient([
      'projectId' => osc_get_preference('project', 'gcs')
    ]);
    $bucket = $storage->bucket(osc_get_preference('project', 'gcs'));
    if (osc_keep_original_image()) {
      $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_original.' . $resource['s_extension'], 'r'));
    }
    $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.' . $resource['s_extension'], 'r'));
    $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '._preview.' . $resource['s_extension'], 'r'));
    $bucket->upload(fopen(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.thumbnail.' . $resource['s_extension'], 'r'));
    gcs_unlink_resource($resource);
  }

  function gcs_unlink_resource($resource) {
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_original.' . $resource['s_extension']);
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.' . $resource['s_extension']);
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_preview.' . $resource['s_extension']);
      @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_thumbnail.' . $resource['s_extension']);
  }
# http://storage.googleapis.com/uspto-pair/applications/05900002.zip

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
