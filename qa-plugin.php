<?php

/*
	Plugin Name: Email Bounce
	Plugin URI:
	Plugin Description: Notify the user of an unachievable mail.
	Plugin Version: 1.0
	Plugin Date: 2016-08-17
	Plugin Author: 38qa.net
	Plugin Author URI: http://www.question2answer.org/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.7
	Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

//Define global constants
@define( 'EMAIL_BAUNCE_DIR', dirname( __FILE__ ) );
@define( 'EMAIL_BAUNCE_TOKEN', 'ySXFVr7pkrrFd*5oeg19i4AhvhxRSO' );

require_once EMAIL_BAUNCE_DIR . '/email-bounce-db.php';
// process
qa_register_plugin_module('process', 'qa-email-bounce-api.php', 'qa_email_bounce', 'Email Bounce API');
// language file
qa_register_plugin_phrases('qa-email-bounce-lang-*.php', 'email_bounce');
// overrides
qa_register_plugin_overrides('qa-email-bounce-overrides.php');
// // layer
// qa_register_plugin_layer('qa-email-bounce-layer.php','Email Bounce Layer');
// widgets
qa_register_plugin_module('widget','qa-email-bounce-widget.php','qa_email_bounce_widget','Email Bounce Notification');

/*
	Omit PHP closing tag to help avoid accidental output
*/
