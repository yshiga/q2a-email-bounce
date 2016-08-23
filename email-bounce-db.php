<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

class email_bounce_db
{
	public static function create_emailbounce_sql($tablename)
	{
		return "CREATE TABLE IF NOT EXISTS $tablename (".
				'id INT(11) NOT NULL AUTO_INCREMENT,'.
				'userid INT(11),'.
				'email VARCHAR(255) NOT NULL,'.
				'bounced TINYINT DEFAULT 0 NOT NULL,'.
				'notify TINYINT DEFAULT 0 NOT NULL,'.
				'created DATETIME NOT NULL,'.
				'updated DATETIME NOT NULL,'.
				'PRIMARY KEY (id)'.
			') ENGINE=InnoDB DEFAULT CHARSET=utf8';
	}

	public static function create_emailbounce_table()
	{
		qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^emailbounce ('.
				'id INT(11) NOT NULL AUTO_INCREMENT,'.
				'userid INT(11),'.
				'email VARCHAR(255) NOT NULL,'.
				'bounced TINYINT DEFAULT 0 NOT NULL,'.
				'notify TINYINT DEFAULT 0 NOT NULL,'.
				'created DATETIME NOT NULL,'.
				'updated DATETIME NOT NULL,'.
				'PRIMARY KEY (id)'.
			') ENGINE=InnoDB DEFAULT CHARSET=utf8'
		);
	}

	public static function get_userid_from_email($email = null)
	{
		if (!isset($email)) {
			return '';
		}

		$sql = 'SELECT userid FROM ^users WHERE email = $';
		return qa_db_read_one_value(qa_db_query_sub($sql, $email),true);
	}

	public static function create_or_update_emailbounce($userid, $email)
	{
		if (empty($email)) {
			return;
		}

		if (empty($userid)) {
			$sql = 'SELECT count(email) FROM ^emailbounce
			WHERE email = $';
			$result =  qa_db_read_one_value(qa_db_query_sub($sql, $email),true);
		} else {
			$sql = 'SELECT count(email) FROM ^emailbounce
			WHERE userid = #
			AND email = $';
			$result =  qa_db_read_one_value(qa_db_query_sub($sql, $userid, $email),true);
		}
		if ($result > 0) {
			self::update_emailbounce($userid, $email);
			error_log('create emailbounce: '.$userid.' '.$email);
		} else {
			self::create_emailbounce($userid, $email);
			error_log('update emailbounce: '.$userid.' '.$email);
		}
	}

	public static function create_emailbounce($userid = '', $email)
	{
		qa_db_query_sub(
			'INSERT INTO ^emailbounce (userid, email, bounced, created, updated) VALUES (#, $, 1, NOW(), NOW())',
			$userid, $email
		);
	}

	public static function update_emailbounce($userid = '', $email, $bounced = 1, $notify = 0)
	{
		if (empty($userid)) {
			qa_db_query_sub(
				'UPDATE ^emailbounce SET bounced = #, notify = #, updated = NOW() WHERE email = $',
				$bounced, $notify, $email
			);
		} else {
			qa_db_query_sub(
				'UPDATE ^emailbounce SET bounced = #, notify = #, updated = NOW() WHERE userid = # AND email = $',
				$bounced, $notify, $userid, $email
			);
		}
	}
	public static function is_emailbounced($email, $userid = null)
	{
		if (empty($email)) {
			return false;
		}

		if (empty($userid)) {
			$sql = 'SELECT count(email) FROM ^emailbounce
			WHERE email = $ AND bounced = 1';
			$result = qa_db_read_one_value(qa_db_query_sub($sql, $email), true);
		} else {
			$sql = 'SELECT count(email) FROM ^emailbounce
			WHERE userid = #
			AND email = $ AND bounced = 1';
			$result = qa_db_read_one_value(qa_db_query_sub($sql, $userid, $email), true);
		}
		if ($result > 0) {
			return true;
		}
		return false;
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
