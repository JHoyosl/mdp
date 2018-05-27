<?php

namespace Gsys;

class equipos{

	//Variable globalr para usar la bd
	private $db;
	
	//Se declara la variable de respeusta
	private $send = array("");
	function __construct(){

		$this->db = new db();


	}

	function getEquipos($info){

		$filters = array();
		
		$filters[] = "CENTER = '".$_SESSION['center']."'";
		
		if($info["CONSECUTIVE"] != ""){
			
			$filters[] = "CONSECUTIVE LIKE '%".$info["CONSECUTIVE"]."%'";
		}
		if($info["COD"] != ""){
			
			$filters[] = "COD LIKE '%".$info["COD"]."%'";
			
		}
		if($info["DESCRIPTION"] != ""){
			
			$filters[] = "UPPER(DESCRIPTION) LIKE UPPER('%".$info["DESCRIPTION"]."%')";
			
		}
		if($info["COD_PRESTADOR"] != ""){
			
			$filters[] = "UPPER(COD_PRESTADOR) LIKE UPPER('%".$info["COD_PRESTADOR"]."%')";
			
		}
		
		$where = " WHERE ".implode(" AND ",$filters);
		
		 $str = "SELECT
				count(*) as QTY
				FROM
				equipment
				LEFT OUTER JOIN centers ON equipment.CENTER = centers.ID
				".$where."
				ORDER BY equipment.CONSECUTIVE";
		
		
		$qty = $this->db->query($str);
		 
		$str = "SELECT
				equipment.CONSECUTIVE,
				equipment.DESCRIPTION, 
				equipment.COD,
				equipment.PLACE,
				equipment.COD_PRESTADOR,
				equipment.SERIAL,
				'' AS CALIBRATION,
				'' AS MAINTENANCE,
				'' AS ACTIONS
				FROM
				equipment
				LEFT OUTER JOIN centers ON equipment.CENTER = centers.ID
				".$where."
				ORDER BY equipment.CONSECUTIVE
				LIMIT ".$info['limits']['lower'].",".$info['limits']['upper']."";
		
		$query = $this->db->query($str);
		
		
		for($i = 0; $i < count($query); $i++){
			
			
			$str = "SELECT
					maintenance.DATE,
					maintenance.CONSECUTIVE,
					maintenance.`NAME`,
					maintenance.CENTER,
					maintenance.PATH
					FROM
					maintenance
					WHERE
					maintenance.CONSECUTIVE = '".$query[$i]["CONSECUTIVE"]."' AND
					maintenance.TYPE = 'M' AND
					maintenance.CENTER = '".$_SESSION["center"]."'
					ORDER BY
					maintenance.DATE DESC
					LIMIT 3";
			
			$maintenance = $this->db->query($str);
			
			$query[$i]["MAINTENANCE"] = $maintenance;
			
			$str = "SELECT
					maintenance.DATE,
					maintenance.CONSECUTIVE,
					maintenance.`NAME`,
					maintenance.CENTER,
					maintenance.PATH
					FROM
					maintenance
					WHERE
					maintenance.CONSECUTIVE = '".$query[$i]["CONSECUTIVE"]."' AND
					maintenance.TYPE = 'C' AND
					maintenance.CENTER = '".$_SESSION["center"]."'
					ORDER BY
					maintenance.DATE DESC
					LIMIT 1"; 

			$calibration = $this->db->query($str);
			
			$query[$i]["MAINTENANCE"] = $maintenance;
			$query[$i]["CALIBRATION"] = $calibration;
			
		}

		$resp["message"] = array("info"=>$query,"qty"=>$qty[0]["QTY"]);
		$resp["status"] = true;
  
		return $resp;
	}


}