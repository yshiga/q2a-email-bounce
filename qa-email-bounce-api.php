<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_email_bounce
{
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

			if (qa_opt('email_bounce_active')) {
				email_bounce_db::create_emailbounce_table();
			}
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
			// qa_db_connect( 'qas_blog_ajax_db_fail_handler' );

			header( 'Access-Control-Allow-Origin: *' );
			header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );

			//	Ensure no PHP errors are shown in the Ajax response
			@ini_set( 'display_errors', 0 );

			$jsontext = qa_post_text('bounce');

			$bounce = json_decode($jsontext);
			error_log($bounce->bounceType);
			error_log($bounce->bounceSubType);
			error_log($bounce->bouncedRecipients[0]->emailAddress);

			echo "200\nOK\n";

			// qa_db_disconnect();
		}

	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
