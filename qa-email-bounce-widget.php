<?php

class qa_email_bounce_widget
{
    
    public function allow_template($template)
    {
        return true;
    }

    public function allow_region($region)
    {
        return $region === 'main';
    }

    public function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
    {
        $email = qa_get_logged_in_email();
        if($email && email_bounce_db::is_emailbounced($email)) {
            $url = qa_path('account', null, qa_opt('site_url'));
            $msg = qa_lang_sub('email_bounce/notice_body', $url);
            $html = $this->get_notice_html($msg);
            $themeobject->output($html);
        }
    }

    private function get_notice_html($msg)
    {
        $html = <<<EOS
<div class="mdl-card mdl-cell mdl-cell--12-col mdl-components__warning">
  <div class="mdl-card__supporting-text">
    {$msg}
  </div>
</div>

EOS;
        return $html;
    }

}