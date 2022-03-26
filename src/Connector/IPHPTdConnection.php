<?php
namespace WztzTech\Iot\PhpTd\Connector;

interface IPHPTdConnection{
    
    public function exec(String $taosql);

    public function query(String $taosql);
    
}