<?php

namespace Crudo\Config;

use Symfony\Component\Yaml\Parser;

class Config
{

    private static $instance;
    private $baseUrl;
    private $dbParams;
    private $paginationPerPage;

    private function __construct()
    {
        $yaml = new Parser();

        $config = $yaml->parse(file_get_contents(__DIR__ . '/config.yml'));

        $this->setDbParams($config['db']);
        $this->setBaseUrl($config['url']['base_url']);

        $this->setPaginationPerPage($config['pagination']['per_page']);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setPaginationPerPage($paginationPerPage)
    {
        $this->paginationPerPage = $paginationPerPage;
    }

    public function getPaginationPerPage()
    {
        return $this->paginationPerPage;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setDbParams($params)
    {
        $this->dbParams = array(
            'dbname' => $params['dbname'],
            'user' => $params['user'],
            'password' => $params['password'],
            'host' => $params['host'],
            'driver' => $params['driver']
        );
    }

    public function getDbParams()
    {
        return $this->dbParams;
    }

}