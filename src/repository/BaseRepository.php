<?php

namespace src\repository;

use src\core\db\QueryHelper;
use src\core\db\Tables;

class BaseRepository
{

    protected $db;
    protected $tables;

    public function __construct()
    {
        $this->db = new QueryHelper();
        $this->tables = (new Tables())->getTables();
    }

}