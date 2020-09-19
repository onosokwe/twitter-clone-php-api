<?php
// HOST is the hostname which is localhost,
// USER is the user name which is root,
// PASSWORD is the password of the user which is empty by default,
// DATABASE is the database which we need to connect to.
define('HOST', 'localhost');
define('USER', 'root');
define('PASSWORD', '');
define('DATABASE', '');

// This class is only used to establish a connection to our database
// We created to return a private and static variable so that 
// connection to our database can only be established from a class that extends this class
// This is some form of security for our application
class appStart {
	// declare a private static variable that will only be
	// accessible in the class that extends it, which is eCommerceApp
	// which we have created below
	private static $connect;
	public function connect($db){
		if(!empty($db)){
			$connect = new MySQLi(HOST,USER,PASSWORD,$db);
		} else {
			$connect = new MySQLi(HOST,USER,PASSWORD,DATABASE);
		}
		return $connect;
	}
}

class sqlOps extends appStart {
	// Declare the public variables for basic sql operations we will use later on
	// We will use these variables later on in creating our queries/methods

	public $conn; // this holds the connection string returned from instantiating the appStart class using the constructor method
	public $sql; // this holds the sql query that will be sent from our function file
	public $result; // this holds the result of the query that the methods will run, which will be received by the function. We will explain this further later

	// the constructor is called first when the class is instantiated
	// hence we use it to establish our database connection
	// with that database connection we can now start making our queries

	public function __construct($db=''){
    	$this->conn = $this->connect($db);
		$this->result = NULL;
	}

	// below here we will make methods that will perform
	// all the queries that we need which are basically 
	// the CRUD operations (Create, Read, Update, Delete)
	// and a few other async operations
	// NOTE that backend is not only CRUD operations, 
	// but they are the basic operations

	// This is a private method that will be doing all our 
	// direct queries into the DATABASE for us. 
	// We have written it as a separate method so that 
	// we can control it
	// Also we made made it a private method to limit 
	// its use to only this class where it is created
	// This restricts database access to only the app layer.
	private function myquery($sql){ 
		// this is SQL statement we want to perform via the query
		$this->sql = $sql; 
		// this is where we are doing the operation via MySQL query
		// function. we assigned the result of the operation to
		// variable result predefined in line 36 
		$this->result = $this->conn->query($this->sql);
		// if the result is TRUE (that is no error), 
		// return the result  
		if($this->result){   
			return $this->result;
		} 
		// else die the operation and return the error.
		else { 
			die ($this->conn->error."; 
				Problem with Query \"".$this->sql."\"\n"); 
		}
	}

	//////////////////////////////////////////////
	/////// C R U D   O P E R A T I O N S ////////
	//////////////////////////////////////////////

	// This is the CREATE operation. It inserts data into
	// the database using the myquery method created above. 
	// We are receiving the column names and values as an array.
	// Therefore they must match. You cannot have three columns 
	// specified and two data values to be inserted, else this 
	// method will throw an error.
	public function insert($table, $cols, $values){
		// the cols is received as an array and we use the 
		// implode function to  
		$cols = (is_array($cols)) ? implode(',',$cols) : $cols;
		$values = (is_array($values)) ? implode(',',$values) : $values;
		if(substr($values,0,1) == '('){
			$sql = $this->myquery("INSERT INTO {$table} ({$cols}) VALUES {$values}");
		} else {
			$sql = $this->myquery("INSERT INTO {$table} ({$cols}) VALUES ({$values})");
		}
		if($sql){ return $this->conn->affected_rows; } else { return FALSE; }
	}

	// This is the READ operation. We will use it to read in other
	// words select data from the database.
	// We have added some optional parameters for the method by
	// assigning them to an empty string.
	// This method returns the number of items that meets the 
	// condition of the parameters as shown on line 96.
	// If you want to select and fetch the data use the next
	// method which is select_fetch 
	public function select($table, $cols='', $where='', $orderBy='', $limit=''){
		$sel = "SELECT {$cols} FROM {$table} {$where} {$orderBy} {$limit}";
		if($this->myquery($sel)){ 
			return $this->result->num_rows; 
		} return FALSE;
	}

	// This is also a READ operation. It is used to select and fetch
	// the data from the database. The data is displayed as an array
	// which could be converted to JSON at the front end	
	public function select_fetch($table, $cols='', $where='', $orderBy='', $limit=''){
		if($sel = $this->select($table,$cols,$where,$orderBy,$limit)){
			$fetch = array(); 
			$sn = 0;
			while($row = $this->result->fetch_assoc()){	
				$fetch[] = $row;
			} return $fetch;
		} else { return ($sel === 0) ? $sel : FALSE; }
	}

	// This is the UPDATE operation. It is used to update data in
	// the database. It receives as a parameter a variable that is 
	// an array of column and value pairs for example. 
	// $colsVals = "`name`='James Cameron',`email`='james@cameron.com'"
	public function update($table, $colsVals, $where){
		if(empty($where)){ die("Please, define a WHERE"); }
		if(empty($colsVals)){ die("Please, specify COLUMN=['VALUE']"); }
		if(is_array($where)){ 
			list($where,$whrUNIQUE) = $where; 
			if($this->select($table,'',$whrUNIQUE)){ return FALSE; }
		}
		$colsVals = (is_array($colsVals)) ? implode(',',$colsVals) : $colsVals; 
		$sql = $this->myquery("UPDATE {$table} SET {$colsVals} {$where}"); 
		if($sql){ return $this->conn->affected_rows; } else { return FALSE; } 
	}
	
	// This is also a READ operation. It is used to select and fetch
	// the all instances of the user liking the particluar tweet 
    public function getTweetLikes($tweetid){
		$table = 'tweets'; 
		$where = "`tweetid` = '$tweetid'"; 
		$limit = '1';
		$sql = $this->myquery("SELECT likes FROM {$table} WHERE {$where} LIMIT {$limit}");
		$rowcount = mysqli_fetch_assoc($sql);
		if (!is_null($rowcount)) {
			$last = end($rowcount); 
			return $last;
		} else {
			return false;
		}
    }
}
?>
