<?php

namespace App\Services\MappingFile;

use App\Models\MapFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MappingFileService
{
  public function index($companyId, $type)
  {
    $sourceType = null;
    switch ($type) {
      case 'accounting':
        $sourceType = MapFile::TYPE_CONCILIAR_INTERNO;
        break;
      case 'thirdParty':
        $sourceType = MapFile::TYPE_CONCILIAR_EXTERNO;
        break;
    }

    //TODO: VALIDATE IF USER HAS PERMISSIONS
    $mapFiles = MapFile::with('createdBy')
      ->with('bank')
      ->with('company')
      ->where('company_id', $companyId);

    if ($sourceType) {
      $mapFiles->where('type', $sourceType);
    }

    return $mapFiles->get();
  }

  public function MappingFileToArray($file, $skipTop)
  {

    $ext = $file->extension() == 'txt' ? 'csv' : $file->extension();
    $filePath = rand() . '.' . $ext;
    $fullPath = storage_path('tmp') . '/' . $filePath;
    Storage::disk('tmp')->put($filePath, file_get_contents($file));

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    $spreadsheet = $reader->load($fullPath);
    $spreadsheet->setActiveSheetIndex(0);
    $worksheet = $spreadsheet->getActiveSheet();
    $startRow = $skipTop + 1;
    $count = 0;
    $data = [];
    foreach ($worksheet->getRowIterator($startRow) as $rowKey => $row) {
      $cellIterator = $row->getCellIterator();
      $cellIterator->setIterateOnlyExistingCells(true);

      $rows = [];
      $count++;
      foreach ($cellIterator as  $columnKey => $cell) {
        if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
          $rows[] = date("Y-m-d H:i:s", \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($cell->getValue()));
          continue;
        }
        $rows[] = $cell->getValue();
      }
      $data[] = $rows;
      if ($count == 2) {
        unlink($fullPath);
        return $data;
      }
    }
  }

  public function getMapIndex($type)
  {
    if (MapFile::TYPE_EXTERNAL) {
      return DB::Table('map_bank_index')
        ->orderBy('description')
        ->get();
    } else {
      return DB::Table('map_local_index')
        ->orderBy('description')
        ->get();
    }
  }

  public function storeMapping($userId, $type, $description, $dateFormat, $separator, $skipTop, $skipBottom, $map, $base, $companyId, $bankId = null)
  {

    $mapInfo = [
      'bank_id' => $bankId,
      'company_id' => $companyId,
      'header' => 0,
      'description' => $description,
      'created_by' => $userId,
      'type' => $type,
      'map' => $map,
      'base' => $base,
      'date_format' => $dateFormat,
      'separator' => $separator,
      'skip_top' => $skipTop,
      'skip_bottom' => $skipBottom,
      'extension' => 'extension',
    ];

    $mapFile = MapFile::create($mapInfo);

    return $mapFile;
    return [$type, $description, $dateFormat, $separator, $skipTop, $skipBottom, $map, $base, $bankId];
  }
}
