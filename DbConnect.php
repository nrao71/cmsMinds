<?php
/**
 * Handling database connection
 *
 * @author manikanta sarma
 * @link URL Tutorial link
 */
class DbConnect
{
    private $conn;
    function __construct() {
    }
    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect()
    {
   //      $host="localhost";
            // $dbuser="yesinter_raju";
    		// $dbpassword="Raju@2020";
			// $database="pla";
      $host="localhost";
      $dbuser="happygo2409";
      $dbpassword="happygo2409@123";
      $database="happygo2409";
	  $con = new mysqli($host, $dbuser, $dbpassword, $database);//Local
      return  $con;
    }
}
?>
