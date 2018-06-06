<?php

namespace Gsys;

class maestros{

	private $db;
	function __construct(){

		$this->db = new db();


	}
	
	
	function getCentroList($info){
		
		if($_SESSION["tipo"] == 1){
			
			$str = "SELECT
				empresas.NIT AS ID,
				empresas.RAZON_SOCIAL AS DESCRIPCION
				FROM
				empresas";
			
		}else{
			
			$str = "SELECT
				empresas.NIT AS ID,
				empresas.RAZON_SOCIAL AS DESCRIPCION
				FROM
				empresas
				WHERE 
				NIT = '".$_SESSION["empres"]."'";
			
		}
		
		
		
		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
	}
	
	function getAreaUsuario($info){
		
		$str = "SELECT
				maestro_area_empresa.ID,
				maestro_area_empresa.DESCRIPCION
				FROM
				maestro_area_empresa";
				
		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
		
	}

	function maestroGenero($info){
		
		$str = "SELECT
				maestro_genero.ID,
				maestro_genero.DESCRIPCION
				FROM
				maestro_genero";
		
		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
	}
	
	function maestroTipoDocumento($info){
		
		$str = "SELECT
				maestro_tipo_doc.ID,
				maestro_tipo_doc.DESCRIPCION
				FROM
				maestro_tipo_doc";
		
		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
	}
	
	function getDeptos($info){
		
		$str = "SELECT
				maestro_deptos.DEPTO AS ID,
				maestro_deptos.`NAME` AS DESCRIPCION
				FROM
				maestro_deptos
				ORDER BY maestro_deptos.NAME ASC";
				
		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
		
	}

	function getCiudades($info){

		$str = "SELECT
				maestro_ciudades.CITY AS ID,
				maestro_ciudades.DEPTO,
				maestro_ciudades.`NAME` AS DESCRIPCION
				FROM
				maestro_ciudades
				WHERE
				maestro_ciudades.DEPTO = '".$info["deptoCode"]."'
				ORDER BY maestro_ciudades.`NAME` ASC";
		

		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
		

	}
}