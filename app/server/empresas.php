<?php

namespace Gsys;

class empresas{

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

	function editarEmpresa($info){

		$str = "UPDATE empresas
				SET 
				RAZON_SOCIAL = '".$info["nombre"]."'";

		$this->db->query($str);

		$send["status"] = true;
		$send["info"] = $info;
		
		return $send;

	}

	function getEmpresasList($info){

		$whereArray = [];
		$whereString = "";

		if($info["nit"] != ""){

			$whereArray[] = "empresas.NIT LIKE UPPER('%".$info["nit"]."%')";

		}
		if($info["nombre"] != ""){

			$whereArray[] = "empresas.RAZON_SOCIAL LIKE UPPER('%".$info["nombre"]."%')";

		}
		

		if(count($whereArray) > 0){

			$whereString = "WHERE ".implode(" AND ", $whereArray);

		}

		$str = "SELECT
				empresas.NIT,
				empresas.RAZON_SOCIAL
				FROM
				empresas ".$whereString;
				
		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;
	}

	function crearEmpresa($info){

		$str = "SELECT
				empresas.NIT,
				empresas.RAZON_SOCIAL
				FROM
				empresas
				WHERE 
				NIT = '".$info["nit"]."'";

		$query = $this->db->query($str);

		if(count($query) == 0){

			$str = "INSERT INTO empresas 
					(NIT, RAZON_SOCIAL)
					VALUES 
					('".$info["nit"]."','".$info["nombre"]."')";

			$query = $this->db->query($str);

			$resp["message"] = $query;
			$resp["status"] = true;

		}else{

			$resp["message"] = "Ya existe una empresa con el NIT: ".$info["nit"];
			$resp["status"] = false;

		}

		

		return $resp;

	}

}