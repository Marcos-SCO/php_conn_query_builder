<?php

namespace App\Traits;

use App\Models\Model;
use PDOException;

trait QueryTrait
{
    // Bind values
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }

        return $this->stmt->bindValue($param, $value, $type);
    }

    public function select($table)
    {
        $query = "SELECT * FROM {$table}";
        $this->stmt = Model::$conn->prepare($query);
        $this->stmt->execute();
        $result = $this->stmt->fetchAll();
        $this->stmt->closeCursor();
        return $result;
    }

    public function insert($table, array $data)
    {
        $fields = implode(',', array_keys($data));
        $places = ':' . implode(',:', array_keys($data));

        $query = "INSERT INTO {$table} ({$fields}) VALUES ({$places})";

        $this->stmt = Model::$conn->prepare($query);
        foreach ($data as $name => $value) {
            $this->bind(":{$name}", $value);
        }
        $this->stmt->execute();
        $this->stmt->closeCursor();
        return Model::$conn->lastInsertId();
    }

    public function update($table, array $data, array $id)
    {
        // Destruct id
        // list($idKey, $idVal) = $id;
        $idKey = array_keys($id)[0];
        $idVal = array_values($id)[0];

        $query = "UPDATE {$table} SET";
        foreach ($data as $field => $value) {
            $query .= " {$field} = :{$field},";
        }

        $query = rtrim($query, ",");
        $query .= " WHERE {$idKey} = :idKey";

        $this->stmt = Model::$conn->prepare($query);

        foreach ($data as $field => $value) {
            $this->bind(":{$field}", $value);
        }

        // Bind id values
        $this->bind(":idKey", $idVal);

        try {
            $this->stmt->execute();
            $this->stmt->closeCursor();
            $this->stmt->fetch();

            $result = $this->stmt->rowCount();

            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function delete($table, array $data)
    {
        $query = "DELETE FROM {$table} WHERE";

        foreach ($data as $field => $value) {
            $query .= " {$field} = :{$field} AND";
        }

        $query = rtrim($query, "AND");
        $this->stmt = Model::$conn->prepare($query);

        foreach ($data as $field => $value) {
            $this->bind(":{$field}", $value);
        }

        $this->stmt->execute();
        $this->stmt->closeCursor();

        $result = $this->stmt->rowCount();
        return $result;
    }

    public function customQuery($query, array $data = null, $fetchMode = null)
    {
        $this->stmt = Model::$conn->prepare($query);
        if ($data) {
            foreach ($data as $field => $value) {
                $this->bind(":{$field}", $value);
            }
        }
        $this->stmt->execute();
        
        $result = $this->stmt->rowCount();
        
        // Fetch results
        if ($fetchMode) {
            return $this->stmt->fetch();
        } else if ($fetchMode == "all") {
            return $this->stmt->fetchAll();
        }

        $this->stmt->closeCursor();

        return $result;
    }
}
