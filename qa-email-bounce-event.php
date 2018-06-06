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
            $cur_email=qa_get_user_email($post_userid);
            $bouncemail = email_bounce_db::get_email_from_userid($post_userid);
            foreach ($bouncemail as $email) {
                if ($cur_email !== $email) {
                    email_bounce_db::update_emailbounce($post_userid, $email, 0);
                }
            }
        }
    }
}