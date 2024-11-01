<?php
function smsfly_checkwc() {
    if (empty(get_option('SMSFLY_apikey'))) {
        wp_die(
            __( 'You did not provide the authorization token. Please add it in the gateway setup' ) .
            ': ' .
            '<a href="admin.php?page=SMSFly_settings">' . __('Gateway setup', 'smsfly') . '</a>'
        );
    }

	if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
		SMSFLY_send_wc_options();
	} else {
		?>
        <div class="wrap">
            <h2><?php _e('Notification settings for WooCommerce', 'smsfly'); ?></h2>
            <h3><?php _e('Woocommerce plugin not installed!!!', 'smsfly'); ?></h3>
        </div>
		<?php
	}
}

function SMSFLY_send_wc_options() {
	if (!current_user_can('manage_options'))
		wp_die(__('You do not have sufficient permissions to manage options for this site.'));

	if (isset($_GET['settings-updated']) && isset($_GET['page'])) {
		add_settings_error('smsfly_wc_options_page_group', 'settings_updated', __('Settings saved.'), 'updated');
		settings_errors('smsfly_wc_options_page_group');
	}

	$props = wc_get_order_statuses();
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sms';
	if ( SMSflyC::inst()->auth ) {
		$formStyle = '';
	} else {
		add_settings_error('smsfly_site_options_page_show_group', 'settings_updated', __(SMSflyC::inst()->error, 'smsfly'), 'error');
		settings_errors( 'smsfly_site_options_page_show_group' );
		$formStyle = 'display: none';
	}
	?>

    <div class="wrap">
        <h2><?php _e('Notification settings for WooCommerce', 'smsfly'); ?></h2>
        <h2 class="nav-tab-wrapper" style="<?php echo $formStyle; ?>">
            <a href="?page=SMSFly_woo&tab=sms" class="nav-tab <?php echo $active_tab == 'sms' ? 'nav-tab-active' : ''; ?>"><?php _e('SMS Settings', 'smsfly'); ?></a>
            <a href="?page=SMSFly_woo&tab=viber" class="nav-tab <?php echo $active_tab == 'viber' ? 'nav-tab-active' : ''; ?>"><?php _e('Viber Settings', 'smsfly'); ?></a>
        </h2>

		<?php
		if ($active_tab == 'sms') {
			?>
            <form method="post" action="options.php" style="<?php echo $formStyle; ?>">
				<?php settings_fields('smsfly_wc_options_page_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="SMSFLY_WC_CHECK"><?php _e('Activate SMS for WC', 'smsfly'); ?></label></th>
                        <td>
                            <input name="SMSFLY_WC_CHECK" type="checkbox" id="SMSFLY_WC_CHECK" <?php checked('1', get_option('SMSFLY_WC_CHECK')); ?> value="1">
                        </td>
                        <td><p class="description"><?php _e('Enable or disable SMS for WooCommerce', 'smsfly'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="SMSFLY_wc_phone"><?php _e("Administrator's phone number", 'smsfly'); ?></label></th>
                        <td>
                            <input name="SMSFLY_wc_phone" type="text" id="SMSFLY_wc_phone" value="<?php echo get_option('SMSFLY_wc_phone'); ?>" placeholder="<?php echo SMSflyC::inst()->placeholderByRegion(); ?>" class="regular-text">
                        </td>
                        <td><p class="description"><?php _e('WooCommerce order manager phone number', 'smsfly'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="SMSFLY_name_site_send"><?php _e('Sender name', 'smsfly'); ?></label></th>
                        <td>
                            <select name="SMSFLY_name_wc_send" id="SMSFLY_name_wc_send" class="regular-text">
								<?php
								$names = SMSflyC::inst()->names['sms'];
								foreach ($names as $name) {
									$selected = (get_option('SMSFLY_name_wc_send') === $name) ? 'selected' : '';
									echo "<option value='$name' $selected>$name</option>";
								}
								?>
                            </select>
                        </td>
                        <td><p class="description"></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="SMSFLY_to_lat_wc"><?php _e('Conversion to Latin', 'smsfly'); ?></label></th>
                        <td>
                            <input name="SMSFLY_to_lat_wc" type="checkbox" id="SMSFLY_to_lat_wc" <?php checked('1', get_option('SMSFLY_to_lat_wc')); ?> value="1">
                        </td>
                        <td><p class="description"><?php _e('Enable conversion of Cyrillic characters to Latin', 'smsfly'); ?></p></td>
                    </tr>
                    <tr>
                        <th colspan="3"><h2><?php _e('Setting up order statuses', 'smsfly'); ?></h2></th>
                    </tr>
                    <tr>
                        <th><?php _e('Woocommerce status', 'smsfly'); ?>:</th>
                        <td><select id="SMSFLY_select">
								<?php
								$inputs = '';
								foreach ($props as $value => $text) {
									echo "<option value='$value'>$text</option>";
									$prop_admin = 'SMSFLY_wc_admin_' . $value;
									$prop_client = 'SMSFLY_wc_client_' . $value;
									$checked_admin = checked('1', get_option($prop_admin . '_check'));
									$checked_client = checked('1', get_option($prop_client . '_check'));
									$inputs .= "<input type='checkbox' name='{$prop_admin}_check' $checked_admin value='1' style='display: none'>";
									$inputs .= "<input type='checkbox' name='{$prop_client}_check' $checked_client value='1' style='display: none'>";
									$template_admin = get_option($prop_admin);
									$template_client = get_option($prop_client);
									$inputs .= "<input type='hidden' name='$prop_admin' value='$template_admin'>";
									$inputs .= "<input type='hidden' name='$prop_client' value='$template_client'>";
								}
								reset($props);
								?>
                            </select></td>
                        <td class="description"></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('SMS to administrator', 'smsfly'); ?></label></th>
                        <td><input type="checkbox" id="SMSFLY_input_admin" <?php checked('1', get_option('SMSFLY_wc_admin_' . key($props) . '_check')); ?>></td>
                        <td class="description"><?php _e('Enable SMS notification for administrator', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Admin SMS Template', 'smsfly'); ?></th>
                        <td><textarea rows="4" class="large-text code" id="SMSFLY_textarea_admin"><?php echo get_option('SMSFLY_wc_admin_' . key($props)); ?></textarea></td>
                        <td class="description"><?php _e('Specify the text of the SMS template corresponding to the selected status', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('SMS to client', 'smsfly'); ?></label></th>
                        <td><input type="checkbox" id="SMSFLY_input_client" <?php checked('1', get_option('SMSFLY_wc_client_' . key($props) . '_check')); ?>></td>
                        <td class="description"><?php _e('Enable SMS notification to client', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Client SMS template', 'smsfly'); ?></th>
                        <td><textarea rows="4" class="large-text code" id="SMSFLY_textarea_client"><?php echo get_option('SMSFLY_wc_client_' . key($props)); ?></textarea></td>
                        <td class="description"><?php _e('Specify the text of the SMS template corresponding to the selected status', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <p><?php _e('You can use substitutions from order data for templates', 'smsfly'); ?>:
                            <ul>
                                <li>{NUM} - <?php _e('order number', 'smsfly'); ?></li>
                                <li>{SUM} - <?php _e('order price', 'smsfly'); ?></li>
                                <li>{EMAIL} - <?php _e('Client\'s email', 'smsfly'); ?></li>
                                <li>{PHONE} - <?php _e('Client phone number', 'smsfly'); ?></li>
                                <li>{FIRSTNAME} - <?php _e('Client name', 'smsfly'); ?></li>
                                <li>{LASTNAME} - <?php _e('Client\'s last name', 'smsfly'); ?></li>
                                <li>{CITY} - <?php _e('Client city', 'smsfly'); ?></li>
                                <li>{ADDRESS} - <?php _e('Client address', 'smsfly'); ?></li>
                                <li>{BLOGNAME} - <?php _e('Name of shop', 'smsfly'); ?></li>
                                <li>{OLD_STATUS} - <?php _e('Old status', 'smsfly'); ?></li>
                                <li>{NEW_STATUS} - <?php _e('New status', 'smsfly'); ?></li>
                                <li>{DATE} - <?php _e('Event date', 'smsfly'); ?></li>
                                <li>{TIME} - <?php _e('Event time', 'smsfly'); ?></li>
                            </ul>
                            </p>
                        </td>
                    </tr>
                </table>
                <script>
                    let select = document.getElementById('SMSFLY_select'),
                        input_admin = document.getElementById('SMSFLY_input_admin'),
                        input_client = document.getElementById('SMSFLY_input_client'),
                        textarea_admin = document.getElementById('SMSFLY_textarea_admin'),
                        textarea_client = document.getElementById('SMSFLY_textarea_client');

                    select.addEventListener('change', e => {
                        input_admin.checked = false;
                        textarea_admin.value = '';
                        input_client.checked = false;
                        textarea_client.value = '';
                        let prop = e.target.value,
                            prop_input_admin = document.getElementsByName(`SMSFLY_wc_admin_${prop}_check`)[0],
                            prop_textarea_admin = document.getElementsByName(`SMSFLY_wc_admin_${prop}`)[0],
                            prop_input_client = document.getElementsByName(`SMSFLY_wc_client_${prop}_check`)[0],
                            prop_textarea_client = document.getElementsByName(`SMSFLY_wc_client_${prop}`)[0];

                        input_admin.checked = prop_input_admin.checked;
                        textarea_admin.value = prop_textarea_admin.value;
                        input_client.checked = prop_input_client.checked;
                        textarea_client.value = prop_textarea_client.value;
                    });

                    input_admin.addEventListener('change', e => {
                        let prop_input = document.getElementsByName(`SMSFLY_wc_admin_${select.value}_check`)[0];
                        prop_input.checked = input_admin.checked;
                    });

                    input_client.addEventListener('change', e => {
                        let prop_input = document.getElementsByName(`SMSFLY_wc_client_${select.value}_check`)[0];
                        prop_input.checked = input_client.checked;
                    });

                    let admin_textareahandler = e => {
                            let prop_textarea = document.getElementsByName(`SMSFLY_wc_admin_${select.value}`)[0];
                            prop_textarea.value = textarea_admin.value;
                        },
                        client_textareahandler = e => {
                            let prop_textarea = document.getElementsByName(`SMSFLY_wc_client_${select.value}`)[0];
                            prop_textarea.value = textarea_client.value;
                        };

                    textarea_admin.addEventListener('keyup', admin_textareahandler);
                    textarea_admin.addEventListener('change', admin_textareahandler);
                    textarea_client.addEventListener('keyup', client_textareahandler);
                    textarea_client.addEventListener('change', client_textareahandler);
                </script>
				<?php echo $inputs; submit_button(); ?>
            </form>
			<?php
		} else {
			?>
            <form method="post" action="options.php" style="<?php echo $formStyle; ?>">
				<?php settings_fields('smsfly_viber_options_page_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="VIBER_WC_CHECK"><?php _e('Activate Viber for WC', 'smsfly'); ?></label></th>
                        <td>
                            <input name="VIBER_WC_CHECK" type="checkbox" id="VIBER_WC_CHECK" <?php checked('1', get_option('VIBER_WC_CHECK')); ?> value="1">
                        </td>
                        <td><p class="description"><?php _e('Enable or disable Viber for WooCommerce', 'smsfly'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="VIBER_wc_phone"><?php _e("Administrator's phone number", 'smsfly'); ?></label></th>
                        <td>
                            <input name="VIBER_wc_phone" type="text" id="VIBER_wc_phone" value="<?php echo get_option('VIBER_wc_phone'); ?>" placeholder="<?php echo SMSflyC::inst()->placeholderByRegion(); ?>" class="regular-text">
                        </td>
                        <td><p class="description"><?php _e('WooCommerce order manager phone number', 'smsfly'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="VIBER_name_site_send"><?php _e('Sender name', 'smsfly'); ?></label></th>
                        <td>
                            <?php
                            $names = SMSflyC::inst()->names['viber'];
                            if (!empty($names)) {
                                ?>
                                <select name="VIBER_name_wc_send" id="VIBER_name_wc_send" class="regular-text">
                                    <?php
                                    foreach ($names as $name) {
                                        $selected = (get_option('VIBER_name_wc_send') === $name) ? 'selected' : '';
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
                    <tr>
                        <th colspan="3"><h2><?php _e('Setting up order statuses', 'smsfly'); ?></h2></th>
                    </tr>
                    <tr>
                        <th><?php _e('Woocommerce status', 'smsfly'); ?>:</th>
                        <td><select id="VIBER_select">
								<?php
								$inputs = '';
								foreach ($props as $value => $text) {
									echo "<option value='$value'>$text</option>";
									$prop_admin = 'VIBER_wc_admin_' . $value;
									$prop_client = 'VIBER_wc_client_' . $value;
									$checked_admin = checked('1', get_option($prop_admin . '_check'));
									$checked_client = checked('1', get_option($prop_client . '_check'));
									$inputs .= "<input type='checkbox' name='{$prop_admin}_check' $checked_admin value='1' style='display: none'>";
									$inputs .= "<input type='checkbox' name='{$prop_client}_check' $checked_client value='1' style='display: none'>";
									$template_admin = get_option($prop_admin);
									$template_client = get_option($prop_client);
									$inputs .= "<input type='hidden' name='$prop_admin' value='$template_admin'>";
									$inputs .= "<input type='hidden' name='$prop_client' value='$template_client'>";
								}
								reset($props);
								?>
                            </select></td>
                        <td class="description"></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Viber to administrator', 'smsfly'); ?></label></th>
                        <td><input type="checkbox" id="VIBER_input_admin" <?php checked('1', get_option('VIBER_wc_admin_' . key($props) . '_check')); ?>></td>
                        <td class="description"><?php _e('Enable Viber notification for administrator', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Admin Viber Template', 'smsfly'); ?></th>
                        <td><textarea rows="4" class="large-text code" id="VIBER_textarea_admin"><?php echo get_option('VIBER_wc_admin_' . key($props)); ?></textarea></td>
                        <td class="description"><?php _e('Specify the text of the Viber template corresponding to the selected status', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Viber to client', 'smsfly'); ?></label></th>
                        <td><input type="checkbox" id="VIBER_input_client" <?php checked('1', get_option('VIBER_wc_client_' . key($props) . '_check')); ?>></td>
                        <td class="description"><?php _e('Enable Viber notification to client', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Client Viber template', 'smsfly'); ?></th>
                        <td><textarea rows="4" class="large-text code" id="VIBER_textarea_client"><?php echo get_option('VIBER_wc_client_' . key($props)); ?></textarea></td>
                        <td class="description"><?php _e('Specify the text of the Viber template corresponding to the selected status', 'smsfly'); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <p><?php _e('You can use substitutions from order data for templates', 'smsfly'); ?>:
                            <ul>
                                <li>{NUM} - <?php _e('order number', 'smsfly'); ?></li>
                                <li>{SUM} - <?php _e('order price', 'smsfly'); ?></li>
                                <li>{EMAIL} - <?php _e('Client\'s email', 'smsfly'); ?></li>
                                <li>{PHONE} - <?php _e('Client phone number', 'smsfly'); ?></li>
                                <li>{FIRSTNAME} - <?php _e('Client name', 'smsfly'); ?></li>
                                <li>{LASTNAME} - <?php _e('Client\'s last name', 'smsfly'); ?></li>
                                <li>{CITY} - <?php _e('Client city', 'smsfly'); ?></li>
                                <li>{ADDRESS} - <?php _e('Client address', 'smsfly'); ?></li>
                                <li>{BLOGNAME} - <?php _e('Name of shop', 'smsfly'); ?></li>
                                <li>{OLD_STATUS} - <?php _e('Old status', 'smsfly'); ?></li>
                                <li>{NEW_STATUS} - <?php _e('New status', 'smsfly'); ?></li>
                                <li>{DATE} - <?php _e('Event date', 'smsfly'); ?></li>
                                <li>{TIME} - <?php _e('Event time', 'smsfly'); ?></li>
                            </ul>
                            </p>
                        </td>
                    </tr>
                </table>
                <script>
                    let select = document.getElementById('VIBER_select'),
                        input_admin = document.getElementById('VIBER_input_admin'),
                        input_client = document.getElementById('VIBER_input_client'),
                        textarea_admin = document.getElementById('VIBER_textarea_admin'),
                        textarea_client = document.getElementById('VIBER_textarea_client');

                    select.addEventListener('change', e => {
                        input_admin.checked = false;
                        textarea_admin.value = '';
                        input_client.checked = false;
                        textarea_client.value = '';
                        let prop = e.target.value,
                            prop_input_admin = document.getElementsByName(`VIBER_wc_admin_${prop}_check`)[0],
                            prop_textarea_admin = document.getElementsByName(`VIBER_wc_admin_${prop}`)[0],
                            prop_input_client = document.getElementsByName(`VIBER_wc_client_${prop}_check`)[0],
                            prop_textarea_client = document.getElementsByName(`VIBER_wc_client_${prop}`)[0];

                        input_admin.checked = prop_input_admin.checked;
                        textarea_admin.value = prop_textarea_admin.value;
                        input_client.checked = prop_input_client.checked;
                        textarea_client.value = prop_textarea_client.value;
                    });

                    input_admin.addEventListener('change', e => {
                        let prop_input = document.getElementsByName(`VIBER_wc_admin_${select.value}_check`)[0];
                        prop_input.checked = input_admin.checked;
                    });

                    input_client.addEventListener('change', e => {
                        let prop_input = document.getElementsByName(`VIBER_wc_client_${select.value}_check`)[0];
                        prop_input.checked = input_client.checked;
                    });

                    let admin_textareahandler = e => {
                            let prop_textarea = document.getElementsByName(`VIBER_wc_admin_${select.value}`)[0];
                            prop_textarea.value = textarea_admin.value;
                        },
                        client_textareahandler = e => {
                            let prop_textarea = document.getElementsByName(`VIBER_wc_client_${select.value}`)[0];
                            prop_textarea.value = textarea_client.value;
                        };

                    textarea_admin.addEventListener('keyup', admin_textareahandler);
                    textarea_admin.addEventListener('change', admin_textareahandler);
                    textarea_client.addEventListener('keyup', client_textareahandler);
                    textarea_client.addEventListener('change', client_textareahandler);
                </script>
				<?php echo $inputs; submit_button(); ?>
            </form>
			<?php
		}
		?>
    </div>
    <?php do_action('SMSFLY_send_wc_options');
}
?>