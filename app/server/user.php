<?php

namespace Gsys;

class user{

	//Variable global
	private $db;
	private $api;
	
	//Se declara la variable de respeusta
	private $send = array("");
	
	function __construct(){

		$this->db = new db();
		$this->api = new api();
		$this->mailer = new mailer();

	}
	
	function editarUsuario($info){
		
		$str = "UPDATE usuarios 
				SET 
				EMAIL = '".$info["EMAIL"]."',
				NOMBRES = '".$info["NOMBRES"]."',
				APELLIDOS = '".$info["APELLIDOS"]."',
				TELEFONO = '".$info["TELEFONO"]."'
				WHERE 
				ID = '".$info["ID"]."'";
		
		$this->db->query($str);	
		
		$send["status"] = true;
		$send["info"] = $info;
		
		return $send;
		
	}
	
	function crearUsuario($info){
		
		$str = "SELECT
				usuarios.ID
				FROM
				usuarios
				WHERE
				usuarios.EMAIL = '".$info["EMAIL"]."'";
		
		$query_id = $this->db->query($str);	
		
		if(count($query_id) == 0){
			
			$pssw = $this->api->generateRandomString(10);
			
			try{
				
				$this->db->beginTransaction();
				$mailresp = $this->mailer->sendInfo($info["EMAIL"],"Bienvenido","su contraseña temporal es: ".$pssw);
				
				$str = "INSERT INTO usuarios
						(EMAIL, NOMBRES, APELLIDOS, TELEFONO, PSSW)
						VALUES
						('".$info["EMAIL"]."','".$info["NOMBRES"]."','".$info["APELLIDOS"]."',
						 '".$info["TELEFONO"]."','".$pssw."')";
				
				$query = $this->db->query($str);
				
			
				$str = "INSERT INTO usuario_empresa 
						(USUARIO_ID, EMPRESA, AREA, ID_INTERNO)
						VALUES
						(LAST_INSERT_ID(),'".$info["EMPRESA"]."','".$info["AREA"]."','".$info["ID_INTERNO"]."')";
				
				$query = $this->db->query($str);
				
				$this->db->commit();
				
				$send["status"] = true;
				$send["info"] = "SUCCCESS";
				return $send;
				
			}catch(\Exception $e){
				
				$send["status"] = false;
				$send["info"] = $e->getMessage();
				$this->db->rollBack();	
				
				return $send;
			}
		}else{
			
			$str = "SELECT
					usuario_empresa.USUARIO_ID,
					usuario_empresa.EMPRESA
					FROM
					usuario_empresa
					WHERE
					usuario_empresa.USUARIO_ID = '".$query_id[0]["ID"]."' AND
					usuario_empresa.EMPRESA = '".$info["EMPRESA"]."'";
			
			$query = $this->db->query($str);
			
			if(count($query) > 0){
				
				$send["status"] = false;
				$send["info"] = "Usuario ya existente";
				return $send;
			}else{
				
				try{
					$this->db->beginTransaction();
					$mailresp = $this->mailer->sendInfo($info["EMAIL"],"Bienvenido","Su cuenta fue agregada a la empresa: ".$info["EMPRESA"]);
					
					$str = "INSERT INTO usuario_empresa 
							(USUARIO_ID, EMPRESA, AREA, ID_INTERNO)
							VALUES
							('".$query_id[0]["ID"]."','".$info["EMPRESA"]."','".$info["AREA"]."','".$info["ID_INTERNO"]."')";
					
					$query = $this->db->query($str);
					
					$this->db->commit();
					
					$send["status"] = true;
					$send["info"] = "SUCCCESS";
					return $send;
					
				}catch(\Exception $e){
				
					$send["status"] = false;
					$send["info"] = $e->getMessage();
					$this->db->rollBack();	
					
					return $send;
				}
				
			}
		}
		
		
		

		
		
	}
	
	function chkLogin($info){
		
		if(isset($_SESSION["user"])){

			$send["status"] = true;
			$send["info"] = $_SESSION["tipo"];

			return $send;

		}else{

			$send["status"] = false;
			$send["info"] = "No session";

			return $send;
		}


	}
	
	function getUsertType(){
		
		$send["status"] = false;
		$send["info"] = $_SESSION["tipo"];

		return $send;
		
	}
	//Validación par inicio de sesión en la web
	function login($info){

		$str = "SELECT
				usuarios.ID,
				usuarios.NOMBRES,
				usuarios.APELLIDOS,
				usuarios.EMAIL,
				usuarios.TIPO,
				usuario_empresa.EMPRESA
				FROM
				usuarios
				INNER JOIN usuario_empresa ON usuarios.ID = usuario_empresa.USUARIO_ID
				WHERE
				usuarios.EMAIL = '".$info["user"]."' AND
				usuarios.PSSW = '".md5($info["pssw"])."'";
		
		$query = $this->db->query($str);	
		
		if(count($query) > 0){
			
			$resp["message"] = "";
			$resp["status"] = true;
			
			$this->sessionInit($query[0]);
			
			$resp["message"] = "SUCCCESS";
			$resp["status"] = true; 

		}else{
			
			$resp["message"] = "Usuario y/o contraseña incorrectos";
			$resp["status"] = false; 
		}
		
		
		return $resp;
	}

	function getUserSession($info){
		
		$send["status"] = true;
		$send["info"] = $_SESSION;

		return $send;
	}
	
	function sessionInit($info){

		
		$_SESSION["user"] = $info["ID"];
		$_SESSION["names"] = $info["NOMBRES"]." ".$info["APELLIDOS"];
		$_SESSION["tipo"] = $info["TIPO"];
		$_SESSION["empresa"] = $info["EMPRESA"];
		
		return $info;

	}

	function logout($info){

		session_destroy();

		$send["status"] = true;
		$send["info"] = $info;

		return $send;
	}
	
	function getUserList($info){
		
		$whereArray = [];
		$whereString = "";

		if($info["EMAIL"] != ""){

			$whereArray[] = "usuarios.EMAIL LIKE UPPER('%".$info["EMAIL"]."%')";

		}
		if($info["NOMBRES"] != ""){

			$whereArray[] = "usuarios.NOMBRES LIKE UPPER('%".$info["NOMBRES"]."%')";

		}
		if($info["APELLIDOS"] != ""){

			$whereArray[] = "usuarios.APELLIDOS UPPER('%".$info["APELLIDOS"]."%')";

		}
		

		if(count($whereArray) > 0){

			$whereString = "WHERE ".implode(" AND ", $whereArray);

		}
		

		$str = "SELECT
				usuarios.ID,
				usuarios.EMAIL,
				usuarios.NOMBRES,
				usuarios.APELLIDOS,
				usuarios.ACTIVO,
				usuarios.TELEFONO,
				usuarios.TIPO,
				usuarios.PSSW
				FROM
				usuarios ".$whereString;
				
		$query = $this->db->query($str);	
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;
		
	}
	
}