<?php

if (!defined('QA_VERSION')) {
    require_once dirname(empty($_SERVER['SCRIPT_FILENAME']) ? __FILE__ : $_SERVER['SCRIPT_FILENAME']).'/../../../qa-include/qa-base.php';
}

require_once QA_PLUGIN_DIR.'q2a-email-bounce/email-bounce-db.php';

error_log('email bounce update start');

$db = qa_db_connection();
email_bounce_db::update_pending_emails(48);
error_log($db->affected_rows. ' emails updated');

error_log('email bounce update end');