<?php

class qa_html_theme_layer extends qa_html_theme_base
{
  function __construct($template, $content, $rooturl, $request) {
    parent::__construct($template, $content, $rooturl, $request);

    if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
      $email = qa_get_logged_in_email() ;
      if($email && email_bounce_db::is_emailbounced($email)) {
        $this->notices[] = array(
          'body' => '現在、サイトからメールが届けられない状態です。お手数ですが、メールアドレスをご確認ください。',
          'url' => qa_opt('site_url') . qa_path_html('account'),
          'button_text' => 'メールアドレスを変更する'
        );
      }
    }
  }
}
