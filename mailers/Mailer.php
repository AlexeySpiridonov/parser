<?php

require_once(dirname(__FILE__) . '/MailHelper.php');

class Mailer
{
    const FAIL_REASON_INVALID_NAME = "invalid_name";
    const FAIL_REASON_INVALID_HOST = "invalid_host";
    const FAIL_REASON_CANNOT_RESOLVE_HOST = "cannot_resolve_host";
    const FAIL_REASON_CANNOT_SEND_EMAIL   = "cannot_send_email";
    const FAIL_REASON_UNKNOWN             = "unknown";

    protected $pathToEmailsFile;
    protected $pathToTemplate;
    protected $subject;

    protected $filterByName = array(
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

    protected $filterByHost = array(
        "noexist.999",
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

        /**
         * Removing old Log Files if any...
         */
        @unlink($this->getSuccessLogFilename());
        @unlink($this->getFailLogFilename());
    }

    public function send()
    {
        $emails = str_getcsv(file_get_contents($this->pathToEmailsFile), "\n");
        foreach ($emails as $email) {
            list($emailAddress, $emailName) = explode(",", $email);

            if (!$this->validateName($emailName)) {
                $this->logFail($emailAddress, self::FAIL_REASON_INVALID_NAME);
                continue;
            }
            if (!$this->validateHost($emailAddress)) {
                $this->logFail($emailAddress, self::FAIL_REASON_INVALID_HOST);
                continue;
            }
            if (!$this->resolveHost($emailAddress)) {
                $this->logFail($emailAddress, self::FAIL_REASON_CANNOT_RESOLVE_HOST);
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
                $this->logFail($emailAddress, self::FAIL_REASON_CANNOT_SEND_EMAIL);
            }
        }

        /**
         * Sending result logs at the end
         */
        $this->sendLogs();
    }

    protected function parseTemplate($pathToTemplate, array $vars)
    {
        extract($vars);
        return require($pathToTemplate);
    }

    protected function _send($email, $content)
    {
        try {
            MailHelper::send(["info@2hive.org", "2Hive Content Moderation Service"], [['email' => $email]], $this->subject, $content, strip_tags($content));
        } catch (Exception $e) {
            echo "Cannot send an email: {$e->getMessage()}\n";
            return false;
        }

        return true;
    }

    protected function sendLogs()
    {
        $failLogFile    = $this->getFailLogFilename();
        $successLogFile = $this->getSuccessLogFilename();

        if (file_exists($failLogFile)) {
            $failLog    = file_get_contents($failLogFile);
            try {
                MailHelper::send(["info@2hive.org", "2Hive Notification System"], [['email' => "info@2hive.org"]], "2Hive Fail Log", nl2br($failLog), $failLog);
            } catch (Exception $e) {
                echo "Cannot send Fail Log: {$e->getMessage()}\n";
            }
        }

        if (file_exists($successLogFile)) {
            $successLog = file_get_contents($successLogFile);
            try {
                MailHelper::send(["info@2hive.org", "2Hive Notification System"], [['email' => "info@2hive.org"]], "2Hive Success Log", nl2br($successLog), $successLog);
            } catch (Exception $e) {
                echo "Cannot send Success Log: {$e->getMessage()}\n";
            }
        }
    }

    protected function validateName($name)
    {
        foreach($this->filterByName as $nameToFilter) {
            if (stripos($name, $nameToFilter) !== false) {
                return false;
            }
        }
        return true;
    }
    protected function validateHost($email)
    {
        list($name, $host) = explode('@', $email);

        // Name
        foreach($this->filterByName as $nameToFilter) {
            if (stripos($name, $nameToFilter) !== false) {
                return false;
            }
        }

        // Host
        foreach($this->filterByHost as $hostToFilter) {
            if (stripos($host, $hostToFilter) !== false) {
                return false;
            }
        }

        return true;
    }

    protected function resolveHost($email)
    {
        $host = preg_replace('/[^@]*@/', '', $email);
        $hostName = gethostbyname($host);
        return ($host != $hostName);
    }

    protected function logFail($email, $reason = self::FAIL_REASON_UNKNOWN)
    {
        $content = "{$email} | " . $this->reasonToText($reason);
        file_put_contents($this->getFailLogFilename(), "{$content}\n", FILE_APPEND);
    }
    protected function logSuccess($email)
    {
        file_put_contents($this->getSuccessLogFilename(), "{$email}\n", FILE_APPEND);
    }

    private function getFailLogFilename()
    {
        return "{$this->pathToEmailsFile}.fail.log";
    }
    private function getSuccessLogFilename()
    {
        return "{$this->pathToEmailsFile}.success.log";
    }

    private function reasonToText($reason)
    {
        $reasons = [
            FAIL_REASON_INVALID_NAME => "Invalid recipient name",
            FAIL_REASON_INVALID_HOST => "Invalid email host",
            FAIL_REASON_CANNOT_RESOLVE_HOST => "Cannot resolve Email host",
            FAIL_REASON_CANNOT_SEND_EMAIL   => "Error while sending an email",
            FAIL_REASON_UNKNOWN             => "Unknown error",
        ];

        if (!isset($reasons[$reason])) {
            return "";
        }

        return $reasons[$reason];
    }
}
