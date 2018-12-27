<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

class email_bounce_db
{
	const FLAG_NOT_BOUNCE = 0;
	const FLAG_BOUNCED    = 1;
	const FALG_PENDING    = 2;

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

	public static function get_email_from_userid($userid = null)
	{
		if (!isset($userid)) {
			return array();
		}

		$sql = 'SELECT email FROM ^emailbounce WHERE userid = $';
		return qa_db_read_all_values(qa_db_query_sub($sql, $userid),true);
	}

	public static function create_or_update_emailbounce($userid, $email)
	{
		if (empty($email)) {
			return;
		}

		$result = self::find_emailbounce_by_userid($userid, $email);

		if (count($result) > 0) {
			$before24 = strtotime('-24 hour');
			foreach ($result as $data) {
				$bounced = $data['bounced'];
				switch ($bounced) {
					case self::FLAG_NOT_BOUNCE: // not bounced のものは pendingにする
						self::update_emailbounce($data['userid'], $data['email'], self::FALG_PENDING);
						error_log('emailbounce update to pending: '.$userid.' '.$email);
						break;
					case self::FLAG_BOUNCED: // すでに bounced の場合 updated だけ更新
						self::update_emailbounce($data['userid'], $data['email'], self::FLAG_BOUNCED);
						error_log('emailbounce update updated: '.$userid.' '.$email);
						break;
					case self::FALG_PENDING: // pending で24時間以上経過していればbounced にする
						$updated = strtotime($data['updated']);
						if ($updated <= $before24) {
							self::update_emailbounce($data['userid'], $data['email'], self::FLAG_BOUNCED);
							error_log('emailbounce update to bounced: '.$userid.' '.$email);
						}
						break;
				}
			}
			
		} else {
			// 新規に pending で作成
			self::create_emailbounce($userid, $email, self::FALG_PENDING);
			error_log('emailbounce create pending: '.$userid.' '.$email);
		}
	}

	public static function find_emailbounce_by_userid($userid, $email)
	{
		if (empty($userid) || is_null($userid)) {
			$sql = 'SELECT *';
			$sql.= ' FROM ^emailbounce';
			$sql.= ' WHERE email = $';
			$result = qa_db_read_all_assoc(qa_db_query_sub($sql, $email));
		} else {
			$sql = 'SELECT *';
			$sql.= ' FROM ^emailbounce';
			$sql.= ' WHERE userid = #';
			$sql.= ' AND email = $';
			$result = qa_db_read_all_assoc(qa_db_query_sub($sql, $userid, $email));
		}
		return $result;
	}

	public static function create_emailbounce($userid = '', $email, $bounced = 2)
	{
		qa_db_query_sub(
			'INSERT INTO ^emailbounce (userid, email, bounced, created, updated) VALUES (#, $, #, NOW(), NOW())',
			$userid, $email, $bounced
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
			WHERE email = $ AND bounced = #';
			$result = qa_db_read_one_value(qa_db_query_sub($sql, $email, self::FLAG_BOUNCED), true);
		} else {
			$sql = 'SELECT count(email) FROM ^emailbounce
			WHERE userid = #
			AND email = $ AND bounced = #';
			$result = qa_db_read_one_value(qa_db_query_sub($sql, $userid, $email, self::FLAG_BOUNCED), true);
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
