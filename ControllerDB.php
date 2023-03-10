<?php

class DB
{
    // Объект PDO
	public static $dbh = null;
 	
	// Statement Handle
	public static $sth = null;
 
	// Выполняемый SQL запрос
	public static $query = '';
 
    public static $dsn = "mysql:dbname=u1615265_telegramm;host=localhost;charset=UTF8";
    public static $user = "u1615265_root";
    public static $password = "uI9jV2iG3ouH5h";

	// Открыть соединения
    public static function getDbh(){
        if (!self::$dbh) {
			try {
				self::$dbh = new PDO(
					self::$dsn, 
					self::$user, 
					self::$password
				);
				self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			} catch (PDOException $e) {
				exit('Error connecting to database: ' . $e->getMessage());
			}
		}
		return self::$dbh; 
    }
    
	// Закрытие соединения
	
	// public static function destroy()
	// {	
	// 	self::$dbh = null;
	// 	return self::$dbh; 
	// } 
    
    public static function getRow($query, $param = [])
	{
		self::$sth = self::getDbh()->prepare($query);
		self::$sth->execute((array) $param);
		return self::$sth->fetchAll(PDO::FETCH_ASSOC);		
	}

	public static function getcheckMessageExistance($query, $param = [])
	{
		self::$sth = self::getDbh()->prepare($query);
		self::$sth->execute($param);
		return self::$sth->fetchAll(PDO::FETCH_ASSOC);		
	}

    public static function getAll($query, $param = [])
	{
		self::$sth = self::getDbh()->prepare($query);
		self::$sth->execute((array) $param);
		return self::$sth->fetchAll(PDO::FETCH_ASSOC);		
	}

    public static function updateRow($query, $param = [])
	{
		self::$sth = self::getDbh()->prepare($query);
		// file_put_contents(__DIR__ . '/error.json', json_encode(self::$sth,true), FILE_APPEND|LOCK_EX );

		return self::$sth->execute((array) $param);
	}

	public static function add($query, $param = [])
	{
		self::$sth = self::getDbh()->prepare($query);
		// file_put_contents(__DIR__ . '/error.json', json_encode(self::$sth,true), FILE_APPEND|LOCK_EX );
		return (self::$sth->execute((array) $param));
	}
}
?>