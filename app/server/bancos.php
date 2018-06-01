<?php

namespace Gsys;

class bancos{

	//Variable global
	private $db;
	private $api;
	private $separator = array("puntoycoma"=>";","coma"=>",","tab"=>"\t","space"=>" ");
	//Se declara la variable de respeusta
	private $send = array("");
	
	function __construct(){

		$this->db = new db();
		$this->api = new api();
		$this->mailer = new mailer();

	}
	
	function uniqueCodFormato($codFormato){

		$str = "SELECT
				mdp.formato_mapeo.COD_FORMATO
				FROM
				mdp.formato_mapeo
				WHERE 
				formato_mapeo.COD_FORMATO = '".$codFormato."'";

		$query = $this->db->query($str);
		
		if(count($query) > 0){

			return false;
			
		}else{

			return true;

		}
		
		return $resp;

	}

	function guardarFormatoRecaudo($info){


		if($this->uniqueCodFormato($info["codFormato"])){

			$jsonStruct = addslashes(json_encode($info["estructura"]));

			$str = "INSERT INTO formato_mapeo
					(BANCO, COD_FORMATO, DESCRIPCION, MAP, ESTRUCTURA)
					VALUES
					(
					 '".$info["banco"]."','".$info["codFormato"]."','".$info["descripcion"]."',
					 '".json_encode($info["map"])."','".$jsonStruct."'
					)";
			
			$query = $this->db->query($str);
			$resp["message"] = $str;
			$resp["status"] = true;

		}else{

			$resp["message"] = "Ya existe un COD FORMATO: ".$info["codFormato"];
			$resp["status"] = false;

		}



		return $resp;

	}

	function segmentarFormato($info){

		$path = $_FILES[0]['name'];
		$ext = pathinfo($path, PATHINFO_EXTENSION);

		switch ($ext) {
			case 'xlsx':
				
				require('libs/PHPExcel/Classes/PHPExcel.php');

				$Reader = \PHPExcel_IOFactory::createReaderForFile($_FILES[0]['tmp_name']);

				//Read the file
				$objXLS = $Reader->load($_FILES[0]['tmp_name']);

				$objXLS->setActiveSheetIndex(0);

				$worksheet = $objXLS->getActiveSheet();
				
				$highestColumn = $objXLS->setActiveSheetIndex(0)->getHighestColumn();

				$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

				

				for($col = 0; $col < $highestColumnIndex; $col++){

					$titleCell = $worksheet->getCellByColumnAndRow($col, 1)->getValue();
					$valueCell = $worksheet->getCellByColumnAndRow($col, 2);
					$typeCell = $worksheet->getCellByColumnAndRow($col, 2)->getDataType();

					if(\PHPExcel_Shared_Date::isDateTime($valueCell)){

						$valueCell = date('Y-m-d',\PHPExcel_Shared_Date::ExcelToPHP($valueCell->getValue()));
						$typeCell = "d";


					}else{

						$valueCell = $worksheet->getCellByColumnAndRow($col, 2)->getValue();

					}

					$cell[] = ["title"=>$titleCell,"value"=>$valueCell,"type"=>$typeCell];


				}
				break;
			
			case "csv":
					
					//CONVERSIÓN DE ARCHIVO A UTF-8
					$csvFile = mb_convert_encoding(file_get_contents($_FILES[0]['tmp_name']), "UTF-8");
					
					$cell = $this->fileExplode($csvFile,$this->separator[$info["separator"]]);
				
				break;
			default:
					$resp["message"] = "Formato no Permitido: .".$ext;
					$resp["status"] = false; 

					return $resp;
				break;
		}
		
		$_SESSION["bancos"]["uploadFIle"] = $_FILES[0];

		$resp["message"] = $cell;
		$resp["status"] = true;

		return $resp;
		
	}
	

	function fileExplode($file, $delimiter){

		$rows = explode("\n",$file);
		
		$colTitles = explode($delimiter,$rows[0]);
		$colValues = explode($delimiter,$rows[1]);
		
		
		if(!(count($colTitles) > 1)){
			
			return false;
		}
		
		for($i = 0; $i < count($colTitles); $i++){
			
			$cell[] = ["title"=>$colTitles[$i],"value"=>$colValues[$i],"type"=>"csv"];	
			
		}
		
		return $cell;

	}

	function getFormatoList(){

		$str = "SELECT
				maestro_formato_recaudo.ID,
				maestro_formato_recaudo.DESCRIPCION,
				maestro_formato_recaudo.TYPE
				FROM
				maestro_formato_recaudo 
				ORDER BY DESCRIPCION ASC";
				
		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;
	}

	function getBancoListSelect(){
		
		$str = "SELECT
				bancos.BANCO_ID AS ID,
				bancos.NOMBRE AS DESCRIPCION
				FROM
				bancos 
				ORDER BY DESCRIPCION ASC";
				
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