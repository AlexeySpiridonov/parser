<?php

/**
 * Created by PhpStorm.
 * User: ssl
 * Date: 20.06.14
 * Time: 15:19
 */
class db
{
    private $dbname;
    private $user;
    private $pass;
    public $db;

    function __construct()
    {
        $this->dbname = DB_NAME;
        $this->user = DB_LOGIN;
        $this->pass = DB_PASSWORD;


        try {
            $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . $this->dbname, $this->user, $this->pass);
            $this->db->exec("set names utf8");
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }


    function checkURL($url)
    {
        PRFLR::Begin('DB.checkURL');
        $sql = "SELECT * FROM `cache` WHERE `url` = :url";
        $pres = $this->db->prepare($sql);
        $pres->bindParam(":url", $url);
        $pres->execute();
        if ($pres->rowCount() > 0) {
            echo "skip parse: " . $url . "\n";
            PRFLR::End('DB.checkURL', "skip");

            return true;
        }

        echo "allow parse: " . $url . "\n";
        $sql = "INSERT INTO `cache` (`url`)
                 VALUES              (:url)";
        $preq = $this->db->prepare($sql);
        $preq->bindValue(':url', $url);

        $preq->execute();
        PRFLR::End('DB.checkURL', "add");
        return false;

    }

    function addItem($type = '', $name = '', $email = '', $domain = '', $site = '', $url = '')
    {
        PRFLR::Begin('DB.addItem');

        echo "type:" . $type . "\n";
        echo "name:" . $name . "\n";
        echo "email:" . $email . "\n";
        echo "domain:" . $domain . "\n";
        echo "site:" . $site . "\n";
        echo "url:" . $url . "\n";

        $sql = "SELECT * FROM `items` WHERE `url` = :url and `type` = :type ";
        $pres = $this->db->prepare($sql);
        @$pres->bindParam(":url", trim($url));
        $pres->bindParam(":type", $type);
        $pres->execute();

        if ($pres->rowCount() > 0) {
            $res = $pres->fetch(PDO::FETCH_ASSOC);
            if (empty($res['email']) && !empty($email)) {
                $this->upItems($res['id'], 'email', trim($email));
                $this->upItems($res['id'], 'domain', trim($domain));
                echo "UP email & domain\n";
            }

            if (empty($res['site']) && !empty($site)) {
                $this->upItems($res['id'], 'site', trim($site));
                echo "UP site\n";
            }


            echo "skip add\n";
            PRFLR::End('DB.addItem', "skip");
            return;
        }
        echo "----------\n\n";
        $sql = "INSERT INTO `items` (`type`, `name`, `email`, `domain`, `url`, `site`, `update`)
                VALUES            (:type,  :name,  :email,  :domain,  :url,  :site,  NOW())";

        $preq = $this->db->prepare($sql);
        $preq->bindValue(':type', $type);
        $preq->bindValue(':name', trim($name));
        $preq->bindValue(':email', trim($email));
        $preq->bindValue(':domain', trim($domain));
        $preq->bindValue(':site', trim($site));
        $preq->bindValue(':url', trim($url));


        if ($preq->execute()) {
            PRFLR::End('DB.addItem', "add");
            return true;
        }
        print_r($preq->errorInfo());
        PRFLR::End('DB.addItem', "error");
        return false;

    }

    function getReport()
    {
        $sql = "SELECT distinct `email`, `name`, `id`  FROM `items` where `update` > NOW() - INTERVAL 1 DAY and email!=''";

        return $this->selexec($sql);
    }

    function getReportAll()
    {
        $sql = "SELECT distinct `email`, `name`  FROM `items` where email!=''";

        return $this->selexec($sql);
    }
    
    function selexec($sql, $fetch = 'all')
    {
        $query = $this->db->prepare($sql);
        $query->execute();
        if ($fetch == 'all')
            return $query->fetchAll(PDO::FETCH_ASSOC);

        if ($fetch != 'all')
            return $query->fetch(PDO::FETCH_ASSOC);
    }

    function upItems($id, $param, $value)
    {
        $sql = "UPDATE `items` SET `" . $param . "`=? WHERE `id`=?";
        $preq = $this->db->prepare($sql);
        $preq->execute(array($value, $id));
    }


    function disconnect()
    {
        $this->db = null;
    }

    function __destruct()
    {
        $this->disconnect();
    }

}
