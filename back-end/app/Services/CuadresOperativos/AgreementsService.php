<?php

namespace App\Services\CuadresOperativos;

use Exception;
use App\Traits\TableNamming;
use App\Models\AgreementsHeader;
use App\Models\AgreementsMaster;
use Illuminate\Support\Facades\DB;
use App\Models\BalanceGeneralHeader;
use App\Models\BalanceGeneralItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AgreementsService
{
  use TableNamming;

  private BalanceSheetReconciliation $balanceSheetReconciliation;

  public function __construct(
    BalanceSheetReconciliation $balanceSheetReconciliation
  ) {
    $this->balanceSheetReconciliation = $balanceSheetReconciliation;
  }

  function index($companyId)
  {
    $balanceSheetHeaderTableName = $this->getBalanceSheetHeadersTableName($companyId);
    $agreementsHeader = $this->getAgreemenetsHeadersTableName($companyId);

    $balanceSheetHeaderTable = new BalanceGeneralHeader($balanceSheetHeaderTableName);

    $info = $balanceSheetHeaderTable
      ->select(
        $balanceSheetHeaderTableName . '.id AS balanceId',
        $agreementsHeader . '.id AS agreementId',
        $balanceSheetHeaderTableName . '.fecha AS balanceDate',
        $agreementsHeader . '.date AS agreementDate',
        $agreementsHeader . '.status',
        $agreementsHeader . '.user',
      )
      ->leftjoin($agreementsHeader, $agreementsHeader . '.date', $balanceSheetHeaderTableName . '.fecha')
      ->get();

    return $info;
  }

  function deleteAgreement($companyId, $id)
  {
    $agreementsHeaderTableName = $this->getAgreemenetsHeadersTableName($companyId);
    $agreementsHeadersTable = (new AgreementsHeader($agreementsHeaderTableName))
      ->where('id', $id)->first();

    if (!$agreementsHeadersTable) {
      throw new Exception('Data not found', 400);
    }

    $agreementsItemsTable = $this->getAgreemenetsItemsTableName($companyId);
    DB::beginTransaction();
    DB::table($agreementsItemsTable)->where('header_id', $id)->delete();

    $agreementsHeadersTable->delete();
    $fileName = $this->agreementsFileName($companyId, $id);
    if (Storage::disk('cuadres')->exists($fileName)) {
      Storage::disk('cuadres')->delete($fileName);
    }
    DB::commit();
    return 'success';
  }

  function getAgreementsResult($companyId, $date, $overwrite = false)
  {
    $tableName = $this->getBalanceSheetHeadersTableName($companyId);
    $headerTable = new BalanceGeneralHeader($tableName);

    $balanceHeader = $headerTable->where('fecha', $date)->first();

    if (!$balanceHeader) {
      throw new Exception('No existe balance para la fecha: ' . $date, 400);
    }

    $agreementsHeadersTableName =  $this->getAgreemenetsHeadersTableName($companyId);
    $agreementsHeadersTable = new AgreementsHeader($agreementsHeadersTableName);
    $agreementsHeader = $agreementsHeadersTable->where('date', $date)->first();

    if (!$agreementsHeader) {
      throw new Exception('No existe convenios para la fecha: ' . $date, 400);
    }

    $fileName = $this->agreementsFileName($companyId, $agreementsHeader->id);

    if (Storage::disk('cuadres')->exists($fileName) && !$overwrite) {
      return json_decode(Storage::disk('cuadres')->get($fileName));
    }

    $agreementsItemsName = $this->getAgreemenetsItemsTableName($companyId);
    $agreementsMaster = $this->getAgreementsMasterTableName($companyId);
    $balanceItems = $this->getBalanceSheetItemsTableName($companyId);

    $balanceItemsTable = new BalanceGeneralItem($balanceItems);

    $info = $balanceItemsTable
      ->select(
        $agreementsMaster . ".account",
        $agreementsMaster . ".line",
        $agreementsMaster . ".name",
        $balanceItems . '.saldo_actual as saldoActual',
        DB::raw('SUM(' . $agreementsItemsName . '.salcuo) sumSalcuo'),
        DB::raw('SUM(' . $agreementsItemsName . '.salcuo) - ' . $balanceItems . '.saldo_actual AS difference')
      )
      ->join($agreementsMaster, $agreementsMaster . ".account", $balanceItems . ".cuenta")
      ->join($agreementsItemsName, $agreementsItemsName . ".numcon", $agreementsMaster . ".line")
      ->where($balanceItems . '.header_id', $balanceHeader->id)
      ->where($agreementsItemsName . '.header_id', $agreementsHeader->id)
      ->groupBy(
        $agreementsMaster . ".account",
        $agreementsMaster . ".line",
        $agreementsMaster . ".name",
        $balanceItems . ".saldo_actual"
      )
      ->get();

    Storage::disk('cuadres')->put($fileName, json_encode($info));

    return $info;
  }

  public function uploadAgreement($companyId, $user, $file, $date)
  {
    $agreementsHeaderTableName = $this->getAgreemenetsHeadersTableName($companyId);
    $agreementsTable = new AgreementsHeader($agreementsHeaderTableName);
    $header = $agreementsTable->where('date', $date)->first();

    if (!$header) {

      (new AgreementsHeader($agreementsHeaderTableName))->insert([
        'date' => $date,
        'status' => AgreementsHeader::OPEN,
        'user' => $user->id,
        'created_at' => now()
      ]);

      $header = $agreementsTable->where('date', $date)->first();
    }

    $headerId = $header->id;

    $agreementsRows = $this->agreementsFileToInsert($file, $headerId);
    $agreementsItemsTableName = $this->getAgreemenetsItemsTableName($companyId);

    try {
      foreach (array_chunk($agreementsRows, 500) as $t) {
        DB::table($agreementsItemsTableName)->insert($t);
      }
    } catch (Exception $e) {
      DB::rollBack();
      throw $e;
    }

    return $header;
  }

  //MASATER
  public function indexMaster($companyId)
  {
    $masterTableName = $this->getAgreementsMasterTableName($companyId);
    return (new AgreementsMaster($masterTableName))->get();
  }

  public function uploadMaster($companyId, $file)
  {
    $masterTableName = $this->getAgreementsMasterTableName($companyId);

    $startRow = 2;
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load($file);

    $spreadsheet->setActiveSheetIndex(0);
    $worksheet = $spreadsheet->getActiveSheet();

    $rows = [];
    foreach ($worksheet->getRowIterator($startRow) as $keyRow => $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
      $cells = [];

      foreach ($cellIterator as $keyCell => $cell) {
        $cells[] = $worksheet->getCell($keyCell . $keyRow)->getValue();
      }

      $rows[] = [
        'account' => $cells[0],
        'line' => $cells[1],
        'name' => $cells[2],
        'created_at' => now(),
      ];
    }
    DB::beginTransaction();
    (new AgreementsMaster($masterTableName))->truncate();
    try {
      foreach (array_chunk($rows, 500) as $t) {
        DB::table($masterTableName)->insert($t);
      }
    } catch (Exception $e) {
      DB::rollBack();
      throw $e;
    }
    DB::commit();

    return $rows;
  }

  //HELPERS
  private function agreementsFileName($companyId, $headerId)
  {

    return "{$companyId}/agreements/{$headerId}.json";
  }

  private function agreementsFileToInsert($file, $headerId)
  {
    $startRow = 2;
    $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load($file);

    $spreadsheet->setActiveSheetIndex(0);
    $worksheet = $spreadsheet->getActiveSheet();

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
        'numcon' => $cells[0],
        'codcon' => $cells[1],
        'nitcli' => $cells[2],
        'nropag' => $cells[3],
        'fecuo' => $cells[4],
        'vlrcuo' => $this->fixedCurrency(',', $cells[5]),
        'vlrpag' => $this->fixedCurrency(',', $cells[6]),
        'salcuo' => $this->fixedCurrency(',', $cells[7]),
        'fecaso' => $cells[8],
        'fecpag' => $cells[9],
        'created_at' => now(),
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

  //TABLE CREATION
  public function createTableAgreementsHeaders($companyId)
  {
    $tableName = $this->getAgreemenetsHeadersTableName($companyId);

    Schema::create($tableName, function ($table) {
      $table->bigIncrements('id');
      $table->dateTime('date');
      $table->string('status');
      $table->string('user');

      $table->softDeletes();
      $table->timestamps();
    });
  }

  public function createTableConveniosItems($companyId)
  {
    $tableAgreementsItems = $this->getAgreemenetsItemsTableName($companyId);
    $tableAgreementsHeader = $this->getAgreemenetsHeadersTableName($companyId);

    Schema::create($tableAgreementsItems, function ($table) use ($tableAgreementsHeader) {
      $table->bigIncrements('id');
      $table->bigInteger('header_id')->unsigned();
      $table->string('numcon')->nullable();
      $table->string('codcon')->nullable();
      $table->string('nitcli')->nullable();
      $table->string('nropag')->nullable();
      $table->string('fecuo')->nullable();
      $table->decimal('vlrcuo', 24, 2)->nullable();
      $table->decimal('vlrpag', 24, 2)->nullable();
      $table->decimal('salcuo', 24, 2)->nullable();
      $table->string('fecaso')->nullable();
      $table->string('fecpag')->nullable();


      $table->softDeletes();
      $table->timestamps();

      $table->foreign('header_id')->references('id')->on($tableAgreementsHeader);
    });
  }

  public function createTableAgreementsMaster($companyId)
  {
    $tableName = $this->getAgreementsMasterTableName($companyId);

    Schema::create($tableName, function ($table) {
      $table->bigIncrements('id');
      $table->string('account')->index();
      $table->string('line');
      $table->string('name');

      $table->softDeletes();
      $table->timestamps();
    });
  }
}
