<?php
/**
 * Mailgun plugin for phplist.
 *
 * This file is a part of Mailgun Plugin.
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  phplist
 *
 * @author    Duncan Cameron
 * @copyright 2017 Duncan Cameron
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License, Version 3
 */

/**
 * Registers the plugin with phplist.
 */
if (!interface_exists('EmailSender')) {
    return;
}

class Mailgun extends phplistPlugin implements EmailSender
{
    const VERSION_FILE = 'version.txt';

    /** @var Mailgun connector instance */
    private $connector;

    /*
     *  Inherited variables
     */
    public $name = 'Multiple Mailgun Plugin';
    public $authors = 'Sabbir Hasan';
    public $description = 'Send emails through Mailgun';
    public $documentationUrl = 'https://resources.phplist.com/plugin/mailgun';
    public $settings = array(
        'mailgun_sender_email' => array(
            'value' => '',
            'description' => 'Sender Email',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Mailgun',
        ),
        'mailgun_api_key' => array(
            'value' => '',
            'description' => 'API key',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Mailgun',
        ),
        'mailgun_domain' => array(
            'value' => '',
            'description' => 'Domain',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Mailgun',
        ),
        'mailgun_sender_email_2' => array(
            'value' => '',
            'description' => 'Sender Email 2',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Mailgun',
        ),
        'mailgun_api_key_2' => array(
            'value' => '',
            'description' => 'API key 2',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Mailgun',
        ),
        'mailgun_domain_2' => array(
            'value' => '',
            'description' => 'Domain 2',
            'type' => 'text',
            'allowempty' => false,
            'category' => 'Mailgun',
        ),
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . 'Mailgun' . '/';
        parent::__construct();
        $this->version = (is_file($f = $this->coderoot . self::VERSION_FILE))
            ? file_get_contents($f)
            : '';
    }

    /**
     * Provide the dependencies for enabling this plugin.
     *
     * @return array
     */
    public function dependencyCheck()
    {
        global $emailsenderplugin;

        return array(
            'PHP version 5.4.0 or greater' => version_compare(PHP_VERSION, '5.4') > 0,
            'phpList version 3.3.0 or greater' => version_compare(getConfig('version'), '3.3') > 0,
            'No other plugin to send emails can be enabled' => (
                empty($emailsenderplugin)
                || get_class($emailsenderplugin) == __CLASS__
            ),
            'curl extension installed' => extension_loaded('curl'),
            'Common Plugin installed' => phpListPlugin::isEnabled('CommonPlugin'),
        );
    }

    /**
     * Implement the EmailSender interface to send an email using the Mailgun API.
     *
     * @param PHPlistMailer $phpmailer mailer instance
     * @param string        $headers   the message http headers
     * @param string        $body      the message body
     *
     * @return bool success/failure
     */
    public function send(PHPlistMailer $phpmailer, $headers, $body)
    {
        static $client = null;
        static $domain;
        static $apiKey;
        static $senderMail = strtolower($phpmailer->From);
        
        if($senderMail === strtolower(getConfig('mailgun_sender_email'))){
            $apiKey = getConfig('mailgun_api_key');
            $domain = getConfig('mailgun_domain');
        }else{
            $apiKey = getConfig('mailgun_api_key_2');
            $domain = getConfig('mailgun_domain_2');
        }
        
        if ($client === null) {
            //$client = \Mailgun\Mailgun::create(getConfig('mailgun_api_key'));
            $client = \Mailgun\Mailgun::create($apiKey);
            //$domain = getConfig('mailgun_domain');
        }
        $to = $phpmailer->getToAddresses();
        $parameters = [
            'from' => "$phpmailer->FromName <$phpmailer->From>",
            'to' => $to[0][0],
            'subject' => $phpmailer->Subject,
        ];
        /*
         * Add any attached files as either attachments or inline.
         * Mailgun requires the cid of inline attachments to match the file name
         */
        $files = [
            'attachment' => [],
            'inline' => [],
        ];
        $inlineCid = [];
        $inlineName = [];

        foreach ($phpmailer->getAttachments() as $item) {
            list($content, $filename, $name, $encoding, $type, $isString, $disposition, $cid) = $item;
            $fileDetails = ['filename' => $name, 'fileContent' => $content];

            if ($disposition == 'inline') {
                $files['inline'][] = $fileDetails;
                $inlineCid[] = "cid:$cid";
                $inlineName[] = "cid:$name";
            } else {
                $files['attachment'][] = $fileDetails;
            }
        }
        /*
         * for an html message both Body and AltBody will be populated
         * for a plain text message only Body will be populated
         */
        $isHtml = $phpmailer->AltBody != '';

        if ($isHtml) {
            $body = $phpmailer->Body;

            if ($inlineCid) {
                $body = str_replace($inlineCid, $inlineName, $body);
            }
            $parameters['html'] = $body;
            $parameters['text'] = $phpmailer->AltBody;
        } else {
            $parameters['text'] = $phpmailer->Body;
        }

        foreach ($phpmailer->getCustomHeaders() as $item) {
            $parameters['h:' . $item[0]] = $item[1];
        }

        try {
            $result = $client->sendMessage($domain, $parameters, $files);
        } catch (Exception $e) {
            logEvent(sprintf('Mailgun send exception: %s', $e->getMessage()));

            return false;
        }

        if ($result->http_response_code == 200 && $result->http_response_body->message == 'Queued. Thank you.') {
            return true;
        }
        logEvent('Mailgun send failed: ' . print_r($result, true));

        return false;
    }
}
