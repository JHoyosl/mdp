<?php

namespace App\Services\CuadresOperativos;

use Exception;
use App\Traits\TableNamming;
use App\Models\ConvenioCuadre;
use App\Models\BalanceGeneralItem;
use Illuminate\Support\Facades\DB;
use App\Models\BalanceGeneralHeader;
use Illuminate\Support\Facades\Schema;
use App\Models\OperativoConvenioHeader;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use function PHPSTORM_META\type;

class BalanceSheetReconciliation
{
  use TableNamming;

  public function __construct()
  {
  }

  public function getBalanceSheetHeaders($companyId)
  {
    $balanceSheetHeadersTableName = $this->getBalanceSheetHeadersTableName($companyId);

    if (!Schema::hasTable($balanceSheetHeadersTableName)) {
      $this->createBalanceSheetHeadersTable($balanceSheetHeadersTableName);
    }

    $headerTable = new BalanceGeneralHeader($balanceSheetHeadersTableName);
    $headers = $headerTable->orderBy('fecha', 'desc')->get();

    return $headers;
  }

  public function getBalanceNaturaleza($companyId, $date, $overwrite = false)
  {

    $tableName = $this->getBalanceSheetHeadersTableName($companyId);
    $headerTable = new BalanceGeneralHeader($tableName);
    $balanceHeader = $headerTable->where('fecha', $date)->first();
    $headerId = $balanceHeader->id;

    $fileName = $this->balanceNaturalezaFileName($companyId, $headerId);

    if (Storage::disk('cuadres')->exists($fileName) && !$overwrite) {
      return json_decode(Storage::disk('cuadres')->get($fileName));
    }

    $masterOperatinalTableName = $this->getMasterOperational($companyId);
    $balanceItemsTableName = $this->getBalanceSheetItemsTableName($companyId);

    $responseBalance = array();

    $accounting = DB::table($masterOperatinalTableName)
      ->select(
        $masterOperatinalTableName . ".cuenta AS cuenta_maestro",
        $balanceItemsTableName . ".saldo_actual",
        $balanceItemsTableName . ".header_id",
        $balanceItemsTableName . ".cuenta AS cuenta_balance",
        $masterOperatinalTableName . ".area",
        $masterOperatinalTableName . ".descripcion",
        $masterOperatinalTableName . ".naturaleza",
        $masterOperatinalTableName . ".tipo_saldo",
      )
      ->where($balanceItemsTableName . '.header_id', $headerId)
      ->where($masterOperatinalTableName . '.area', 'CONTABILIDAD')
      ->where(function ($query) use ($masterOperatinalTableName, $balanceItemsTableName) {
        $query
          ->orWhere(function ($subQuery1) use ($masterOperatinalTableName, $balanceItemsTableName) {
            $subQuery1
              ->where($masterOperatinalTableName . '.naturaleza', 'DEBITO')
              ->where($balanceItemsTableName . '.saldo_actual', '<', 0)
              ->where($masterOperatinalTableName . '.tipo_saldo', '=', 'Con saldo');
          })
          ->orWhere(function ($subQuery2) use ($masterOperatinalTableName, $balanceItemsTableName) {
            $subQuery2
              ->where($masterOperatinalTableName . '.naturaleza', 'CREDITO')
              ->where($balanceItemsTableName . '.saldo_actual', '>', 0)
              ->where($masterOperatinalTableName . '.tipo_saldo', '=', 'Con saldo');
          })
          ->orWhere(function ($ceros) use ($masterOperatinalTableName, $balanceItemsTableName) {
            $ceros
              ->where($balanceItemsTableName . '.saldo_actual', '!=', 0)
              ->where($masterOperatinalTableName . '.tipo_saldo', '=', 'CEROS');
          });
      })
      ->join(
        $balanceItemsTableName,
        $balanceItemsTableName . '.cuenta',
        $masterOperatinalTableName . ".cuenta"
      )->get();

    $operational = DB::table($masterOperatinalTableName)
      ->select(
        $masterOperatinalTableName . ".cuenta AS cuenta_maestro",
        $balanceItemsTableName . ".saldo_actual",
        $balanceItemsTableName . ".header_id",
        $balanceItemsTableName . ".cuenta AS cuenta_balance",
        $masterOperatinalTableName . ".area",
        $masterOperatinalTableName . ".descripcion",
        $masterOperatinalTableName . ".naturaleza",
        $masterOperatinalTableName . ".tipo_saldo",
      )
      ->where($balanceItemsTableName . '.header_id', $headerId)
      ->where($masterOperatinalTableName . '.area', 'OPERACIONES')
      ->where(function ($query) use ($masterOperatinalTableName, $balanceItemsTableName) {
        $query
          ->orWhere(function ($subQuery1) use ($masterOperatinalTableName, $balanceItemsTableName) {
            $subQuery1
              ->where($masterOperatinalTableName . '.naturaleza', 'DEBITO')
              ->where($balanceItemsTableName . '.saldo_actual', '<', 0)
              ->where($masterOperatinalTableName . '.tipo_saldo', '=', 'Con saldo');
          })
          ->orWhere(function ($subQuery2) use ($masterOperatinalTableName, $balanceItemsTableName) {
            $subQuery2
              ->where($masterOperatinalTableName . '.naturaleza', 'CREDITO')
              ->where($balanceItemsTableName . '.saldo_actual', '>', 0)
              ->where($masterOperatinalTableName . '.tipo_saldo', '=', 'Con saldo');
          })
          ->orWhere(function ($ceros) use ($masterOperatinalTableName, $balanceItemsTableName) {
            $ceros
              ->where($balanceItemsTableName . '.saldo_actual', '!=', 0)
              ->where($masterOperatinalTableName . '.tipo_saldo', '=', 'CEROS');
          });
      })
      ->join(
        $balanceItemsTableName,
        $balanceItemsTableName . '.cuenta',
        $masterOperatinalTableName . ".cuenta"
      )->get();

    $responseBalance['CONTABILIDAD'] = $accounting;
    $responseBalance['OPERATIVO'] = $operational;

    $info = [
      'nautralezaContable' => $accounting,
      'nautralezaOperativa' => $operational,
    ];

    Storage::disk('cuadres')->put($fileName, json_encode($info));

    return $info;
  }
  public function getBalanceResult($companyId, $date)
  {

    $tableName = $this->getBalanceSheetHeadersTableName($companyId);
    $headerTable = new BalanceGeneralHeader($tableName);

    $balanceHeader = $headerTable->where('fecha', $date)->first();

    if ($balanceHeader) {
      $agreementsHeadersTableName =  $this->getAgreemenetsHeadersTableName($companyId);
      $agreementsHeadersTable = new OperativoConvenioHeader($agreementsHeadersTableName);
      $agreementsHeader = $agreementsHeadersTable->where('fecha', $date)->first();

      if ($agreementsHeader) {
        $agreementsItemsName = $this->getAgreemenetsItemsTableName($companyId);

        $items = DB::table($agreementsItemsName)
          ->select(DB::raw("SUM(" . $agreementsItemsName . ".salcuo) AS sum_salcuo,convenios_items_" . $companyId . ".header_id,convenios_items_" . $companyId . ".numcon"))
          ->where('header_id', $agreementsHeader->id)
          ->groupBy('numcon', 'header_id', 'numcon')
          ->get();
      }
    }

    $agreementsMasterTableName = $this->getMasterAgreements($companyId);
    $agreementsMaster = (new ConvenioCuadre($agreementsMasterTableName))->get();

    $balanceItemsTableName = $this->getBalanceSheetItemsTableName($companyId);
    $balanceItemsTable = new BalanceGeneralItem($balanceItemsTableName);

    $balanceItems = $balanceItemsTable
      ->select(
        $balanceItemsTableName . '.cuenta',
        $balanceItemsTableName . '.nombre_cuenta',
        $balanceItemsTableName . '.saldo_actual'
      )
      ->join(
        $agreementsMasterTableName,
        $agreementsMasterTableName . '.cuenta',
        $balanceItemsTableName . '.cuenta'
      )
      ->where($balanceItemsTableName . '.header_id', $balanceHeader->id)
      ->get();



    $info = [
      'balance' => [
        'header' => $balanceHeader,
        'items' => $balanceItems,
      ],
      'convenios' => ['header' => $balanceHeader, 'items' => $items],
      'cuentasArray' => $agreementsMaster,
    ];
    return $info;
  }

  public function uploadBlance($date, $file, $companyId, $user)
  {
    $balanceSheetHeaderTableName = $this->getBalanceSheetHeadersTableName($companyId);
    $balanceHeaderTable = new BalanceGeneralHeader($balanceSheetHeaderTableName);
    $header = $balanceHeaderTable->where('fecha', $date)->first();

    DB::beginTransaction();
    if (!$header) {
      $insertValues = [
        'fecha' => $date,
        'file_name' => '',
        'file_path' => '',
        'status' => BalanceGeneralHeader::OPEN,
        'user' => $user->id,
      ];
      $balanceHeaderTable->insert($insertValues);

      $header = $balanceHeaderTable->where('fecha', $date)->first();
    }

    $balance = $this->balanceToInsert($file, $header->id);

    $balanceItemsTableName = $this->getBalanceSheetItemsTableName($companyId);
    try {
      foreach (array_chunk($balance, 500) as $t) {
        DB::table($balanceItemsTableName)->insert($t);
      }
    } catch (Exception $e) {
      DB::rollBack();
      throw $e;
    }

    DB::commit();
    return $header;
  }

  public function deleteBalance($companyId, $id)
  {
    $balanceSheetHeaderTableName = $this->getBalanceSheetHeadersTableName($companyId);
    $balanceHeader = (new BalanceGeneralHeader($balanceSheetHeaderTableName))->where('id', $id)->first();

    if (!$balanceHeader) {
      throw new Exception('Model not found');
    }

    $headerId = $balanceHeader->id;

    $fileName = $this->balanceNaturalezaFileName($companyId, $headerId);

    if (Storage::disk('cuadres')->exists($fileName)) {
      Storage::disk('cuadres')->delete($fileName);
    }

    $balanceItemsTableName = $this->getBalanceSheetItemsTableName($companyId);
    (new BalanceGeneralItem($balanceItemsTableName))->where('header_id', $headerId)->delete();

    (new BalanceGeneralHeader($balanceSheetHeaderTableName))->where('id', $id)->delete();

    return 'success';
  }

  public function downloadBalanceNaturaleza($companyId, $date)
  {

    $headerTableName = $this->getBalanceSheetHeadersTableName($companyId);
    $header = (new BalanceGeneralHeader($headerTableName))->where('fecha', $date)->first();
    $fileName = $this->balanceNaturalezaFileName($companyId, $header->id);
    $jsonFile = Storage::disk('cuadres')->get($fileName);

    $data = json_decode($jsonFile, true);
    // return $data;
    $fileName = $this->balanceNaturalezaFileName($companyId, $header->id, 'xlsx');

    $filePath = Storage::disk('cuadres')->path($fileName);

    $this->balanceNaturalezaToXlsx($data, $filePath);

    $fileName = 'balance_naturaleza_' . $date . '.xlsx';
    $headers = array(
      'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'Content-Disposition' => "attachment; filename='" . $fileName . "'"
    );
    return [
      'fileName' => $fileName,
      'filePath' => $filePath,
      'headers' => $headers
    ];
  }
  //HELPERS 
  private function balanceNaturalezaToXlsx($data, $fileName)
  {
    $spreadsheet = new Spreadsheet();
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Nautraleza Contable');
    $sheet1->setCellValue('A1', 'Cuenta');
    $sheet1->setCellValue('B1', 'Descripción');
    $sheet1->setCellValue('C1', 'Naturaleza');
    $sheet1->setCellValue('D1', 'Tipo');
    $sheet1->setCellValue('E1', 'Saldo');


    for ($i = 1; $i < count($data['nautralezaContable']); $i++) {
      $row = $i + 1;
      $sheet1->setCellValue('A' . $row, $data['nautralezaContable'][$i]['cuenta_maestro']);
      $sheet1->setCellValue('B' . $row, $data['nautralezaContable'][$i]['descripcion']);
      $sheet1->setCellValue('C' . $row, $data['nautralezaContable'][$i]['naturaleza']);
      $sheet1->setCellValue('D' . $row, $data['nautralezaContable'][$i]['tipo_saldo']);
      $sheet1->setCellValue('E' . $row, $data['nautralezaContable'][$i]['saldo_actual']);
    }

    $spreadsheet->createSheet();
    $sheet2 = $spreadsheet->getSheet(1);
    $sheet2->setTitle('Nautraleza Operativa');
    $sheet2->setCellValue('A1', 'Cuenta');
    $sheet2->setCellValue('B1', 'Descripción');
    $sheet2->setCellValue('C1', 'Naturaleza');
    $sheet2->setCellValue('D1', 'Tipo');
    $sheet2->setCellValue('E1', 'Saldo');

    for ($i = 1; $i < count($data['nautralezaOperativa']); $i++) {
      $row = $i + 1;
      $sheet2->setCellValue('A' . $row, $data['nautralezaOperativa'][$i]['cuenta_maestro']);
      $sheet2->setCellValue('B' . $row, $data['nautralezaOperativa'][$i]['descripcion']);
      $sheet2->setCellValue('C' . $row, $data['nautralezaOperativa'][$i]['naturaleza']);
      $sheet2->setCellValue('D' . $row, $data['nautralezaOperativa'][$i]['tipo_saldo']);
      $sheet2->setCellValue('E' . $row, $data['nautralezaOperativa'][$i]['saldo_actual']);
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save($fileName);
  }

  private function balanceNaturalezaFileName($companyId, $headerId, $type = 'json')
  {
    if ($type == 'json') {
      return "{$companyId}/balanceNaturaleza/{$headerId}.json";
    }
    if ($type == 'xlsx') {
      return "{$companyId}/balanceNaturaleza/{$headerId}.xlsx";
    }
  }

  private function balanceToInsert($file, $headerId)
  {
    $startRow = 6;
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load($file);

    $spreadsheet->setActiveSheetIndex(0);
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestDataRow();
    $rows = [];

    foreach ($worksheet->getRowIterator($startRow) as $keyRow => $row) {

      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
      $cells = [];
      foreach ($cellIterator as $keyCell => $cell) {
        $cells[] = $worksheet->getCell($keyCell . $keyRow)->getValue();
      }
      $rows[] = [
        'header_id' => $headerId,
        'registro' => $cells[0],
        'agencia' => $cells[1],
        'cuenta' => $cells[2],
        'nombre_cuenta' => $cells[3],
        'saldo_anterior' => $this->fixedCurrency(',', $cells[4]),
        'debito' => $this->fixedCurrency(',', $cells[5]),
        'credito' => $this->fixedCurrency(',', $cells[6]),
        'saldo_actual' => $this->fixedCurrency(',', $cells[7]),
      ];
    }

    return $rows;
  }

  private function fixedCurrency($separator, $value)
  {
    $value = str_replace('$', '', $value);
    if ($separator == ',') {
      $value = str_replace('.', '', $value);
      $value = str_replace(',', '.', $value);
    }
    if ($separator == '.') {
      $value = str_replace(',', '', $value);
    }
    return floatval($value);
  }


  // TABLES CEATION
  public function createBalanceSheetHeadersTable($tableName)
  {
    Schema::create($tableName, function ($table) {
      $table->bigIncrements('id');
      $table->dateTime('fecha');
      $table->string('file_name');
      $table->string('file_path');
      $table->string('status');
      $table->string('user');

      $table->softDeletes();
      $table->timestamps();
    });
  }
}
