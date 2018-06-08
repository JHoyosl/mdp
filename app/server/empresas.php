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
				RAZON_SOCIAL = '".$info["nombre"]."',
				SECTOR = '".$info["sector"]."',
				DIRECCION = '".$info["direccion"]."',
				TELEFONO = '".$info["telefono"]."',
				CIUDAD = '".$info["ciudad"]."',
				DEPARTAMENTO = '".$info["depto"]."'
				WHERE 
				NIT = '".$info["nit"]."'";

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
				empresas.CIUDAD AS CIUDAD_ID,
				maestro_ciudades.`NAME` AS CIUDAD_NOMBRE,
				empresas.DEPARTAMENTO AS DEPTO_ID,
				maestro_deptos.`NAME` AS DEPTO_NOMBRE,
				empresas.NIT,
				empresas.RAZON_SOCIAL,
				empresas.SECTOR,
				empresas.DIRECCION,
				empresas.TELEFONO
				FROM
				empresas
				LEFT OUTER JOIN maestro_ciudades
				ON empresas.CIUDAD = maestro_ciudades.CITY 
				LEFT OUTER JOIN maestro_deptos
				ON empresas.DEPARTAMENTO = maestro_deptos.DEPTO ".$whereString;
				
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
					(NIT, RAZON_SOCIAL,SECTOR,DIRECCION,DEPARTAMENTO,CIUDAD,TELEFONO)
					VALUES 
					('".$info["nit"]."','".$info["nombre"]."','".$info["sector"]."',
				     '".$info["direccion"]."','".$info["depto"]."','".$info["ciudad"]."','".$info["telefono"]."')";

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