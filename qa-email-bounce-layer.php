<?php

class qa_html_theme_layer extends qa_html_theme_base
{
  function __construct($template, $content, $rooturl, $request) {
    parent::__construct($template, $content, $rooturl, $request);

    if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
      $email = qa_get_logged_in_email();
      if($email && email_bounce_db::is_emailbounced($email)) {
        $this->notices[] = array(
          'body' => qa_lang('email_bounce/notice_body'),
          'url' => qa_path('account', null, qa_opt('site_url')),
          'button_text' => qa_lang('email_bounce/button_text')
        );
      }
    }
  }
}
