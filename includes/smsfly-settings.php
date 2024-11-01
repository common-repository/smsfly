<?php
function smsfly_setting_show() {
	$balance = __( 'Your balance SMS-fly', 'smsfly' ).': '.number_format_i18n(SMSflyC::inst()->balance, 2).' '. SMSflyC::inst()->currency .'.';

    if ( !SMSflyC::inst()->auth && !empty(get_option('SMSFLY_apikey'))) {
	    add_settings_error('SMSFly_setting_group', 'settings_updated', __(SMSflyC::inst()->error, 'smsfly'), 'error');
	    settings_errors( 'SMSFly_setting_group' );
    } else if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
	    add_settings_error('SMSFly_setting_group', 'settings_updated', __('Settings saved.'), 'updated');
	    settings_errors( 'SMSFly_setting_group' );
    }

    if ( ! current_user_can( 'manage_options' ) ) {
	    wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );
    }
//
//	if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
//	    add_settings_error('SMSFly_setting_group', 'settings_updated', __('Settings saved.'), 'updated');
//        if ( !SMSflyC::inst()->auth ) add_settings_error('SMSFly_setting_group', 'settings_updated', SMSflyC::inst()->response['error']['code']);
//	    settings_errors( 'SMSFly_setting_group' );
//    }

?>
<div class="wrap">
    <h3><?php echo (SMSflyC::inst()->auth)?$balance:'' ?></h3>
    <form method="post" action="options.php">
		<?php settings_fields( 'SMSFLY_OPTIONS' );?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="SMSFLY_apikey"><?php _e('Your API key on sms-fly', 'smsfly'); ?></label></th>
                <td>
                    <input name="SMSFLY_apikey" type="text" id="SMSFLY_apikey" value="<?php echo get_option('SMSFLY_apikey'); ?>" class="regular-text">
                </td>
            </tr>
        </table>
		<?php submit_button(); ?>
    </form>
</div>
<?php
}
?>