<?php
if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

function qa_send_email($params, $async=false, $buffering=false, $eventid=null, $userid=null)
{
	if(email_bounce_db::is_emailbounced($params['toemail'])) {
		error_log('qa_send_email: in emailbounce:' . $params['toemail']);

		// 10回に1回はフラグを取り消し、次回送信する
		if (rand(1, 10) === 1) {
			email_bounce_db::update_emailbounce($userid, $params['toemail'], 0);
		}

		return true;
	}

	return qa_send_email_base($params, $async, $buffering, $eventid, $userid);
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
