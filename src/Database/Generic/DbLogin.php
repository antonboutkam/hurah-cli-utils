<?php


namespace HurahCli\Database\Generic;
use ErrorException;


class DbLogin
{
    private string $user;
    private string $db_name;
    private string $pass;
    private string $host;

    function __construct(string $user, string $pass, string $host = 'localhost', string $sDbName = 'same_as_db_user')
    {
        if($sDbName === 'same_as_db_user')
        {
            $this->db_name = $user;
        }
        $this->user = $user;
        $this->pass = $pass;
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->db_name;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string|string
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @param string|string $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    public function createUser(DbLogin $oDbLogin)
    {
        $mysqli = $this->connect();
        $sQuery = "CREATE USER `{$oDbLogin->getUser()}`@`%` IDENTIFIED BY '{$oDbLogin->getPass()}';";
        $bResult = $mysqli->query($sQuery);
        $mysqli->query('FlUSH PRIVILEGES;');
    }

    private function connect():\mysqli
    {
        return mysqli_connect($this->host, $this->user, $this->pass);
    }
    public function grantUserAccess(DbLogin $oDbLogin, string $sDbName)
    {
        $sQuery = "GRANT ALL PRIVILEGES ON *.$sDbName TO `$oDbLogin->user`@`%`;";
echo $sQuery . PHP_EOL;
        $mysqli = $this->connect();
        $mysqli->query($sQuery);
        echo $mysqli->error;
        $mysqli->query(
            'FlUSH PRIVILEGES;');
        echo $mysqli->error;
    }
    public function canSelectDb(string $sDbName):bool
    {
        try
        {
            $mysqli = $this->connect();

            $mysqli->select_db($sDbName);
            return true;
        }
        catch (ErrorException $e)
        {
            return false;
        }
    }
    public function dbCreate(string $sDbName)
    {
        $mysqli = $this->connect();
        $sQuery = "CREATE DATABASE `{$sDbName}`";
        $result = $mysqli->query($sQuery, MYSQLI_USE_RESULT);
        echo $mysqli->error;
    }
    public function dbExists(string $sDbName):bool
    {
        $mysqli = $this->connect();
        $sQuery = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$sDbName}'";
        $result = $mysqli->query($sQuery);

        return $result->num_rows > 0;
    }
    public function canConnect():bool
    {
        try
        {
            $mysqli = mysqli_connect($this->host, $this->user, $this->pass);

            if($mysqli)
            {
                return true;
            }
            return false;
        }
        catch (ErrorException $e)
        {
            return false;
        }
    }



}