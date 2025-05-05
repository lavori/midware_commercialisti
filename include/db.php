<?php
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection; // Dichiara esplicitamente la proprietà

    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }
    public function connect() {
        $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);
        if (!$this->connection) {
            die("Errore di connessione al database: " . mysqli_connect_error());
        }
    }
    public function disconnect() {
        mysqli_close($this->connection);
    }
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $query = "INSERT INTO $table ($columns) VALUES ($values)";
        //echo $query; echo"<hr>";
        mysqli_query($this->connection, $query);
    }
    public function update($table, $data, $where) {
        $set = "";
        foreach ($data as $column => $value) {
            if ($value === "NOW()") {
                $set .= "$column = $value, ";
            } elseif (is_numeric($value) || $value=="NULL") {
                $set .= "$column = $value, ";
            } else {
                $set .= "$column = '$value', ";
            }
        }
        $set = rtrim($set, ", ");
        $query = "UPDATE $table SET $set WHERE $where";
        //debug
        //echo $query; exit();
        mysqli_query($this->connection, $query);
    }
    public function select($table, $columns = "*", $where = "") {
        if(is_array($columns)){
            $colonne=implode("," ,   $columns);
        } else {
            $colonne=$columns;
        }
        $query = "SELECT ". $colonne . " FROM $table";
        if ($where != "") {
            if(is_array($where)){
                $query .=" WHERE 1";
                foreach($where as $key => $value){
                    $value_array = explode("|", $value);
                    $query .= " and $key $value_array[0] '$value_array[1]'";
                }        
            }else{
                $query .= " WHERE $where";
            }
        }
        //debug
        //echo $query; exit();
        $result = mysqli_query($this->connection, $query);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    public function query($query) {
        $result = mysqli_query($this->connection, $query);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    public function delete($table, $where) {
        $query = "DELETE FROM $table WHERE $where";
        mysqli_query($this->connection, $query);
    }
    public function query_nr($query) {
        $result = mysqli_query($this->connection, $query);
    }
    public function prepareInsert($table, $columns, $placeholders) {
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->stmt = mysqli_prepare($this->connection, $sql);
        return $this->stmt;
    }
    public function bindParams($types, ...$values) {
        $bindParams = [$this->stmt, $types];
        foreach ($values as $value) {
            $bindParams[] = &$value;
        }
        call_user_func_array('mysqli_stmt_bind_param', $bindParams);
    }
    public function countRows($table, $where = "") {
        $query = "SELECT COUNT(*) AS total_rows FROM $table";
    
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
    
        $result = mysqli_query($this->connection, $query);
        $row = mysqli_fetch_assoc($result);
    
        return $row['total_rows'];
    }    
    public function lastId() {
        return mysqli_insert_id($this->connection);
    }
}

?>
