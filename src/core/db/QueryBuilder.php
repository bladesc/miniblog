<?php

namespace src\core\db;

class QueryBuilder extends Query
{
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    public const C_AND = 'AND';
    public const C_OR = 'OR';

    protected $select = [];
    protected $insert = [];
    protected $update = [];
    protected $delete = false;
    protected $from = null;
    protected $where = [];
    protected $orderBy = [];
    protected $limit = null;
    protected $offset = null;
    protected $leftJoin = [];
    protected $rightJoin = [];
    protected $innerJoin = [];
    protected $having = [];
    protected $groupBy = null;
    protected $like = [];
    protected $raw = '';
    protected $logicConditions = [];

    protected $query = '';

    protected $err = [];

    protected function resetCondition()
    {
        $this->select = [];
        $this->insert = [];
        $this->update = [];
        $this->from = null;
        $this->where = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->leftJoin = [];
        $this->rightJoin = [];
        $this->innerJoin = [];
        $this->having = [];
        $this->groupBy = [];
        $this->query = '';
        $this->raw = '';
        $this->err = [];
        $this->like = [];
        $this->logicConditions = [];
    }

    public function rawSql($statemet)
    {
        $this->resetCondition();
        $this->raw = $statemet;
        return $this;
    }

    public function select($tableNames)
    {
        $this->resetCondition();
        if (is_string($tableNames)) {
            $this->select[] = $tableNames;
        } else {
            foreach ($tableNames as $tableName) {
                $this->select[] = $tableName;
            }
        }
        return $this;
    }

    public function insert(string $tableName, array $data)
    {
        $this->resetCondition();
        foreach ($data as $field => $value) {
            $this->insert[] = ["table" => $tableName, "field" => $field, "value" => $value];
        }
        return $this;
    }

    public function update(string $tableName, array $data)
    {
        $this->resetCondition();
        foreach ($data as $field => $value) {
            $this->update[] = ["table" => $tableName, "field" => $field, "value" => $value];
        }
        return $this;
    }

    public function delete()
    {
        $this->resetCondition();
        $this->delete = true;
        return $this;
    }

    public function from(string $tableName)
    {
        $this->from = $tableName;
        return $this;
    }

    public function where(string $field, string $operator, string $value)
    {
        $this->where[] = ['field' => $field, 'operator' => $operator, 'value' => $value];
        return $this;
    }

    public function orderBy(string $field, string $type)
    {
        $this->orderBy[] = ["field" => $field, "type" => $type];
        return $this;
    }

    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function leftJoin(string $tableName, string $fieldName, string $joinTableName, string $joinFieldName)
    {
        $this->leftJoin[] = ['tableName' => $tableName, 'fieldName' => $fieldName, 'joinTableName' => $joinTableName, 'joinFieldName' => $joinFieldName];
        return $this;
    }

    public function rightJoin()
    {
        return $this;
    }

    public function innerJoin()
    {
        return $this;
    }

    public function groupBy(string $fieldName)
    {
        $this->groupBy = $fieldName;
        return $this;
    }

    public function having()
    {

    }

    public function like(string $field, $value)
    {
        $this->like[] = ['field' => $field, 'value' => $value];
        return $this;
    }

    public function conditions(array $conditions)
    {
        foreach ($conditions as $condition) {
            $this->logicConditions[] = $condition;
        }
        return $this;
    }

    public function execute()
    {
        $this->prepareQuery();
        $this->sth = $this->conn->prepare($this->query);
        return $this->sth->execute();
    }

    public function getAll(): array
    {
        $this->execute();
        return $this->sth->fetchAll();
    }

    public function getOne()
    {
        $this->execute();
        return $this->sth->fetch();
    }

    protected function prepareQuery(): void
    {
        if (!empty($this->select)) {
            $this->prepareSelect();
        } elseif (!empty($this->insert)) {
            $this->prepareInsert();
        } elseif (!empty($this->update)) {
            $this->prepareUpdate();
        } elseif ($this->delete) {
            $this->prepareDelete();
        } elseif ($this->raw) {
            $this->prepareRaw();
        }
    }

    protected function prepareRaw()
    {
        $this->query = $this->raw;
    }

    protected function prepareSelect(): void
    {
        $this->query .= "SELECT ";
        foreach ($this->select as $tableName) {
            $this->query .= $tableName;
            if ($tableName !== end($this->select)) {
                $this->query .= ", ";
            }
        }
        $this->prepareFrom();
    }

    protected function prepareDelete(): void
    {
        $this->query .= "DELETE ";
        $this->prepareFrom();
    }

    protected function prepareUpdate()
    {
        $this->query .= "UPDATE ";
        $table = (current($this->update))['table'];
        $this->query .= $table;
        $this->query .= " SET ";
        foreach ($this->update as $update) {
            $this->query .= $update['field'] . '=' . "'" .  $update['value'] . "', ";
        }
        $this->query = substr($this->query, 0, -2);
        $this->prepareWhere();
    }


    protected function prepareInsert()
    {
        $table = (current($this->insert))['table'];
        $fields = '(';
        $values = '(';
        foreach ($this->insert as $insert) {
            $fields .= $insert['field'] . ', ';
            $values .= "'" . $insert['value'] . "'" . ', ';
        }
        $fields = substr($fields, 0, -2);
        $values = substr($values, 0, -2);
        $fields .= ')';
        $values .= ')';
        $this->query .= 'INSERT INTO ' . $table . ' ' . $fields . ' VALUES ' . $values;
    }

    protected function prepareFrom(): void
    {
        if (empty($this->from)) {
            $this->err[] = "";
        } else {
            $this->query .= " FROM " . $this->from;
        }
        $this->prepareLeftJoin();
        $this->prepareRightJoin();
        $this->prepareInnerJoin();
        $this->prepareWhere();
    }

    protected function prepareLeftJoin() {
        if (!empty($this->leftJoin)) {
            foreach ($this->leftJoin as $join) {
                $this->query .= ' LEFT JOIN ' . $join['joinTableName'] . ' ON ' . $join['tableName'] . '.' . $join['fieldName'] . '=' . $join['joinTableName'] . '.' . $join['joinFieldName'];
            }
        }
    }

    protected function prepareRightJoin()
    {

    }

    protected function prepareInnerJoin()
    {

    }

    protected function prepareWhere(): void
    {
        if (!empty($this->where)) {
            $this->query .= " WHERE ";
            foreach ($this->where as $condition) {
                if (reset($this->where)) {
                    $this->query .= $condition['field'] . $condition['operator'] . "'" . $condition['value'] . "'";
                } else {
                    $this->query .= " AND (";
                    $this->query .= $condition['field'] . $condition['operator'] . "'" . $condition['value'] . "'";
                    $this->query .= ")";
                }
            }
        }
        $this->prepareLike();
    }

    protected function prepareLike(): void
    {
        if (!empty($this->like)) {
            $this->query .= " WHERE ";
            $i = 0;
            foreach ($this->like as $condition) {
                if ($i === 0) {
                    $this->query .= $condition['field'] . " LIKE '" . $condition['value'] . "'";
                } else {
                    if (empty($this->logicConditions)) {
                        $lCondition = self::C_AND;
                    } else {
                        $lCondition = $this->logicConditions[($i-1)];
                    }
                    $this->query .= " $lCondition (";
                    $this->query .= $condition['field'] . " LIKE '" . $condition['value'] . "'";
                    $this->query .= ")";
                }
                $i++;
            }
        }
        $this->prepareGroupBy();
    }

    protected function prepareGroupBy()
    {
        if (!empty($this->groupBy)) {
            $this->query .= " GROUP BY " . $this->groupBy;
        }
        $this->prepareOrderBy();
    }

    protected function prepareOrderBy(): void
    {
        if (!empty($this->orderBy)) {
            $this->query .= " ORDER BY ";
            foreach ($this->orderBy as $order) {
                $this->query .= $order["field"] . " " . $order["type"];
                if ($order !== end($this->orderBy)) {
                    $this->query .= ", ";
                }
            }
        }
        $this->prepareLimit();
    }

    public function prepareLimit(): void
    {
        if (!empty($this->limit)) {
            $this->query .= " LIMIT " . $this->limit;
        }
        $this->prepareOffset();
    }

    public function prepareOffset(): void
    {
        if (!empty($this->offset)) {
            $this->query .= " OFFSET " . $this->offset;
        }
    }

    public function beginTransactions()
    {
        $this->conn->beginTransaction();
    }

    public function rollback()
    {
        $this->conn->rollBack();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function getLastInsertId()
    {
        return $this->conn->lastInsertId();
    }
}