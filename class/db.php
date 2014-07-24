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
        $sql = "SELECT * FROM `cache` WHERE `url` = :url";
        $pres = $this->db->prepare($sql);
        $pres->bindParam(":url", $url);
        $pres->execute();
        if ($pres->rowCount() > 0) {
            echo "skip parse: " . $url . "\n";
            return true;
        }

        echo "allow parse: " . $url . "\n";
        $sql = "INSERT INTO `cache` (`url`)
                 VALUES              (:url)";
        $preq = $this->db->prepare($sql);
        $preq->bindValue(':url', $url);

        $preq->execute();
        return false;

    }

    function addItem($type = '', $name = '', $email = '', $domain = '', $site = '', $url = '')
    {
        echo "type:" . $type . "\n";
        echo "name:" . $name . "\n";
        echo "email:" . $email . "\n";
        echo "domain:" . $domain . "\n";
        echo "site:" . $site . "\n";
        echo "url:" . $url . "\n\n\n";

        $sql = "SELECT * FROM `items` WHERE `email` = :email and `type` = :type ";
        $pres = $this->db->prepare($sql);
        $pres->bindParam(":email", $email);
        $pres->bindParam(":type", $type);
        $pres->execute();
        if ($pres->rowCount() > 0) {
            echo "skip add";
            return;
        }

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

            return true;
        }
        print_r($preq->errorInfo());
        return false;

    }

    function selDev()
    {
        $sql = "SELECT `id`, `email`, `domen`, `name`  FROM `dev`";

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


    function disconnect()
    {
        $this->db = null;
    }

    function __destruct()
    {
        $this->disconnect();
    }

}