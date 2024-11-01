<?php
function smsfly_site_options_page_show() {
    if (empty(get_option('SMSFLY_apikey'))) {
        wp_die(
            __( 'You did not provide the authorization token. Please add it in the gateway setup' ) .
            ': ' .
            '<a href="admin.php?page=SMSFly_settings">' . __('Gateway setup', 'smsfly') . '</a>'
        );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );
    }
    if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
        add_settings_error('smsfly_site_options_page_show_group', 'settings_updated', __('Settings saved.'), 'updated');
        settings_errors( 'smsfly_site_options_page_show_group' );
    }

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'sms';
	if ( SMSflyC::inst()->auth ) {
		$formStyle = '';
	} else {
		add_settings_error('smsfly_site_options_page_show_group', 'settings_updated', __(SMSflyC::inst()->error, 'smsfly'), 'error');
		settings_errors( 'smsfly_site_options_page_show_group' );
		$formStyle = 'display: none';
	}

    ?>
    <div class="wrap">
        <h2><?php _e('Notifications settings', 'smsfly'); ?></h2>

        <h2 class="nav-tab-wrapper" style="<?php echo $formStyle; ?>">
            <a href="?page=SMSFly_notify&tab=sms" class="nav-tab <?php echo $tab == 'sms' ? 'nav-tab-active' : ''; ?>"><?php _e('SMS Settings', 'smsfly'); ?></a>
            <a href="?page=SMSFly_notify&tab=viber" class="nav-tab <?php echo $tab == 'viber' ? 'nav-tab-active' : ''; ?>"><?php _e('Viber Settings', 'smsfly'); ?></a>
        </h2>
        <?php
        if (SMSflyC::inst()->auth) {
	        if ($tab == 'sms') {
		        smsfly_sms_settings();
	        } else {
		        smsfly_viber_settings();
	        }
        }
        ?>
    </div>
    <?php
}

function smsfly_sms_settings() {
	// Original SMS settings code
	$base_templates = [
		'{USER} - '.__('author of the post page', 'smsfly'),
		'{DATE} - '.__('date of action performed', 'smsfly'),
		'{TIME} - '.__('time of action performed', 'smsfly')
	];
	$post_templates = [
		'{POSTID} - '.__('Record page ID number', 'smsfly'),
		'{POSTTITLE} - '.__('post page name', 'smsfly')
	];
	$user_templates = [
		'{EMAIL} - '.__("user's email", 'smsfly'),
		'{IP} - '.__("user's ip", 'smsfly'),
	];
	$plugin_templates = [
		'{PLUGIN} - '.__('plugin name', 'smsfly')
	];
	$themes_templates = [
		'{THEME} - '.__('theme name', 'smsfly')
	];

	$props = [
		['SMSFLY_site_new_post', __( 'Notification about the publication of a new post', 'smsfly' ), array_merge($base_templates, $post_templates)],
		['SMSFLY_site_update_post', __( 'Post update notification', 'smsfly' ), array_merge($base_templates, $post_templates)],
		['SMSFLY_send_new_user_notifications', __( 'Notification about new user registration', 'smsfly' ), array_merge($base_templates, $user_templates)],
		['SMSFLY_site_user_login', __( 'Notification that the user has logged in to the site', 'smsfly' ), array_merge($base_templates, $user_templates)],
		['SMSFLY_site_install_plugin', __( 'Notification about installing a new plugin', 'smsfly' ), array_merge($base_templates, $plugin_templates)],
		['SMSFLY_site_update_plugin', __( 'Plugin update notification', 'smsfly' ), array_merge($base_templates, $plugin_templates)],
		['SMSFLY_site_install_theme', __( 'Notification about installing a theme on the site', 'smsfly' ), array_merge($base_templates, $themes_templates)],
		['SMSFLY_site_update_theme', __( 'Topic update notification', 'smsfly' ), array_merge($base_templates, $themes_templates)]
	];
	?>
    <form method="post" action="options.php">
		<?php settings_fields( 'SMSFLY_SITE_OPTIONS' ); ?>
        <table class="form-table">
            <tr><td colspan="3"><h3><?php _e('SMS parameters', 'smsfly'); ?>:</h3></td></tr>
        </table>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="SMSFLY_site_phone"><?php _e("Recipient's phone number", 'smsfly'); ?></label></th>
                <td>
                    <input name="SMSFLY_site_phone" type="text" id="SMSFLY_site_phone" value="<?php echo get_option('SMSFLY_site_phone'); ?>" placeholder="<?php echo SMSflyC::inst()->placeholderByRegion(); ?>" class="regular-text">
                </td>
                <td><p class="description"><?php _e("Phone number of the recipient of notification about events on the site, usually the administrator's phone number", 'smsfly'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="SMSFLY_site_source"><?php _e('Sender name', 'smsfly'); ?></label></th>
                <td>
                    <select name="SMSFLY_site_source" id="SMSFLY_site_source" class="regular-text">
						<?php
						$names = SMSflyC::inst()->names['sms'];
						foreach ( $names as $name ) {
							$selected = (get_option('SMSFLY_site_source') === $name) ? 'selected':'';
							echo "<option value='$name' $selected>$name</option>";
						}
						?>
                    </select>
                </td>
                <td><p class="description"></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="SMSFLY_site_to_lat"><?php _e('Conversion to Latin', 'smsfly'); ?></label></th>
                <td>
                    <input name="SMSFLY_site_to_lat" type="checkbox" id="SMSFLY_site_to_lat" <?php  checked( '1', get_option('SMSFLY_site_to_lat') ); ?> value="1">
                </td>
                <td><p class="description"><?php _e('Enable conversion of Cyrillic characters to Latin', 'smsfly'); ?></p></td>
            </tr>
            <tr><td colspan="3"><h3><?php _e('Alert options', 'smsfly'); ?>:</h3></td></tr>
            <tr>
                <th><?php _e('Select alert type', 'smsfly'); ?>:</th>
                <td><select id="SMSFLY_select">
						<?php
						$inputs = ''; $li = [];
						foreach ($props as $prop) {
							echo "<option value='{$prop[0]}'>{$prop[1]}</option>";
							$checked = checked( '1', get_option($prop[0].'_check') );
							$inputs .= "<input type='checkbox' name='$prop[0]_check' $checked value='1' style='display: none'>";
							$value = get_option($prop[0]);
							$inputs .= "<input type='hidden' name='$prop[0]' value='$value'>";

							$li[$prop[0]] = '';
							foreach ($prop[2] as $template_descr) {
								$li[$prop[0]] .= "<li>$template_descr</li>";
							}
						}
						?>
                    </select></td>
                <td class="description"></td>
            </tr>
            <tr>
                <th><label><?php _e('Activated', 'smsfly'); ?></label></th>
                <td><input type="checkbox" id="SMSFLY_input" <?php  checked( '1', get_option('SMSFLY_site_new_post_check') ); ?>></td>
                <td class="description"><?php _e('Enable for selected alert type', 'smsfly'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Message template', 'smsfly'); ?></th>
                <td><textarea rows="4" class="large-text code" id="SMSFLY_textarea"><?php echo get_option('SMSFLY_site_new_post'); ?></textarea></td>
                <td class="description"><?php _e('Specify the SMS message template using the tags below', 'smsfly'); ?></td>
            </tr>
            <tr><td colspan="3">
                    <p><?php _e('For each type of notification, you can set your own substitutions', 'smsfly'); ?>:
                    <ul id="sms-fly-templates-description">
						<?php echo $li['SMSFLY_site_new_post'] ?>
                    </ul>
                    </p>
                </td>
            </tr>
            <script>
                let li = <?php echo json_encode($li); ?>;
                let select = document.getElementById('SMSFLY_select'), input = document.getElementById('SMSFLY_input'), textarea = document.getElementById('SMSFLY_textarea')
                select.addEventListener('change', e => {
                    input.checked = false; textarea.value = ''
                    let prop = e.target.value, prop_input = document.getElementsByName(prop+'_check')[0], prop_textarea = document.getElementsByName(prop)[0]
                    input.checked = prop_input.checked
                    textarea.value = prop_textarea.value

                    let ul = document.getElementById('sms-fly-templates-description')
                    ul.innerHTML = li[prop]
                })

                input.addEventListener('change', e => {
                    let prop_input = document.getElementsByName(select.value+'_check')[0]
                    prop_input.checked = input.checked
                })

                let textareahandler = e => {
                    let prop_textarea = document.getElementsByName(select.value)[0]
                    prop_textarea.value = textarea.value
                }
                textarea.addEventListener('keyup', textareahandler)
                textarea.addEventListener('change', textareahandler)
            </script>
        </table>
	    <?php echo $inputs; submit_button(); ?>
    </form>
	<?php
}

function smsfly_viber_settings() {
	// Analogous Viber settings code
	$base_templates = [
		'{USER} - '.__('author of the post page', 'smsfly'),
		'{DATE} - '.__('date of action performed', 'smsfly'),
		'{TIME} - '.__('time of action performed', 'smsfly')
	];
	$post_templates = [
		'{POSTID} - '.__('Record page ID number', 'smsfly'),
		'{POSTTITLE} - '.__('post page name', 'smsfly')
	];
	$user_templates = [
		'{EMAIL} - '.__("user's email", 'smsfly'),
		'{IP} - '.__("user's ip", 'smsfly'),
	];
	$plugin_templates = [
		'{PLUGIN} - '.__('plugin name', 'smsfly')
	];
	$themes_templates = [
		'{THEME} - '.__('theme name', 'smsfly')
	];

	$props = [
		['VIBER_site_new_post', __( 'Notification about the publication of a new post', 'smsfly' ), array_merge($base_templates, $post_templates)],
		['VIBER_site_update_post', __( 'Post update notification', 'smsfly' ), array_merge($base_templates, $post_templates)],
		['VIBER_send_new_user_notifications', __( 'Notification about new user registration', 'smsfly' ), array_merge($base_templates, $user_templates)],
		['VIBER_site_user_login', __( 'Notification that the user has logged in to the site', 'smsfly' ), array_merge($base_templates, $user_templates)],
		['VIBER_site_install_plugin', __( 'Notification about installing a new plugin', 'smsfly' ), array_merge($base_templates, $plugin_templates)],
		['VIBER_site_update_plugin', __( 'Plugin update notification', 'smsfly' ), array_merge($base_templates, $plugin_templates)],
		['VIBER_site_install_theme', __( 'Notification about installing a theme on the site', 'smsfly' ), array_merge($base_templates, $themes_templates)],
		['VIBER_site_update_theme', __( 'Topic update notification', 'smsfly' ), array_merge($base_templates, $themes_templates)]
	];
	?>
    <form method="post" action="options.php">
		<?php settings_fields( 'VIBER_SITE_OPTIONS' ); ?>
        <table class="form-table">
            <tr><td colspan="3"><h3><?php _e('Viber parameters', 'smsfly'); ?>:</h3></td></tr>
        </table>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="VIBER_site_phone"><?php _e("Recipient's phone number", 'smsfly'); ?></label></th>
                <td>
                    <input name="VIBER_site_phone" type="text" id="VIBER_site_phone" value="<?php echo get_option('VIBER_site_phone'); ?>" placeholder="<?php echo SMSflyC::inst()->placeholderByRegion(); ?>" class="regular-text">
                </td>
                <td><p class="description"><?php _e("Phone number of the recipient of notification about events on the site, usually the administrator's phone number", 'smsfly'); ?></p></td>
            </tr>
            <tr>
                <th scope="row"><label for="VIBER_site_source"><?php _e('Sender name', 'smsfly'); ?></label></th>
                <td>
                    <?php
                    $names = SMSflyC::inst()->names['viber'];

                    if (!empty($names)) {
                        ?>
                        <select name="VIBER_site_source" id="VIBER_site_source" class="regular-text">
                            <?php
                            foreach ($names as $name) {
                                $selected = (get_option('VIBER_site_source') === $name) ? 'selected' : '';
                                echo "<option value='$name' $selected>$name</option>";
                            }
                            ?>
                        </select>
                        <?php
                    } else {
                        $site_link = SMSflyC::inst()->getSiteLinkToSettingViberNames();
                        echo '<p>' . __('No Viber sender names found in your account. You need to create one, you can do this by following this ', 'smsfly') . '<a href="' . esc_url($site_link) . '" target="_blank">' . __('link', 'smsfly') . '</a>.</p>';
                    }
                    ?>
                </td>
                <td><p class="description"></p></td>
            </tr>
            <tr><td colspan="3"><h3><?php _e('Alert options', 'smsfly'); ?>:</h3></td></tr>
            <tr>
                <th><?php _e('Select alert type', 'smsfly'); ?>:</th>
                <td><select id="VIBER_select">
						<?php
						$inputs = ''; $li = [];
						foreach ($props as $prop) {
							echo "<option value='{$prop[0]}'>{$prop[1]}</option>";
							$checked = checked( '1', get_option($prop[0].'_check') );
							$inputs .= "<input type='checkbox' name='$prop[0]_check' $checked value='1' style='display: none'>";
							$value = get_option($prop[0]);
							$inputs .= "<input type='hidden' name='$prop[0]' value='$value'>";

							$li[$prop[0]] = '';
							foreach ($prop[2] as $template_descr) {
								$li[$prop[0]] .= "<li>$template_descr</li>";
							}
						}
						?>
                    </select></td>
                <td class="description"></td>
            </tr>
            <tr>
                <th><label><?php _e('Activated', 'smsfly'); ?></label></th>
                <td><input type="checkbox" id="VIBER_input" <?php  checked( '1', get_option('VIBER_site_new_post_check') ); ?>></td>
                <td class="description"><?php _e('Enable for selected alert type', 'smsfly'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Message template', 'smsfly'); ?></th>
                <td><textarea rows="4" class="large-text code" id="VIBER_textarea"><?php echo get_option('VIBER_site_new_post'); ?></textarea></td>
                <td class="description"><?php _e('Specify the Viber message template using the tags below', 'smsfly'); ?></td>
            </tr>
            <tr><td colspan="3">
                    <p><?php _e('For each type of notification, you can set your own substitutions', 'smsfly'); ?>:
                    <ul id="viber-templates-description">
						<?php echo $li['VIBER_site_new_post'] ?>
                    </ul>
                    </p>
                </td>
            </tr>
            <script>
                let li = <?php echo json_encode($li); ?>;
                let select = document.getElementById('VIBER_select'), input = document.getElementById('VIBER_input'), textarea = document.getElementById('VIBER_textarea')
                select.addEventListener('change', e => {
                    input.checked = false; textarea.value = ''
                    let prop = e.target.value, prop_input = document.getElementsByName(prop+'_check')[0], prop_textarea = document.getElementsByName(prop)[0]
                    input.checked = prop_input.checked
                    textarea.value = prop_textarea.value

                    let ul = document.getElementById('viber-templates-description')
                    ul.innerHTML = li[prop]
                })

                input.addEventListener('change', e => {
                    let prop_input = document.getElementsByName(select.value+'_check')[0]
                    prop_input.checked = input.checked
                })

                let textareahandler = e => {
                    let prop_textarea = document.getElementsByName(select.value)[0]
                    prop_textarea.value = textarea.value
                }
                textarea.addEventListener('keyup', textareahandler)
                textarea.addEventListener('change', textareahandler)
            </script>
        </table>
	    <?php echo $inputs; submit_button(); ?>
    </form>
	<?php
}
?>
