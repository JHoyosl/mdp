<?php

namespace App\Services\MappingFile;

use App\Models\Account;
use App\Models\Company;
use App\Models\MapFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MappingFileService
{
  public function index($companyId, $type)
  {
    if ($type == 'accounting') {
      $mapFiles = MapFile::with('createdBy')
        ->with('bank')
        ->with('company')
        ->where('company_id', $companyId)
        ->where('type', MapFile::TYPE_CONCILIAR_INTERNO);
    }

    if ($type == 'thirdParty') {
      $mapFiles = MapFile::with('createdBy')
        ->with('bank')
        ->with('company')
        ->where('type', MapFile::TYPE_CONCILIAR_EXTERNO);
    }

    if ($type == 'all') {
      $mapFiles = MapFile::with('createdBy')
        ->with('bank')
        ->with('company');
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
    if ($type == MapFile::TYPE_EXTERNAL) {
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
  }

  public function patchMapping(MapFile $mapping, $description, $dateFormat, $separator,  $skipTop, $skipBottom, $map)
  {
    $mapping->description = $description;
    $mapping->date_format = $dateFormat;
    $mapping->description = $separator;
    $mapping->skip_top = $skipTop;
    $mapping->skip_botton = $skipBottom;
    $mapping->map = $map;

    $mapping->save();
    return $mapping;
  }

  public function delete($id)
  {
    $map = MapFile::findOrFail($id);
    Account::where('map_id', $map->id)->update(['map_id' => null]);
    Company::findOrFail($map->company_id)->update(['map_id' => null]);

    return MapFile::findOrFail($id)->delete();
  }
}
