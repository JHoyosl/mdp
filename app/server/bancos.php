<?php

namespace Gsys;

class bancos{

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
	
	function segmentarFormato($info){
		
		$resp["message"] = $_FILES["0"];
		$resp["status"] = true;

		return $resp;
		
	}
	
	function getBancoListSelect(){
		
		$str = "SELECT
				bancos.BANCO_ID AS ID,
				bancos.NOMBRE AS DESCRIPCION
				FROM
				bancos 
				ORDER BY DESCRIPCION DESC";
				
		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;
		
	}
	
	function getBancosList($info){
		
		$whereArray = [];
		$whereString = "";

		if($info["bancoId"] != ""){

			$whereArray[] = "bancos.BANCO_ID LIKE UPPER('%".$info["bancoId"]."%')";

		}
		if($info["nombre"] != ""){

			$whereArray[] = "bancos.NOMBRE LIKE UPPER('%".$info["nombre"]."%')";

		}
		if($info["ruta"] != ""){

			$whereArray[] = "bancos.RUTA UPPER('%".$info["ruta"]."%')";

		}
		

		if(count($whereArray) > 0){

			$whereString = "WHERE ".implode(" AND ", $whereArray);

		}

		$str = "SELECT
				bancos.BANCO_ID,
				bancos.NOMBRE,
				bancos.HEAD,
				bancos.RUTA,
				bancos.NOTAS
				FROM
				bancos ".$whereString;
				
		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;
		
	}
	
	function editarBanco($info){


		$str = "UPDATE bancos 
				SET 
				NOMBRE = '".$info["nombre"]."',
				HEAD = '".$info["head"]."',
				RUTA = '".$info["ruta"]."',
				NOTAS = '".$info["notas"]."' 
				WHERE 
				BANCO_ID = '".$info["bancoId"]."'";

		$this->db->query($str);

		$send["status"] = true;
		$send["info"] = $info;
		
		return $send;

	}

	function crearBanco($info){
		
		
		$str = "SELECT
				bancos.BANCO_ID
				FROM
				bancos
				WHERE
				bancos.BANCO_ID = '".$info["bancoId"]."'";
		
		$query_id = $this->db->query($str);	
		
		if(count($query_id) == 0){
			
			$str = "INSERT INTO bancos 
					(BANCO_ID, NOMBRE, HEAD, RUTA, NOTAS)
					VALUES
					('".$info["bancoId"]."','".$info["nombre"]."','".$info["head"]."','".$info["ruta"]."','".$info["notas"]."')";
			
			$this->db->query($str);	
			
		}else{
			
			$send["status"] = false;
			$send["info"] = "Ya existe un banco con el Id: ".$info["bancoId"];
			return $send;
			
		}
		
		$send["status"] = true;
		$send["info"] = $info;
		
		return $send;
	}
	
	
}