<?php

require_once('MailHelper.php');

class Mailer
{
    const FAIL_REASON_INVALID_NAME = "invalid_name";
    const FAIL_REASON_INVALID_HOST = "invalid_host";
    const FAIL_REASON_CANNOT_RESOLVE_HOST = "cannot_resolve_host";
    const FAIL_REASON_UNKNOWN             = "unknown";

    protected $pathToEmailsFile;
    protected $pathToTemplate;
    protected $subject;

    public function __construct($pathToEmailsFile, $pathToTemplate, $subject)
    {
        if (!file_exists($pathToEmailsFile)) {
            throw new Exception("No file with emails: " . $pathToEmailsFile);
        }
        if (!file_exists($pathToTemplate)) {
            throw new Exception("No template file: " . $pathToTemplate);
        }

        $this->pathToEmailsFile = $pathToEmailsFile;
        $this->pathToTemplate   = $pathToTemplate;
        $this->subject          = $subject;
    }

    public function send()
    {
        $emails = parse_ini_file($this->pathToEmailsFile);
        foreach ($emails as $email) {
            list($emailAddress, $emailName) = explode(",", $email);

            if (!$this->validateName($emailName)) {
                $this->logFail($emailAddress, FAIL_REASON_INVALID_NAME);
                continue;
            }
            if (!$this->validateHost($emailAddress)) {
                $this->logFail($emailAddress, FAIL_REASON_INVALID_HOST);
                continue;
            }
            if (!$this->resolveHost($emailAddress)) {
                $this->logFail($emailAddress, FAIL_REASON_CANNOT_RESOLVE_HOST);
                continue;
            }

            $vars = [
                'email' => $emailAddress,
                'name'  => $emailName,
            ];
            $emailContent = $this->parseTemplate($this->pathToTemplate, $vars);

            if ($this->_send($emailAddress, $emailContent)) {
                $this->logSuccess($emailAddress);
            } else {
                $this->logFail($emailAddress, FAIL_REASON_CANNOT_SEND_EMAIL);
            }

            $this->sendLogs();
        }
    }

    protected function parseTemplate($pathToTemplate, array $vars)
    {
        extract($vars);
        return require($pathToTemplate);
    }

    protected function _send($email, $content)
    {
        try {
            MailHelper::send("info@2hive.org", $email, $this->subject, $content, strip_tags($content));
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function sendLogs()
    {
        $failLogFile    = "{$this->pathToEmailsFile}.fail.log";
        $successLogFile = "{$this->pathToEmailsFile}.success.log";
    }

    protected function validateName($name)
    {
        return true;
    }
    protected function validateHost($email)
    {
        $f = array(
            "abuse",
            "support",
            "example",
            "germanysales",
            "john.doe",
            "johndoe",
            "johnsmith",
            "yourname",
            "yourmail"
        );
        
        $l = array(
            "getsentry.com",
            "2x.png",
            "sentry2.aboutme-cloud.n",
            "mysite.com",
            "yoursite.com",
            "2x.gif",
            "incoming.interc",
            "address.com",
            "company.com",
            "domain.com",
            "email.com",
            "example.com",
            "hollywoodlife.com"
        );
        
        //TODO logic
        return true;
    }

    protected function resolveHost($email)
    {
        $host = preg_replace('/[^@]*@/', '', $email);
        return ($host == gethostbyname($host));
    }

    protected function logFail($email, $reason = FAIL_REASON_UNKNOWN)
    {
        $content = "{$email} | " + $reason;
        file_put_contents("{$this->pathToEmailsFile}.fail.log", "{$content}\n", FILE_APPEND);
    }
    protected function logSuccess($email)
    {
        file_put_contents("{$this->pathToEmailsFile}.success.log", "{$email}\n", FILE_APPEND);
    }
}
