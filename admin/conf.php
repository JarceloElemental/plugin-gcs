<?php if (!defined('OC_ADMIN') || OC_ADMIN!==true) exit('Access is not allowed.');
    /*
     *      OSCLass – software for creating and publishing online classified
     *                           advertising platforms
     *
     *                        Copyright (C) 2010 OSCLASS
     *
     *       This program is free software: you can redistribute it and/or
     *     modify it under the terms of the GNU Affero General Public License
     *     as published by the Free Software Foundation, either version 3 of
     *            the License, or (at your option) any later version.
     *
     *     This program is distributed in the hope that it will be useful, but
     *         WITHOUT ANY WARRANTY; without even the implied warranty of
     *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *             GNU Affero General Public License for more details.
     *
     *      You should have received a copy of the GNU Affero General Public
     * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */

    if(Params::getParam('plugin_action')=='done') {
        osc_set_preference('bucket', Params::getParam('bucket'), 'gcs', 'STRING');
        osc_set_preference('project', Params::getParam('project'), 'gcs', 'STRING');
        if(osc_version()<320) {
            echo '<div style="text-align:center; font-size:22px; background-color:#00bb00;"><p>' . __('Congratulations. The plugin is now configured', 'gcs') . '.</p></div>' ;
            osc_reset_preferences();
        } else {
            // HACK : This will make possible use of the flash messages ;)
            ob_get_clean();
            osc_add_flash_ok_message(__('Congratulations, the plugin is now configured', 'gcs'), 'admin');
            osc_redirect_to(osc_route_admin_url('gcs-admin-conf'));
        }
    }
?>
<div id="settings_form" style="border: 1px solid #ccc; background: #eee; ">
    <div style="padding: 20px;">
        <div style="float: left; width: 100%;">
            <fieldset>
                <legend><?php _e('Google Cloud Storage', 'gcs'); ?></legend>
                <form name="gcs_form" id="gcs_form" action="<?php echo osc_admin_base_url(true); ?>" method="post" enctype="multipart/form-data" >
                    <div style="float: left; width: 100%;">
                    <input type="hidden" name="page" value="plugins" />
                    <input type="hidden" name="action" value="renderplugin" />
                    <?php if(osc_version()<320) { ?>
                        <input type="hidden" name="file" value="<?php echo osc_plugin_folder(__FILE__); ?>conf.php" />
                    <?php } else { ?>
                        <input type="hidden" name="route" value="gcs-admin-conf" />
                    <?php }; ?>
                    <input type="hidden" name="plugin_action" value="done" />
                        <label for="bucket"><?php _e('Name of the bucket (it should be a worldwide-unique name)', 'gcs'); ?></label>
                        <br/>
                        <input type="text" name="bucket" id="bucket" value="<?php echo osc_get_preference('bucket', 'gcs'); ?>"/>
                        <br/>
                        <label for="project"><?php _e('Project Name', 'gcs'); ?></label>
                        <br/>
                        <input type="text" name="project" id="project" value="<?php echo osc_get_preference('project', 'gcs'); ?>"/>
                        <br/>
                        <?php printf(__("You need an GCP Project. More information on %s",'gcs'), '<a href="https://cloud.google.com/">https://cloud.google.com/</a>'); ?>
                        <br/>
                        <span style="float:right;"><button type="submit" style="float: right;"><?php _e('Update', 'gcs');?></button></span>
                    </div>
                    <br/>
                    <div style="clear:both;"></div>
                </form>
            </fieldset>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>