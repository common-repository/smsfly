<?php
function smsfly_sms_show() {
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
		try {
			$method = get_option('SMSFLY_SMS_METHOD');
			$smsSender = get_option('SMSFLY_SMS_SOURCE');
			$viberSender = get_option('SMSFLY_VIBER_SOURCE');
			$phone = get_option('SMSFLY_SMS_PHONE');
			$text = get_option('SMSFLY_SMS_TEXT');

			if ($method == 'SMS') {
                SMSflyC::inst()->typeMessage = 'Manually SMS';
				SMSflyC::sendToFly($smsSender, $phone, $text);
			}

			if ($method == 'Viber') {
                SMSflyC::inst()->typeMessage = 'Manually Viber';
				SMSflyC::inst()->setSourceViber($viberSender);
				SMSflyC::inst()->sendViber(['phone' => $phone, 'text' => $text]);
			}

			if ($method == 'Viber+SMS') {
                SMSflyC::inst()->typeMessage = 'Manually Viber+SMS';
				SMSflyC::inst()::inst()->setSources($smsSender);
				SMSflyC::inst()->setSourceViber($viberSender);
				SMSflyC::inst()->sendMixedSMS($phone, $text);
			}

            if ( SMSflyC::inst()->error ) throw new Exception(SMSflyC::inst()->error);

            $notify = 'Success send';
			$type = 'updated';
		} catch (Exception $e) {
            $notify = $e->getMessage();
            $type = 'error';
        } finally {
			add_settings_error( 'smsfly_sms_options_page_group', 'settings_updated', __( $notify, 'smsfly' ), $type );
			settings_errors( 'smsfly_sms_options_page_group' );
		}
	}?>
	<div class="wrap">
		<h3><?php _e('Manual sending of SMS messages', 'smsfly'); ?></h3>
		<?php
		$names = SMSflyC::inst()->names['sms'];

		if ( SMSflyC::inst()->auth ) {
			$formStyle = '';
		} else {
			add_settings_error('smsfly_site_options_page_show_group', 'settings_updated', __(SMSflyC::inst()->error, 'smsfly'), 'error');
			settings_errors( 'smsfly_site_options_page_show_group' );
			$formStyle = 'display: none';
		}
		?>
        <form method="post" action="options.php" style="<?php echo $formStyle;?>">
			<?php settings_fields( 'smsfly_sms_options_page_group' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="SMSFLY_SMS_SOURCE"><?php _e('Sender name', 'smsfly'); ?></label></th>
                    <td>
                        <select name="SMSFLY_SMS_SOURCE" id="SMSFLY_SMS_SOURCE" class="regular-text">
							<?php
							foreach ( $names as $name ) {
								$selected = (get_option('SMSFLY_SMS_SOURCE') === $name) ? 'selected':'';
								echo "<option value='$name' $selected>$name</option>";
							}
							?>
                        </select>
                    </td>
                    <td><p class="description"><?php _e('Enter the sender name for SMS', 'smsfly'); ?></p></td>
                </tr>
                <tr>
                    <th><label for="SMSFLY_VIBER_SOURCE"><?php _e('Viber sender name', 'smsfly'); ?></label></th>
                    <td>
                        <?php
                        $names = SMSflyC::inst()->names['viber'];

                        if (!empty($names)) {
                            ?>
                            <select name="SMSFLY_VIBER_SOURCE" id="SMSFLY_VIBER_SOURCE" class="regular-text">
                                <?php
                                foreach ($names as $name) {
                                    $selected = (get_option('SMSFLY_VIBER_SOURCE') === $name) ? 'selected' : '';
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
                    <td><p class="description"><?php _e('Enter the sender name for Viber', 'smsfly'); ?></p></td>
                </tr>
                <tr>
                    <th><label for="SMSFLY_SMS_PHONE"><?php _e('Recipient number', 'smsfly'); ?>:</label></th>
                    <td><input type="text" id="SMSFLY_SMS_PHONE" name="SMSFLY_SMS_PHONE" placeholder="<?php echo SMSflyC::inst()->placeholderByRegion(); ?>" value="" required></td>
                    <td><p class="description"> <?php _e("Enter the recipient's number in the format of the recipient's country", 'smsfly'); ?></p></td>
				</tr>
				<tr>
					<th><label for="SMSFLY_SMS_TEXT"><?php _e('Message', 'smsfly'); ?></label></th>
					<td><textarea id="SMSFLY_SMS_TEXT" name="SMSFLY_SMS_TEXT" class="large-text code"><?php echo get_option('SMSFLY_SMS_SAVE')?get_option('SMSFLY_SMS_TEXT'):'';?></textarea></td>
					<td><p class="description"> <?php _e('The message text cannot be empty. One message up to 70 Cyrillic or 160 Latin characters', 'smsfly'); ?>.</p></td>
				</tr>
                <tr>
                    <th><label for="SMSFLY_SMS_SAVE"><?php _e('Save text', 'smsfly'); ?></label></th>
                    <td><input name="SMSFLY_SMS_SAVE" type="checkbox" id="SMSFLY_SMS_SAVE" <?php  checked( '1', get_option('SMSFLY_SMS_SAVE') ); ?> value="1"></td>
                    <td><p class="description"><?php _e('Do not clear the "Message text" field when refreshing the page', 'smsfly'); ?></p></td>
                </tr>
                <tr>
                    <th><label for="SMSFLY_SMS_METHOD"><?php _e('Send method', 'smsfly'); ?></label></th>
                    <td>
                        <select name="SMSFLY_SMS_METHOD" id="SMSFLY_SMS_METHOD" class="regular-text">
                            <option value="SMS" <?php selected(get_option('SMSFLY_SMS_METHOD'), 'SMS'); ?>><?php _e('SMS', 'smsfly'); ?></option>
                            <option value="Viber" <?php selected(get_option('SMSFLY_SMS_METHOD'), 'Viber'); ?>><?php _e('Viber', 'smsfly'); ?></option>
                            <option value="Viber+SMS" <?php selected(get_option('SMSFLY_SMS_METHOD'), 'Viber+SMS'); ?>><?php _e('Viber + SMS', 'smsfly'); ?></option>
                        </select>
                    </td>
                    <td><p class="description"><?php _e('Select the method of sending the message', 'smsfly'); ?></p></td>
                </tr>
            </table>
			<?php submit_button( __( 'Send a message', 'smsfly' ));?>
		</form>
	</div>
	<?php
}