<?php
// don't allow this page to be requested directly from browser
if (!defined('QA_VERSION')) {
	header('Location: ../../');
	exit;
}

class q2a_email_bounce_event {
    function process_event($event, $post_userid, $post_handle, $cookieid, $params)
    {
        if ($event === 'u_save') {
            error_log('u_save: user_id: '.$post_userid);
            $email=qa_get_user_email($post_userid);
            error_log('u_save: email: '.$email);
        }
    }
}