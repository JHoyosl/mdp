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
	
	function desVincularFormato($info){

		$str = "DELETE FROM formato_empresa 
				WHERE 
				NIT = '".$_SESSION["empresa"]."' AND 
				FORMATO_ID = '".$info["id"]."'";

		try{

			$query = $this->db->query($str);

		}catch(\Exception $e){

			$resp["message"] = $e->getMessage();
			$resp["status"] = false;

			return $resp;
		}
		
		
		$resp["message"] = $str;
		$resp["status"] = true;

		return $resp;
	}

	function vincularFormato($info){


		$str = "INSERT INTO formato_empresa 
				(NIT, FORMATO_ID)
				VALUES 
				('".$_SESSION["empresa"]."','".$info["id"]."')";
		
		try{

			$query = $this->db->query($str);

		}catch(\Exception $e){

			$resp["message"] = $e->getMessage();
			$resp["status"] = false;

			return $resp;
		}
		
		
		$resp["message"] = "";
		$resp["status"] = true;

		return $resp;


	}

	function getFormatoVinculado($info){

		$str = "SELECT
				formato_mapeo.ID,
				formato_mapeo.COD_FORMATO,
				formato_mapeo.DESCRIPCION,
				formato_mapeo.FRECUENCIA,
				formato_mapeo.BANCO, 
				IF(bancos.NOMBRE IS NULL, 'CONTABLE', bancos.NOMBRE) AS NOMBRE,
				IF(bancos.BANCO_ID IS NULL, '".$_SESSION["empresa"]."', bancos.BANCO_ID) AS BANCO_ID
				FROM
				formato_empresa
				RIGHT JOIN formato_mapeo
				ON formato_empresa.FORMATO_ID = formato_mapeo.ID 
				JOIN bancos
				ON formato_mapeo.BANCO = bancos.BANCO_ID
				WHERE 
				formato_mapeo.FRECUENCIA = '".$info["frecuencia"]."' AND 
				formato_empresa.FORMATO_ID IS NOT NULL
				ORDER BY 
				NOMBRE, COD_FORMATO";

		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;

	}
	
	function getFormatoContableNoVinculado($info){


		$str = "SELECT
				formato_mapeo.ID,
				formato_mapeo.BANCO,
				formato_mapeo.COD_FORMATO,
				formato_mapeo.DESCRIPCION,
				formato_mapeo.FUENTE,
				formato_mapeo.FRECUENCIA,
				'CONTABLE' AS NOMBRE
				FROM
				formato_mapeo
				LEFT OUTER JOIN formato_empresa
				ON formato_mapeo.ID = formato_empresa.FORMATO_ID
				WHERE
				formato_mapeo.FUENTE = 1 AND 
				formato_mapeo.FRECUENCIA = '".$info["frecuencia"]."' AND 
				formato_mapeo.BANCO = '".$_SESSION["empresa"]."' AND 
				formato_empresa.NIT IS NULL";

		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;

	}

	function getFormatoContableVinculado($info){


		$str = "SELECT
				formato_mapeo.ID,
				formato_mapeo.BANCO,
				formato_mapeo.COD_FORMATO,
				formato_mapeo.DESCRIPCION,
				formato_mapeo.FUENTE,
				formato_mapeo.FRECUENCIA,
				'CONTABLE' AS NOMBRE
				FROM
				formato_mapeo
				LEFT OUTER JOIN formato_empresa
				ON formato_mapeo.ID = formato_empresa.FORMATO_ID
				WHERE
				formato_mapeo.FUENTE = 1 AND 
				formato_mapeo.FRECUENCIA = '".$info["frecuencia"]."' AND 
				formato_mapeo.BANCO = '".$_SESSION["empresa"]."' AND 
				formato_empresa.NIT IS NOT NULL";

		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;

	}

	function getFormatoExternoNoVinculado($info){


		$str = "SELECT
				formato_mapeo.ID,
				formato_mapeo.COD_FORMATO,
				formato_mapeo.DESCRIPCION,
				formato_mapeo.FRECUENCIA,
				formato_mapeo.BANCO, 
				formato_mapeo.FUENTE, 
				IF(bancos.NOMBRE IS NULL, 'CONTABLE', bancos.NOMBRE) AS NOMBRE,
				IF(bancos.BANCO_ID IS NULL, '".$_SESSION["empresa"]."', bancos.BANCO_ID) AS BANCO_ID
				FROM
				formato_empresa
				RIGHT JOIN formato_mapeo
				ON formato_empresa.FORMATO_ID = formato_mapeo.ID 
				JOIN bancos
				ON formato_mapeo.BANCO = bancos.BANCO_ID
				WHERE 
				formato_mapeo.FRECUENCIA = '".$info["frecuencia"]."' AND 
				formato_empresa.FORMATO_ID IS NULL
				ORDER BY 
				NOMBRE, COD_FORMATO";

		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;

	}

	
	function getFormatMap($info){

		$str = "SELECT 
				MAP,
				ESTRUCTURA
				FROM 
				formato_mapeo 
				WHERE 
				formato_mapeo.ID = ".$info["ID"];

		$formato = $this->db->query($str);

		$str = "SELECT
				maestro_formato_recaudo.ID,
				maestro_formato_recaudo.DESCRIPCION,
				maestro_formato_recaudo.TYPE
				FROM
				maestro_formato_recaudo";

		$maestro = $this->db->query($str);

		$resp["message"] = array($formato[0], $maestro);
		$resp["status"] = true;

		return $resp;

	}

	function getFormatoListTable($info){

		$whereArray = [];
		$whereString = "";
		
		$whereArray[] = "((formato_mapeo.BANCO = '".$_SESSION["empresa"]."' AND formato_mapeo.FUENTE = '1') OR formato_mapeo.FUENTE = '0')";
		
		if($info["banco"] != ""){

			$whereArray[] = "bancos.BANCO_ID LIKE UPPER('%".$info["banco"]."%')";

		}
		if($info["codigo"] != ""){

			$whereArray[] = "formato_mapeo.COD_FORMATO LIKE UPPER('%".$info["codigo"]."%')";

		}
		if($info["frecuencia"] != ""){

			$whereArray[] = "formato_mapeo.FRECUENCIA  = '".$info["frecuencia"]."'";

		}
		

		if(count($whereArray) > 0){

			$whereString = "WHERE ".implode(" AND ", $whereArray);

		}

		$str = "SELECT DISTINCT
				formato_mapeo.ID,
				formato_mapeo.COD_FORMATO,
				formato_mapeo.DESCRIPCION,
				formato_mapeo.FRECUENCIA,
				formato_mapeo.BANCO, 
				IF(bancos.NOMBRE IS NULL, 'CONTABLE', bancos.NOMBRE) AS NOMBRE,
				IF(bancos.BANCO_ID IS NULL, '".$_SESSION["empresa"]."', bancos.BANCO_ID) AS BANCO_ID
				FROM
				formato_mapeo
				LEFT OUTER JOIN bancos
				ON formato_mapeo.BANCO = bancos.BANCO_ID ".$whereString;
				
		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;

	}

	function uniqueCodFormato($codFormato){

		$str = "SELECT
				formato_mapeo.COD_FORMATO
				FROM
				formato_mapeo
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

	function validateMap($map,$fuente){

		$errorArray = array();
		
		if($fuente == 1){
			
			$str = "SELECT
					maestro_formato_contable.ID,
					maestro_formato_contable.DESCRIPCION,
					maestro_formato_contable.TYPE
					FROM
					maestro_formato_contable
					WHERE 
					TYPE = '1'";
			
		}else{
			
			$str = "SELECT
					maestro_formato_recaudo.ID,
					maestro_formato_recaudo.DESCRIPCION,
					maestro_formato_recaudo.TYPE
					FROM
					maestro_formato_recaudo
					WHERE 
					TYPE = '1'";
			
		}
		
		

		$query = $this->db->query($str);


		for($i = 0; $i<count($query); $i++){

			if(!in_array($query[$i]["ID"],$map)){

				$errorArray[] = $query[$i]["DESCRIPCION"];

			}

		}
		return $errorArray;
 
	}

	function guardarFormatoRecaudo($info){

		$chkMandatory = $this->validateMap($info["map"],$info["fuente"]);

		if(count($chkMandatory) > 0){

			$resp["message"] = $chkMandatory;
			$resp["status"] = false;

			return $resp;
		}
		
		if($info["banco"] == ""){
			
			$info["banco"] = $_SESSION["empresa"];
			
		}
		
		if($this->uniqueCodFormato($info["codFormato"])){

			$jsonStruct = addslashes(json_encode($info["estructura"]));
			
			$str = "INSERT INTO formato_mapeo
					(BANCO, COD_FORMATO, DESCRIPCION, MAP, ESTRUCTURA, FRECUENCIA, FUENTE, ENCABEZADOS)
					VALUES
					(
					 '".$info["banco"]."','".$info["codFormato"]."','".$info["descripcion"]."',
					 '".json_encode($info["map"])."','".$jsonStruct."','".$info["frecuencia"]."','".$info["fuente"]."','".$info["encabezado"]."'
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
			case "CSV":
					
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
	
	function getFormatoListContable(){
		
		$str = "SELECT
				maestro_formato_contable.ID,
				IF(maestro_formato_contable.TYPE = 1,concat(maestro_formato_contable.DESCRIPCION,'*'),maestro_formato_contable.DESCRIPCION) as DESCRIPCION,
				maestro_formato_contable.DESCRIPCION as ORDERED,
				maestro_formato_contable.TYPE
				FROM
				maestro_formato_contable 
				ORDER BY ORDERED ASC";
				
		$query = $this->db->query($str);
		
		$resp["message"] = $query;
		$resp["status"] = true;

		return $resp;

	}
	
	function getFormatoList(){

		$str = "SELECT
				maestro_formato_recaudo.ID,
				IF(maestro_formato_recaudo.TYPE = 1,concat(maestro_formato_recaudo.DESCRIPCION,'*'),maestro_formato_recaudo.DESCRIPCION) as DESCRIPCION,
				maestro_formato_recaudo.DESCRIPCION as ORDERED,
				maestro_formato_recaudo.TYPE
				FROM
				maestro_formato_recaudo 
				ORDER BY ORDERED ASC";
				
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

		if($info["codComp"] != ""){

			$whereArray[] = "bancos.COD_COMP LIKE '%".$info["codComp"]."%'";

		}
		if($info["nombre"] != ""){

			$whereArray[] = "bancos.NOMBRE LIKE UPPER('%".$info["nombre"]."%')";

		}
		if($info["nit"] != ""){

			$whereArray[] = "bancos.BANCO_ID LIKE '%".$info["nit"]."%'";

		}
		

		if(count($whereArray) > 0){

			$whereString = "WHERE ".implode(" AND ", $whereArray);

		}

		$str = "SELECT
				bancos.COD_COMP,
				bancos.BANCO_ID,
				bancos.NOMBRE,
				bancos.MONEDA,
				bancos.PORTAL
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
				COD_COMP = '".$info["codComp"]."',
				NOMBRE = '".$info["nombre"]."',
				MONEDA = '".$info["moneda"]."',
				PORTAL = '".$info["portal"]."'
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
					(BANCO_ID, NOMBRE, MONEDA, TELEFONO, CONTACTO, EMAIL, PORTAL, COMISION, CANALES, COD_COMP)
					VALUES
					('".$info["bancoId"]."','".$info["nombre"]."','".$info["moneda"]."','".$info["telefono"]."','".$info["contacto"]."',
					 '".$info["email"]."','".$info["portal"]."','".$info["comision"]."','".$info["canal"]."','".$info["codComp"]."')";

			
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