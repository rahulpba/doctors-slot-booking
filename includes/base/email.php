<?php
namespace Dudlewebs\DSLB;

defined('ABSPATH') || exit;

class SendMail {
    protected $to;
    protected $subject;
    protected $content;
    protected $headers = [];
    protected $attachments = [];

    protected $email_header = 'Dudlewebs';
    protected $email_footer;

    public function __construct($to, $subject, $content, $headers = [], $attachments = []) {
        $this->to          = $to;
        $this->subject     = $subject;
        $this->content     = $content;
        $this->headers     = $headers;
        $this->attachments = $attachments;
        $this->email_footer = '© ' . date('Y') . ' ' . get_bloginfo('name') . '. All rights reserved.';
    }

    public function set_header($header_html) {
        $this->email_header = $header_html;
    }

    public function set_footer($footer_html) {
        $this->email_footer = $footer_html;
    }

    public function send() {
        $headers = array_merge([
            'Content-Type: text/html; charset=UTF-8',
        ], $this->headers);

        $message = $this->get_template();

        return wp_mail($this->to, $this->subject, $message, $headers, $this->attachments);
    }

    protected function get_template() {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    background-color: #f6f9fc;
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 40px auto;
                    background-color: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
                }
                .email-header {
                    background-color: #2e6df6;
                    color: #ffffff;
                    padding: 20px 30px;
                    text-align: center;
                    font-size: 20px;
                    font-weight: bold;
                }
                .email-content {
                    padding: 30px;
                    color: #333333;
                    font-size: 16px;
                    line-height: 1.6;
                }
                .email-footer {
                    background-color: #f1f1f1;
                    color: #777777;
                    text-align: center;
                    padding: 20px;
                    font-size: 14px;
                }
                a {
                    color: #2e6df6;
                    text-decoration: none;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">' . $this->email_header . '</div>
                <div class="email-content">' . wpautop($this->content) . '</div>
                <div class="email-footer">' . $this->email_footer . '</div>
            </div>
        </body>
        </html>';

        return $html;
    }
}
