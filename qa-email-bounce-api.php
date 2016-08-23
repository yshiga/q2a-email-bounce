<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_email_bounce
{
	// initialize db-table 'emailbounce' if it does not exist yet
	function init_queries($tableslc) {
		$tablename = qa_db_add_table_prefix('emailbounce');

		if(!in_array($tablename, $tableslc)) {
			return email_bounce_db::create_emailbounce_sql($tablename);
		}
	}

	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot)
	{
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function allow_template($tamplate)
	{
		return ($template !== 'admin');
	}

	function option_default($option)
	{
		switch($option) {
			case 'email_bounce_active':
				return 0;
			default:
				return null;
		}
	}

	function admin_form(&$qa_content)
	{
		$ok = null;

		if(qa_clicked('email_bounce_save_settings')) {
			qa_opt('email_bounce_active', (bool)qa_post_text('email_bounce_active_check'));

			// if (qa_opt('email_bounce_active')) {
			// 	email_bounce_db::create_emailbounce_table();
			// }
			$ok = qa_lang('admin/options_saved');
		}

		//	Create the form for display
		$fields = array();

		$fields[] = array(
			'label' => qa_lang('email_bounce/admin_activate'),
			'tags' => 'NAME="email_bounce_active_check"',
			'value' => qa_opt('email_bounce_active'),
			'type' => 'checkbox',
		);

		return array(
			'ok' => ($ok && !isset($error)) ? $ok : null,

			'fields' => $fields,

			'buttons' => array(
				array(
					'label' => qa_lang('main/save_button'),
					'tags' => 'NAME="email_bounce_save_settings"',
				),
			),
		);
	}

	function init_ajax()
	{
		if (!qa_opt('email_bounce_active')) {
			return;
		}
		$operation = qa_post_text( 'qa_operation' );

		if ( isset($operation) && $operation === 'email_bounce' ) {
			header( 'Access-Control-Allow-Origin: *' );
			header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );

			$token = qa_post_text( 'api_token' );
			if (empty($token) || $token !== EMAIL_BAUNCE_TOKEN) {
				return;
			}
			$bouncejson = qa_post_text('bounce');
			if (empty($bouncejson)) {
				return;
			}
			// error_log($bouncejson);
			//	Ensure no PHP errors are shown in the Ajax response
			@ini_set( 'display_errors', 0 );

			qa_db_connect( 'qas_blog_ajax_db_fail_handler' );

			$bounce = json_decode($bouncejson);
			// error_log(serialize($bounce));
			if ($bounce->bounceType === 'Permanent') {
				$email = $bounce->bouncedRecipients[0]->emailAddress;
				if (!empty($email)) {
					$userid = email_bounce_db::get_userid_from_email($email);
					email_bounce_db::create_or_update_emailbounce($userid, $email);
				}
			}

			echo "OK";

			qa_db_disconnect();
			qa_exit();
		}

	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
