<?php
defined('BASEPATH') OR exit('{\"Out\":\"No direct script access allowed\"}');
/**
*
* Class based on PDO for easily calling sql statements.
* @since v20160515
* @package SQL4SYSTEM
* @lastModified 2015-12-18 4:21 PM
* @access public
* @name get | result
* @param ARRAY $w 2D Array containing Key and Value
* @copyright (c) 2016, Ravinder Payal<mail@ravinderpayal.com>
*
*/
class sql extends Database{
    private static $PDOtype = array("integer"=>PDO::PARAM_INT,"string"=>PDO::PARAM_STR,"boolean"=>PDO::PARAM_BOOL);
    public function __construct(){
        //Following Constants should be predefined :- host , username , password , database
        /*******Right Now we will try PDO But will write code IN such a way that we can change anytime*********/
        //$this->DB = new MySQLi(SQL_HOST,SQL_USER,SQL_PASS,SQL_DB) or die("error in servers");
        try{
                $this->DB = new PDO("mysql:host=".SQL_HOST.";dbname=".SQL_DB.";",SQL_USER ,SQL_PASSWORD);
                $this->DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               // $this->DB->exec("SET NAMES utf8mb4");
         }
        catch(PDOException $e){
                $this->halt($e);
         }
    }
    /**
    *
    * Private Variable for storing resulting SQL
    *@access private
    */
    private $sql;

    /**
    * Private Variable for storing Params that will be binded during execution of SQL
    *@access private
    */
    private $params = array();
    
    /**
    * Function for selecting columns
    * @param $a : Column names comma separated<!(array)>
    * @param $b : array for other options
    * @since v20160515
    * @lastModified 2015-05-15 7:30 PM
    * @access public
    * @name get | result
    *
    */
    public function select($a = "*"){
        $this->sql = "SELECT $a";
        /**
        *
        *Param no more used as ARRAY
        foreach($a as $cn){
                $sql.=$cn.",";
        }
        $sql=rtrim($sql,",");
        *
        */
        return $this;
    }

    /**
    * Function for updating a table
    * @param $t : Table Name
    * @since v20160515
    * @lastModified 2015-05-15 7:30 PM
    * @access public
    * @name get | result
    *
    */
   public function update($t){
       $this->sql = "UPDATE `$t` ";
        return $this;
   }

    /**
    * Function for updating a table
    * @param $t : Table Name
    * @since v20160515
    * @lastModified 2015-05-15 7:30 PM
    * @access public
    * @name get | result
    *
    */
   public function delete(){
       $this->sql = "DELETE ";
        return $this;
   }
   
   /**
    * 
    *  Function for setting table name
    * @param String $table Name of the table that will be used
    * @since v20160515
    * @lastModified 2015-05-15 7:30 PM
    * @access public
    * @name from
    *
    */
    public function from($table){
                   $this->sql.=" FROM ".$table;
                   return $this;
    }
    public function limit($limit){
        $this->sql.=" LIMIT ".$limit;
        return $this;
    }

    /**
     * 
     * Function for Adding Where Condition
     * @since v20160515
     * @lastModified 2015-05-15 7:30 PM
     * @access public
     * @name where
     * @param ARRAY $w 2D Array containing Key and Value
     * 
     */
    public function where($w){
        $this->sql.=" WHERE (";
        $this->whereIt_a($w,"WHERE");
        $this->sql.=") ";
        return $this;
    }
    /**
     * 
     * Function for Adding Where Condition with Additional Parenthesis appeneded just after where condition
     * Use Case:- When grouping of conditions is required
     * Note:- Add `parenthesisEnd()` when you want to end the current group
     * @since v20160515
     * @lastModified 2015-05-15 7:30 PM
     * @access public
     * @name where
     * @param ARRAY $w 2D Array containing Key and Value
     * 
     */
    public function where_ps($w){
        $this->sql.=" WHERE ((";
        $this->whereIt_a($w,"WHEREPS");
        $this->sql.=") ";
        return $this;
    }
    /**
     * 
     * Function for Adding Where Condition with prepended `AND`
     * @since v20170322
     * @lastModified 2017-03-22 7:50 PM
     * @access public
     * @name or_where
     * @param ARRAY $w 2D Array containing Key and Value
     * 
     */
    public function and_where($w){
        $this->sql.=" AND (";
        $this->whereIt_a($w,"ANDWHERE");
        $this->sql.=") ";
        return $this;
    }
    /**
     * 
     * Function for Adding Where Condition with prepended `OR`
     * @since v20170322
     * @lastModified 2017-03-22 7:50 PM
     * @access public
     * @name or_where
     * @param ARRAY $w 2D Array containing Key and Value
     * 
     */
    public function or_where($w){
        $this->sql.=" OR (";
        $this->whereIt_a($w,"ORWHERE");
        $this->sql.=") ";
        return $this;
    }

    /**
     * 
     * Function for Adding Where Condition with no bindings on Variables and prepended `and`.
     * @since v20170322
     * @lastModified 2017-03-22 7:50 PM
     * @access public
     * @name or_where
     * @param ARRAY $w 2D Array containing Key and Value
     * 
     */
    public function nb_and_where($w){
        $this->sql.=" AND (";
        $i=0;
        foreach($w as $j=>$k){
            $this->sql.=($i==0?" ":" and ")."$j = $k";
            $i++;
        }
        $this->sql.=") ";
        return $this;
    }

    /**
    *
    * Does the common task of all where functions with "AND" operation between two conditions.
    * @var $w Takes meta info about where conditions.
    * @var $idf Takes identifier of callee.
    *
    */
    private function whereIt_a($w,$idf){
        $i=0;
        foreach($w as $j=>$k){
            $operator = "=";
            if(is_array($k)){
                $operator = $k[0];
                $k = $k[1];
            }
            $j_ = str_replace(".","",$j);
            $this->params[] = array($k,$idf."WHERE".$j_);
            $this->sql.=($i==0?" ":" AND ")."$j $operator :".$idf."WHERE".$j_;
            $i++;
        }
    }
    /**
    *
    * Does the common task of all where functions with "OR" operation between two conditions
    * @var $w Takes meta info about where conditions.
    * @var $idf Takes identifier of callee.
    *
    */
    private function whereIt_o($w,$idf){
        $i=0;
        foreach($w as $j=>$k){
            $operator = "=";
            if(is_array($k)){
                $operator = $k[0];
                $k = $k[1];
            }
            $j_ = str_replace(".","",$j);
            $this->params[] = array($k,$idf."WHERE".$j_);
            $this->sql.=($i==0?" ":" OR ")."$j $operator :".$idf."WHERE".$j_;
            $i++;
        }
    }

    /**
     * 
     * Function for Adding SET clause in update statements
     * @since v20160515
     * @lastModified 2015-05-15 7:30 PM
     * @access public
     * @name set
     * @uses SQL UPDATE It will be used for setting values of fields during UPDATE
     * @param ARRAY $w 2D Array containing Key and Value
     * 
     */
    public function set($w){
        $this->sql.=" SET ";
        $i=0;
        foreach($w as $j=>$k){
                $this->params[] = array($k,"SET$j");
                $this->sql.=($i==0?" ":" , ")."$j = :SET$j";
                $i++;
        }
        return $this;
    }

    //--------------Insert Commands

    /**
     * @param $t String Name of Table in which data will be inserted
     * @return $this
     */
    public function insertInto($t){
        $this->sql="INSERT INTO ".$t;
        return $this;
    }

    /**
     * @param $v Array Values of columns, which will be inserted into Table
     * @return $this
     */
    public function values($v){
        $i=0;
        $part1=null;
        $part2=null;
        foreach($v as $j=>$k){
            $this->params[$i] = array($k,"VALUES$j");
            $part1.=($i==0?" ( ":",").$j;
            $part2.=($i==0?" VALUES ( ":",").":VALUES$j";
            $i++;
        }
        $part1.=")";
        $part2.=")";
        $this->sql.=$part1.$part2;
        return $this;
    }

//-------Some high level features, use with precautions

    /**
    *
    *@Param $t String Name of table which will be joined
    *
    */
    public function rightJoin($t){
        $this->sql.=" RIGHT JOIN $t";
        return $this;
    }

    /**
    *
    *@Param $c String Conditions to be considered for joining two tables
    *
    */
    public function on($c){
        $this->sql.=" ON($c)";
        return $this;
    }



//-------------------Execution and Result Functions



    /**
     * 
     *  Function for Executing Query
     * @since version20160515
     * @lastModified 2015-05-15 8:00 PM
     * @access public
     * @name get | result
     * 
     */
    public  function exec(){
        $stmt = $this->DB->prepare($this->sql);
        if (isset($this->params)) {
            foreach ($this->params as $p) {
                $stmt->bindParam(":". $p[1],$p[0], self::$PDOtype[gettype ( $p[0] )]);
            }
        }
        try{
            $stmt->execute();
            $this->reset();
            return $stmt->rowCount()==0?false:$this->DB->lastInsertId();
        }
        catch(PDOException $e){
            $this->halt($e);
         }
    }
//----------------------Formatting functions

    public function parenthesisStart(){
        $this->sql.=" ( ";
        return $this;
    }
    public function parenthesisEnd(){
        $this->sql.=" ) ";
        return $this;
    }
    /**
     * 
     *  Function for getting Result
     * @since version20160515
     * @lastModified 2016-05-15 8:00 PM
     * @access public
     * @name get | result
     * 
     */
    public function result(){
        $stmt = $this->DB->prepare($this->sql);
        if (isset($this->params)) {
            foreach ($this->params as $p) {
                $stmt->bindParam(":". $p[1],$p[0], self::$PDOtype[gettype ( $p[0] )]);
            }
        }
        try{
            //var_dump($this->sql);
            $stmt->execute();
            $this->reset();
            $this->rowCount=$stmt->rowCount();
            return $this->rowCount==0?false:$stmt->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e){
            $this->halt($e);
         }
    }
    
    /**
     * 
     *  Function for resetting the current SQL statement 
     * @since v20160515
     * @lastModified 2016-05-15 7:30 PM
     * @access public
     * @name get | result
     * 
     */
    public function reset(){
        $this->sql = NULL;
        $this->params=array();
    }
}