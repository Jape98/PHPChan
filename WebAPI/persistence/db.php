<?php

class DB {

	private static $DBConnection;

	//If different servers are needed for reating and writing. Right now the connections are to the same server. 
	public static function connectDB() {

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {

			#region READ db connection
			if(self::$DBConnection === null) {

				try {
					self::$DBConnection = new PDO('mysql:host=localhost;dbname=chan;charset=utf8', 'jape', 'root');
					self::$DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					self::$DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

					return self::$DBConnection;

				} catch (PDOException $e) {
					//0 = php error logfile
					error_log("Connection error - ".$e, 0);
					$response = new ResponseModel();
					$response->setHttpStatusCode(500);
					$response->setSuccess(false);
					$response->addMessage("Database connection error");
					$response->send();
					exit();
				}
			}
			#endregion

		} else {

			#region WRITE db connection
			if(self::$DBConnection === null) {

				try {
					self::$DBConnection = new PDO('mysql:host=localhost;dbname=chan;charset=utf8', 'jape', 'root');
					self::$DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					self::$DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

					return self::$DBConnection;

				} catch (PDOException $e) {
					//0 = php error logfile
					error_log("Connection error - ".$e, 0);
					$response = new ResponseModel();
					$response->setHttpStatusCode(500);
					$response->setSuccess(false);
					$response->addMessage("Database connection error");
					$response->send();
					exit();
				}
			}
			#endregion

		}	
	}	
}
