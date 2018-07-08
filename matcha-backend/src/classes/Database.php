<?php
    class Database{
        private static $conn = null;

        public function __construct(){
            $globs = $GLOBALS['api_config']['server'];
            $HOST = Config::get('server/host');
            $DB_DSN = Config::get('server/db_name');
            $DB_USER = Config::get('server/db_user');
            $DB_PASSWORD =  Config::get('server/db_password');

            if (!isset(self::$conn)){
                try{
                    self::$conn = new PDO("mysql:host=". $HOST .";dbname=". $DB_DSN, $DB_USER, $DB_PASSWORD);
                    self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }catch(PDOException $exc){
                    throw new Exception ("Could not connect to database");
                }
            }
        }

        public function connection(){
            if (!isset(self::$conn)){
                self::$conn = new Database();
            }
            return (self::$conn);
        }

        public function server_connection(){
            $globs = $GLOBALS['api_config']['server'];
            $HOST = Config::get('server/host');
            $DB_USER = Config::get('server/db_user');
            $DB_PASSWORD =  Config::get('server/db_password');
            $conn = false;

            try{
                $conn = new PDO("mysql:host=". $HOST .";", $DB_USER, $DB_PASSWORD);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }catch(PDOException $exc){
                die($exc->getMessage());
                throw new Exception ("Could not connect to database: ");
            }
            return ($conn);
        }

        public function rawQuery($query, $returnData = false, $connection = false){
            //echo "<h3>$query</h3>";
            try{
                $stmt = '';
                if ($connection)
                    $stmt = $connection->prepare($query);
                else
                    $stmt = self::$conn->prepare($query);
                $stmt->execute();

                if ($returnData === false)
                    return ($stmt);
                else{
                    $data['rows'] = self::getRows($stmt, 0);
                    $data['rowCount'] = self::getCount($stmt);
                    return ($data);
                }
            }catch(Exception $exc){
                ft_put_error($exc->getMessage());
            }
            return (false);
        }

        public function select($table, $where = array(), $filter = null, $ret_obj = null){
            $query = "SELECT * FROM `$table`";

            if (!empty($where))
                $query = $query . self::_build_where($where);
            
            $query = "$query $filter;";

            try{
                $stmt = self::$conn->prepare($query);

                if (!empty($where))
                    self::_bind_where($stmt, $where);
                $stmt->execute();

                if (isset($ret_obj)){
                    if ($ret_obj === true){
                        $data['rows'] = self::getRows($stmt, 0);
                        $data['rowCount'] = self::getCount($stmt);
                        return ((object)$data);
                    }
                    $data['rows'] = self::getRows($stmt, 0);
                    $data['rowCount'] = self::getCount($stmt);
                    return ((array)$data);
                }
                return ($stmt);
            }catch(Exception $exc){
                ft_put_error($exc->getMessage());
            }
            return (false);
        }

        public function insert($table, $elements = array()){
            $query = "INSERT INTO `$table` (";
            $keys = array();
            $i = 0;

            foreach (array_keys($elements) as $el){
                array_push($keys, $el);
            }
            while (isset($keys[$i])){
                $query = "$query `$keys[$i]`";
                if (isset($keys[$i + 1]))
                    $query = "$query, ";
                $i++;
            }
            
            $query = "$query) VALUES (";

            $i = 0;
            while (isset($keys[$i])){
                $query = $query.' :'.$keys[$i];
                if (isset($keys[$i + 1])){
                    $query = $query.', ';
                }
                $i++;
            }

            $query = "$query);";

            try{
                $stmt = self::$conn->prepare($query);
                self::_bind($stmt, $elements);
                $stmt->execute();
                return ($stmt);
            }catch(Exception $exc){
                ft_put_error($exc->getMessage());
            }
            return (false);
        }

        public function update($table, $elements, $where){
            $query = "UPDATE `$table` SET ";

            $i = count($elements) - 1;
            foreach (array_keys($elements) as $el){
                $query = "$query `$el` = :$el";
                if ($i > 0)
                    $query = "$query, ";
                $i--;
            }

            $query = "$query " . self::_build_where($where);            
            $query = "$query;";

            try{
                $stmt = self::$conn->prepare($query);
                self::_bind($stmt, $elements);
                self::_bind_where($stmt, $where);
                $stmt->execute();
                return ($stmt);
            }catch(Exception $exc){
                ft_put_error($exc->getMessage());
            }
            return (false);
        }

        public function delete($table, $where = array()){
            $query = "DELETE FROM `$table` ";

            if (empty($where))
                return (false);
            $query = $query . self::_build_where($where);
            $query = "$query;";

            try{
                $stmt = self::$conn->prepare($query);
                self::_bind_where($stmt, $where);
                $stmt->execute();
                return ($stmt);
            }catch(Exception $exc){
                ft_put_error($exc->getMessage());
            }
            return (false);
        }

        public function getRows($stmt, $type = 1){
            $ret = null;

            if ($type === 1)
                $ret = $stmt->fetchAll(PDO::FETCH_OBJ);
            else
                $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ($ret);
        }

        public function getCount($stmt){
            return ($stmt->rowCount());
        }

        private function _bind($stmt, $elements){
            foreach (array_keys($elements) as $el){
                $stmt->bindparam(":$el", $elements[$el]);
            }
        }
        
        private function _bind_where($stmt, $where){
            $i = 0;

            while (isset($where[$i])){
                if (self::_is_sql_op(trim($where[$i]))){
                    $stmt->bindparam(":" . $where[$i - 1], $where[$i + 1]);
                    $i++;
                }
                $i++;
            }
        }

        private function _build_where($where){
            $ret = ' WHERE ';

            $i = 0;
            while (isset($where[$i])){
                $ret = "$ret $where[$i]";

                if (trim($where[$i]) === '='){
                    $ret = $ret . ' :' . $where[$i - 1];
                    $i++;
                }
                $i++;
            }
            return ($ret);
        }

        private function _is_sql_op($op){
            $ops = array("=", ">", "<", ">=", "<=");

            if (in_array($op, $ops))
                return (true);
            return (false);
        }
    }
?>