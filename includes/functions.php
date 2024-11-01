<?php

include_once(SMSFLY_DIR . 'includes/logs.php');

if ( get_option( 'SMSFLY_site_new_post_check' ) || get_option( 'VIBER_site_new_post_check' ) ) {
	function smsfly_published_post ( $new_status, $old_status, $post ) {
		if ( ($new_status === 'publish') && ($old_status !== 'publish') && ($post->post_type == 'post') ) {
			$authname = get_user_by('id', $post->post_author);
			$search = ['{USER}', '{POSTID}', '{POSTTITLE}', '{DATE}', '{TIME}', '{SITE}'];
			$replace = [$authname->user_login, $post->ID, $post->post_title, gmdate("d.m.Y"), gmdate("H:i"), $authname->user_url];

			$sms_msg = str_replace($search, $replace, get_option('SMSFLY_site_new_post'));
			$sms_msg = (get_option('SMSFLY_site_to_lat') == 1) ? SMSflyC::translit($sms_msg) : $sms_msg;

			$viber_msg = str_replace($search, $replace, get_option('VIBER_site_new_post'));

			if ( get_option('SMSFLY_site_new_post_check') == 1 ) {
                SMSflyC::inst()->typeMessage = 'New Post (SMS)';
				SMSflyC::sendToFly(get_option('SMSFLY_SMS_SOURCE'), get_option('SMSFLY_site_phone'), $sms_msg);
			}

			if ( get_option('VIBER_site_new_post_check') == 1 ) {
                SMSflyC::inst()->typeMessage = 'New Post (Viber)';
				SMSflyC::inst()->setSourceViber(get_option('VIBER_site_source'));
				SMSflyC::inst()->sendViber(['phone' => get_option('VIBER_site_phone'), 'text' => $viber_msg]);
			}
		}
	}
	add_action( 'transition_post_status', 'smsfly_published_post', 10, 3 );
}

if ( get_option( 'SMSFLY_site_update_post_check' ) || get_option( 'VIBER_site_update_post_check' ) ) {
	function smsfly_post_update( $post_ID, $post_after, $post_before ) {
		if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) ||
		     $post_before->post_status == 'auto-draft' || $post_before->post_type != 'post' ) {
			return;
		} else {
			$postinfo = get_post($post_ID);
			$authname = get_user_by('id', $postinfo->post_author);
			$search = ['{USER}', '{POSTID}', '{POSTTITLE}', '{DATE}', '{TIME}', '{SITE}'];
			$replace = [$authname->user_login, $post_ID, $post_before->post_title, gmdate("d.m.Y"), gmdate("H:i"), $authname->user_url];

			$sms_msg = str_replace($search, $replace, get_option('SMSFLY_site_update_post'));
			$sms_msg = (get_option('SMSFLY_site_to_lat') == 1) ? SMSflyC::translit($sms_msg) : $sms_msg;

			$viber_msg = str_replace($search, $replace, get_option('VIBER_site_update_post'));

			if ( get_option('SMSFLY_site_update_post_check') == 1 ) {
                SMSflyC::inst()->typeMessage = 'Update Post (SMS)';
				SMSflyC::sendToFly(get_option('SMSFLY_site_source'), get_option('SMSFLY_site_phone'), $sms_msg );
			}

			if ( get_option('VIBER_site_update_post_check') == 1 ) {
                SMSflyC::inst()->typeMessage = 'Update Post (Viber)';
				SMSflyC::inst()->setSourceViber(get_option('VIBER_site_source'));
				SMSflyC::inst()->sendViber(['phone' => get_option('VIBER_site_phone'), 'text' => $viber_msg]);
			}
		}
	}
	add_action( 'post_updated', 'smsfly_post_update', 10, 3 );
}

if ( get_option( 'SMSFLY_send_new_user_notifications_check' ) || get_option( 'VIBER_send_new_user_notifications_check' ) ) {
	function smsfly_send_new_user_notifications( $user_id ) {
		$authname = get_user_by('id', $user_id);

		$search = ['{USER}', '{DATE}', '{TIME}', '{SITE}', '{EMAIL}', '{IP}'];
		$replace = [$authname->user_login, gmdate("d.m.Y"), gmdate("H:i"), $authname->user_url, $authname->user_email, $_SERVER['REMOTE_ADDR']];

		$sms_msg = str_replace($search, $replace, get_option('SMSFLY_send_new_user_notifications'));
		$sms_msg = (get_option('SMSFLY_site_to_lat') == 1) ? SMSflyC::translit($sms_msg) : $sms_msg;

		$viber_msg = str_replace($search, $replace, get_option('VIBER_send_new_user_notifications'));

		if ( get_option('SMSFLY_send_new_user_notifications_check') == 1 ) {
            SMSflyC::inst()->typeMessage = 'New User (SMS)';
			SMSflyC::sendToFly(get_option('SMSFLY_site_source'), get_option('SMSFLY_site_phone'), $sms_msg );
		}

		if ( get_option('VIBER_send_new_user_notifications_check') == 1 ) {
            SMSflyC::inst()->typeMessage = 'New User (Viber)';
			SMSflyC::inst()->setSourceViber(get_option('VIBER_site_source'));
			SMSflyC::inst()->sendViber(['phone' => get_option('VIBER_site_phone'), 'text' => $viber_msg]);
		}
	}
	add_action( 'register_new_user', 'smsfly_send_new_user_notifications', 10, 1 );
}

if ( get_option( 'SMSFLY_site_user_login_check' ) || get_option( 'VIBER_site_user_login_check' ) ) {
	function smsfly_user_login( $user_login, $user ) {
		$search = ['{USER}', '{DATE}', '{TIME}', '{SITE}', '{EMAIL}', '{IP}'];
		$replace = [$user_login, gmdate("d.m.Y"), gmdate("H:i"), $user->user_url, $user->user_email, $_SERVER['REMOTE_ADDR']];

		$sms_msg = str_replace($search, $replace, get_option('SMSFLY_site_user_login'));
		$sms_msg = (get_option('SMSFLY_site_to_lat') == 1) ? SMSflyC::translit($sms_msg) : $sms_msg;

		$viber_msg = str_replace($search, $replace, get_option('VIBER_site_user_login'));

		if ( get_option('SMSFLY_site_user_login_check') == 1 ) {
            SMSflyC::inst()->typeMessage = 'User Login (SMS)';
			SMSflyC::sendToFly(get_option('SMSFLY_site_source'), get_option('SMSFLY_site_phone'), $sms_msg );
		}

		if ( get_option('VIBER_site_user_login_check') == 1 ) {
            SMSflyC::inst()->typeMessage = 'User Login (Viber)';
			SMSflyC::inst()->setSourceViber(get_option('VIBER_site_source'));
			SMSflyC::inst()->sendViber(['phone' => get_option('VIBER_site_phone'), 'text' => $viber_msg]);
		}
	}
	add_action('wp_login', 'smsfly_user_login', 10, 2);
}
if ( get_option( 'SMSFLY_site_install_plugin_check' ) || get_option( 'SMSFLY_site_update_plugin_check' ) ||
     get_option( 'SMSFLY_site_install_theme_check' ) || get_option( 'SMSFLY_site_update_theme_check' ) ||
     get_option( 'VIBER_site_install_plugin_check' ) || get_option( 'VIBER_site_update_plugin_check' ) ||
     get_option( 'VIBER_site_install_theme_check' ) || get_option( 'VIBER_site_update_theme_check' ) ) {

	function smsfly_upgrader_post_install ($response, $hook_extra, $result) {
		if ( !isset($hook_extra['type']) && !isset($hook_extra['theme']) && !isset($hook_extra['plugin']) ) return;

		if ( isset($hook_extra['type']) ) {
			$option_sms = 'SMSFLY_site_install_'.$hook_extra['type'];
			$option_viber = 'VIBER_site_install_'.$hook_extra['type'];
		} else {
			$option_sms = 'SMSFLY_site_update_'.(( isset($hook_extra['theme']) ) ? 'theme' : 'plugin');
			$option_viber = 'VIBER_site_update_'.(( isset($hook_extra['theme']) ) ? 'theme' : 'plugin');
		}

		if ( get_option( $option_sms.'_check') != 1 && get_option( $option_viber.'_check') != 1 ) return;

		$cuser = wp_get_current_user();
		$search = ['{USER}', '{PLUGIN}', '{DATE}', '{TIME}', '{THEME}'];
		$replace = [$cuser->user_login, $result['destination_name'], gmdate("d.m.Y"), gmdate("H:i"), $result['destination_name']];

		$sms_msg = str_replace($search, $replace, get_option($option_sms));
		$sms_msg = (get_option('SMSFLY_site_to_lat') == 1) ? SMSflyC::translit($sms_msg) : $sms_msg;

		$viber_msg = str_replace($search, $replace, get_option($option_viber));

		if ( get_option($option_sms.'_check') == 1 ) {
            SMSflyC::inst()->typeMessage = 'Theme Install (SMS)';
			SMSflyC::sendToFly(get_option('SMSFLY_site_source'), get_option('SMSFLY_site_phone'), $sms_msg);
		}

		if ( get_option($option_viber.'_check') == 1 ) {
            SMSflyC::inst()->typeMessage = 'Theme Install (Viber)';
			SMSflyC::inst()->setSourceViber(get_option('VIBER_site_source'));
			SMSflyC::inst()->sendViber(['phone' => get_option('VIBER_site_phone'), 'text' => $viber_msg]);
		}
	}

	add_action( 'upgrader_post_install', 'smsfly_upgrader_post_install', 10, 3 );
}

if ( get_option( 'SMSFLY_WC_CHECK' ) || get_option( 'VIBER_WC_CHECK' ) ) {
	function change_status_client($order_id, $old_status, $new_status, $that) {
		$sms_admin = get_option('SMSFLY_wc_admin_wc-'.$new_status.'_check');
		$sms_adminTemplate = get_option('SMSFLY_wc_admin_wc-'.$new_status);
		$sms_client = get_option('SMSFLY_wc_client_wc-'.$new_status.'_check');
		$sms_clientTemplate = get_option('SMSFLY_wc_client_wc-'.$new_status);

		$viber_admin = get_option('VIBER_wc_admin_wc-'.$new_status.'_check');
		$viber_adminTemplate = get_option('VIBER_wc_admin_wc-'.$new_status);
		$viber_client = get_option('VIBER_wc_client_wc-'.$new_status.'_check');
		$viber_clientTemplate = get_option('VIBER_wc_client_wc-'.$new_status);

		$search = ['{NUM}', '{SUM}', '{EMAIL}', '{PHONE}', '{FIRSTNAME}', '{LASTNAME}', '{CITY}', '{ADDRESS}', '{BLOGNAME}', '{OLD_STATUS}', '{NEW_STATUS}', '{DATE}', '{TIME}'];
		$replace = [
			$order_id,
			html_entity_decode(wp_strip_all_tags($that->get_formatted_order_total('', false))),
			$that->get_billing_email(),
			$that->get_billing_phone(),
			$that->get_billing_first_name(),
			$that->get_billing_last_name(),
			empty($that->get_shipping_city()) ? $that->get_billing_city() : $that->get_shipping_city(),
			trim(empty($that->get_shipping_address_1()) ? $that->get_billing_address_1() . " " . $that->get_billing_address_2() : $that->get_shipping_address_1() . " " . $that->get_shipping_address_2()),
			get_option('blogname'),
			wc_get_order_status_name($old_status),
			wc_get_order_status_name($new_status),
			gmdate("d.m.Y"),
			gmdate("H:i")
		];

		$sms_admin_msg = str_replace($search, $replace, $sms_adminTemplate);
		$sms_admin_msg = (get_option('SMSFLY_to_lat_wc') == 1) ? SMSflyC::translit($sms_admin_msg) : $sms_admin_msg;
		$sms_client_msg = str_replace($search, $replace, $sms_clientTemplate);
		$sms_client_msg = (get_option('SMSFLY_to_lat_wc') == 1) ? SMSflyC::translit($sms_client_msg) : $sms_client_msg;

		$viber_admin_msg = str_replace($search, $replace, $viber_adminTemplate);
		$viber_client_msg = str_replace($search, $replace, $viber_clientTemplate);

		// Отправка через SMS
		if ( $sms_admin == 1 ) {
            SMSflyC::inst()->typeMessage = 'WC Admin (SMS)';
			SMSflyC::sendToFly(get_option('SMSFLY_name_wc_send'), get_option('SMSFLY_wc_phone'), $sms_admin_msg);
		}

		if ( $sms_client == 1 ) {
            SMSflyC::inst()->typeMessage = 'WC Client (SMS)';
			SMSflyC::sendToFly(get_option('SMSFLY_name_wc_send'), $that->get_billing_phone(), $sms_client_msg);
		}

		// Отправка через Viber
		if ( $viber_admin == 1 ) {
            SMSflyC::inst()->typeMessage = 'WC Admin (Viber)';
			$viberSender = get_option('VIBER_name_wc_send');
			$modelSMSflyC = SMSflyC::inst();
			$modelSMSflyC->setSourceViber($viberSender);
			$modelSMSflyC->sendViber(['phone' => get_option('VIBER_wc_phone'), 'text' => $viber_admin_msg]);
		}

		if ( $viber_client == 1 ) {
            SMSflyC::inst()->typeMessage = 'WC Client (Viber)';
			$viberSender = get_option('VIBER_name_wc_send');
			SMSflyC::inst()->setSourceViber($viberSender);
			SMSflyC::inst()->sendViber(['phone' => $that->get_billing_phone(), 'text' => $viber_client_msg]);
		}
	}
	add_action('woocommerce_order_status_changed', 'change_status_client', 10, 4);
}
if ( get_option('SMSFLY_cf7_onsubmit') || get_option('VIBER_cf7_onsubmit') ) {
	function sendtest($contactform, $result) {
		$errors = ['validation_failed'];
		if ( in_array($result['status'], $errors) ) return;
		$submission = WPCF7_Submission::get_instance();
		if ( $submission ) {
			$posted_data = $submission->get_posted_data();

			$sms_to = get_option('SMSFLY_cf7_phone');
			$sms_template = get_option('SMSFLY_cf7_onsubmit_msg');
			$sms_tolat = get_option('SMSFLY_cf7_to_lat') == 1;

			$viber_to = get_option('VIBER_cf7_phone');
			$viber_template = get_option('VIBER_cf7_onsubmit_msg');

			$fulltext = implode(';', $posted_data);

			$sms_fulltext = $sms_tolat ? SMSflyC::translit($fulltext) : $fulltext;
			$viber_fulltext = $fulltext;

			$shortcodes = $replace = [];
			preg_match_all('/\[.+\]/U', $sms_template, $shortcodes);
			foreach ($shortcodes[0] as $shortcode) {
				switch ($shortcode) {
					case '[DATE]': $replace[] = gmdate("d.m.Y"); break;
					case '[TIME]': $replace[] = gmdate("H:i"); break;
					case '[FULL]':
						$sms_replace[] = $sms_fulltext;
						$viber_replace[] = $viber_fulltext;
						break;
					case '[SHORT]':
						$sms_replace[] = mb_substr($sms_fulltext, 0, ($sms_tolat ? 140 : 65));
						$viber_replace[] = mb_substr($viber_fulltext, 0, 65);
						break;
					default:
						$code = str_replace(['[',']'], '', $shortcode);
						$sms_replace[] = $posted_data[ $code ] ?? '';
						$viber_replace[] = $posted_data[ $code ] ?? '';
				}
			}
			$sms_msg = str_replace($shortcodes[0], $sms_replace, $sms_template);
			$viber_msg = str_replace($shortcodes[0], $viber_replace, $viber_template);

			// Отправка через SMS
			if ( get_option('SMSFLY_cf7_onsubmit') == 1 ) {
                SMSflyC::inst()->typeMessage = 'Cf7 OnSubmit (SMS)';
				SMSflyC::sendToFly(get_option('SMSFLY_cf7_namesend'), $sms_to, $sms_msg);
			}

			// Отправка через Viber
			if ( get_option('VIBER_cf7_onsubmit') == 1 ) {
                SMSflyC::inst()->typeMessage = 'Cf7 OnSubmit (Viber)';
				$viberSender = get_option('VIBER_cf7_namesend');
				SMSflyC::inst()->setSourceViber($viberSender);
				SMSflyC::inst()->sendViber(['phone' => $viber_to, 'text' => $viber_msg]);
			}
		}
	}

	add_action( 'wpcf7_submit', 'sendtest', 10, 2 );
}