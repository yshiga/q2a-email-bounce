<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
}

class email_bounce_db
{
	public static function create_emailbounce_table()
	{
		qa_db_query_sub(
			'CREATE TABLE IF NOT EXISTS ^emailbounce ('.
				'id INT(11) NOT NULL AUTO_INCREMENT,'.
				'user_id INT(11) NOT NULL,'.
				'email VARCHAR(255) NOT NULL,'.
				'bounced TINYINT DEFAULT 0 NOT NULL,'.
				'notify TINYINT DEFAULT 0 NOT NULL,'.
				'created DATETIME NOT NULL,'.
				'updated DATETIME NOT NULL,'.
				'PRIMARY KEY (id)'.
			') ENGINE=InnoDB DEFAULT CHARSET=utf8'
		);
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
