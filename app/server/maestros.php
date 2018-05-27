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
				master_deptos.DEPTO, 
					master_deptos.`NAME`
				FROM master_deptos";

		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
		

	}

	function getCities($info){

		$str = "SELECT master_cities.`NAME`, 
					master_cities.CITY
				FROM master_cities
				WHERE master_cities.DEPTO = '".$info["deptoCode"]."'
				ORDER BY master_cities.`NAME` ASC";
		

		$resp = $this->db->query($str);

		$send["status"] = true;
		$send["info"] = $resp;

		return $send;
		

	}
}