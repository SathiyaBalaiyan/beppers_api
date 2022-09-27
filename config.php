<?php
	class Config {
	  // Database Details
	  private const DBHOST = 'localhost';
	  private const DBUSER = 'u762890487_beppers';
	  private const DBPASS = 'Beppers@123';
	  private const DBNAME = 'u762890487_beppers';
	  // Data Source Network
	  private $dsn = 'mysql:host=' . self::DBHOST . ';dbname=' . self::DBNAME . '';
	  // conn variable
	  protected $conn = null;

	  // Constructor Function
	  public function __construct() {
	    try {
	      $this->conn = new PDO($this->dsn, self::DBUSER, self::DBPASS);
	      $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	    } catch (PDOException $e) {
	      die('Connectionn Failed : ' . $e->getMessage());
	    }
	    return $this->conn;
	  }

	  // Sanitize Inputs
	  public function test_input($data) {
	    $data = strip_tags($data);
	    $data = htmlspecialchars($data);
	    $data = stripslashes($data);
	    $data = trim($data);
	    return $data;
	  }
	  
	  public function decodeArray($data)

	  {

		  $d=array();
  
		  foreach($data as $key=>$value)
  
		  {
  
		  if(is_string($key))
  
			  $key=htmlspecialchars_decode($key,ENT_QUOTES);
  
		  if(is_string($value))
  
			  $value=htmlspecialchars_decode($value,ENT_QUOTES);
  
		  else if(is_array($value))
  
			  $value=self::decodeArray($value);
  
		  $d[$key]=$value;
  
		  }
  
		  return $d;
  
	  }

	  // JSON Format Converter Function
	  public function message($content, $status, $data = null) {
	  	if ($data) {
	    	return json_encode(['message' => $content, 'error' => $status, 'data'=>$data]);
		} else {
			return json_encode(['message' => $content, 'error' => $status]);

		}
	  }
	}

?>
