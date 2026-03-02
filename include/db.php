<?php

class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;

    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    public function connect() {
        $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);
        if ($this->connection->connect_error) {
            die("Errore di connessione al database: " . $this->connection->connect_error);
        }
    }

    public function disconnect() {
        if ($this->connection) {
            mysqli_close($this->connection);
        }
        $this->connection = null; // Important to prevent errors on multiple disconnects
    }

    public function beginTransaction() {
        $this->connection->begin_transaction();
    }

    public function commit() {
        $this->connection->commit();
    }

    public function rollback() {
        $this->connection->rollback();
    }

    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = "";
        foreach ($data as $value) {
            if ($value === null) {
                $values .= "NULL, ";
            } elseif (is_string($value)) {
                $values .= "'" . $this->connection->real_escape_string($value) . "', ";
            } else {
                $values .= $value . ", ";
            }
        }
        $values = rtrim($values, ", ");

        $query = "INSERT INTO $table ($columns) VALUES ($values)";
        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Errore nella query di inserimento: " . $this->connection->error . " - Query: " . $query);
        }

        return $this->connection->insert_id; // Return last inserted ID
    }

    public function update($table, $data, $where) {
        $set = "";
        foreach ($data as $column => $value) {
            if ($value === null) {
                $set .= "$column = NULL, ";
            } elseif ($value === "NOW()") {
                $set .= "$column = NOW(), ";
            } elseif (is_numeric($value)) {
                $set .= "$column = $value, ";
            } else {
                $set .= "$column = '" . $this->connection->real_escape_string($value) . "', ";
            }
        }
        $set = rtrim($set, ", ");

        $query = "UPDATE $table SET $set WHERE $where";
        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Errore nella query di aggiornamento: " . $this->connection->error . " - Query: " . $query);
        }

        return $this->connection->affected_rows; // Return number of affected rows
    }

    public function select($table, $columns = "*", $where = "") {
        $query = "SELECT " . (is_array($columns) ? implode(", ", $columns) : $columns) . " FROM $table";

        if ($where) {
            $query .= " WHERE " . (is_array($where) ? $this->buildWhereClause($where) : $where);
        }

        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Errore nella query di selezione: " . $this->connection->error . " - Query: " . $query);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function query($query) {
        $result = mysqli_query($this->connection, $query);
    
        // Modifica qui: controlla se la query ha avuto successo
        if ($result === TRUE) {
            return TRUE; // Oppure potresti restituire il numero di righe affette:  return mysqli_affected_rows($this->connection);
        } elseif ($result) { // Se è un SELECT, continua a recuperare i dati
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            return $rows;
        } else {
            // Gestione dell'errore (importante!)
            die("Errore nella query: " . mysqli_error($this->connection) . " - Query: " . $query);
        }
    }

    public function query_nr($query) {
        $result = mysqli_query($this->connection, $query);
        if (!$result) {
            throw new Exception("Errore nella query: " . $this->connection->error . " - Query: " . $query);
        }
        return $result; // Restituisci il risultato per eventuali controlli
    }

    public function delete($table, $where) {
        $query = "DELETE FROM $table WHERE $where";
        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Errore nella query di eliminazione: " . $this->connection->error . " - Query: " . $query);
        }

        return $this->connection->affected_rows;
    }

    public function prepareInsert($table, $columns, $placeholders) {
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->stmt = $this->connection->prepare($sql);

        if (!$this->stmt) {
            throw new Exception("Errore nella preparazione della query: " . $this->connection->error);
        }

        return $this->stmt;
    }

    public function bindParams($types, ...$values) {
        if (!$this->stmt) {
            throw new Exception("Errore: Statement non inizializzato.");
        }

        $bindParams = [$types];
        foreach ($values as &$value) {
            $bindParams[] = &$value;
        }
        if (!call_user_func_array([$this->stmt, 'bind_param'], $bindParams)) {
            throw new Exception("Errore nel binding dei parametri: " . $this->stmt->error);
        }
    }

    public function execute() {
        if (!$this->stmt) {
            throw new Exception("Errore: Statement non inizializzato.");
        }
        if (!$this->stmt->execute()) {
            throw new Exception("Errore nell'esecuzione dello statement: " . $this->stmt->error);
        }
        return $this->stmt->insert_id ?: $this->stmt->affected_rows;
    }

    public function countRows($table, $where = "") {
        $query = "SELECT COUNT(*) AS total_rows FROM $table";
        if ($where) {
            $query .= " WHERE " . (is_array($where) ? $this->buildWhereClause($where) : $where);
        }

        $result = $this->connection->query($query);

        if (!$result) {
            throw new Exception("Errore nella query di conteggio righe: " . $this->connection->error . " - Query: " . $query);
        }

        return (int)$result->fetch_object()->total_rows;
    }

    public function lastId() {
        return $this->connection->insert_id;
    }

    private function buildWhereClause($where) {
        $clause = "1"; // Default to always true if where array is empty
        foreach ($where as $key => $value) {
            if (is_array($value)) {
                list($operator, $val) = $value;
                $clause .= " AND $key $operator '" . $this->connection->real_escape_string($val) . "'";
            } else {
                $clause .= " AND $key = '" . $this->connection->real_escape_string($value) . "'";
            }
        }
        return $clause;
    }

    /**
     * Escapes special characters in a string for use in an SQL statement, using the current connection
     */
    public function escapeString(string $value): string {
        return $this->connection->real_escape_string($value);
    }
}
?>
