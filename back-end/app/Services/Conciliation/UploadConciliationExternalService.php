<?php

namespace App\Services\Conciliation;

use App\Models\User;
use App\Models\Account;
use App\Models\MapFile;
use App\Models\ConciliarItem;
use App\Models\ExternalTxType;
use App\Models\ConciliarHeader;
use App\Traits\SheetToolsTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\Account\AccountService;

class UploadConciliationExternalService
{
    use SheetToolsTrait;

    protected $reconciliationHeader = "";
    protected $reconciliationItems = "";
    protected $reconciliationItemsTemp = "";

    private AccountService $accountService;
    private ConciliationService $reconciliationService;
    private $filePath;

    public function __construct(
        AccountService $accountService,
        ConciliationService $reconciliationService
    ) {
        $this->accountService = $accountService;
        $this->reconciliationService = $reconciliationService;
    }

    public function processFile($user, $account, $file)
    {

        $path = $this->saveFile($user, $account->id, $file);

        $actualHeader = $this->getCurrentReconciliation($user);
        $currentItem = $this->getCurrentItem($user, $actualHeader->id, $account->id);

        // Turn file into MDP domain fileds to be save into  DB
        $mappedFile = $this->fileToArray($user, $path, $account, $currentItem);

        $tmpItems = $this->save($user->current_company, $actualHeader->id, $account->id, $mappedFile);

        $currentItem->credit_externo = $tmpItems->credit_externo;
        $currentItem->debit_externo = $tmpItems->debit_externo;

        $currentItem->save();

        return $tmpItems;
    }

    public function saveFile(User $user, String $accountId, $file): String
    {
        $this->reconciliationItems = 'conciliar_tmp_items_' . $user->current_company;

        $conciliarHeader = $this->getCurrentReconciliation($user);
        $itemTable = new ConciliarItem($this->reconciliationItems);

        $currentItem = $itemTable
            ->where('header_id', '=', $conciliarHeader->id)
            ->where('account_id', '=', $accountId)
            ->first();

        $ext = $file->extension() == 'txt' ? 'csv' : $file->extension();

        $account = $this->accountService->getAccountById($accountId);

        $this->filePath = $user->current_company . '/' . $conciliarHeader->id . '/external/' . $account['bank_account'] . '.' . $ext;

        Storage::disk('conciliation')->put($this->filePath, file_get_contents($file));

        return storage_path('conciliation/') . '/' . $this->filePath;
    }

    public function getFile(String $path)
    {
        return Storage::disk('conciliation')->get($path);
    }

    public function getCurrentItem(User $user, String $headerId, String $accountId): ConciliarItem
    {
        $reconciliationItems = 'conciliar_items_' . $user->current_company;

        $itemTable = new ConciliarItem($reconciliationItems);

        $currentItem = $itemTable->where('header_id', '=', $headerId)
            ->where('account_id', '=', $accountId)
            ->first();

        if ($currentItem == null) {

            $item = new ConciliarItem($reconciliationItems);

            $explodeName = explode('/', $this->filePath);
            $fileName = $explodeName[count($explodeName) - 1];

            $itemInfo = [
                'header_id' => $headerId,
                'account_id' => $accountId,
                'debit_externo' => 0,
                'debit_local' => 0,
                'credit_externo' => 0,
                'credit_local' => 0,
                'balance_externo' => 0,
                'balance_local' => 0,
                'file_path' => $this->filePath,
                'file_name' => $fileName,
                'total' => 0,
                'status' => ConciliarHeader::OPEN_STATUS,
            ];
            $item->insert($itemInfo);

            $currentItem = $itemTable->where('header_id', '=', $headerId)
                ->where('account_id', '=', $accountId)
                ->first();
        }
        return $currentItem;
    }

    /**
     * If exist an open conciliacion return, if not exist create one and retur
     * @param User $user
     * @return ConciliarHeader $conciliarHeader
     * 
     */
    public function getCurrentReconciliation(User $user)
    {
        $reconciliationHeader = 'conciliar_headers_' . $user->current_company;

        $conciliarHeaderTable = new ConciliarHeader($reconciliationHeader);
        $header = $conciliarHeaderTable
            ->where('status', ConciliarHeader::OPEN_STATUS)
            ->orderBy('id', 'desc')
            ->first();

        if ($header == null) {

            $header = new ConciliarHeader($reconciliationHeader);

            $header->insert(
                [
                    'fecha_ini' => date('Y-m-d H:i:s'),
                    'created_by' => $user->id,
                    'status' => ConciliarHeader::OPEN_STATUS,

                ]
            );
        }

        return $header;
    }

    public function getTempItems(String $companyId, String $headerId,  String $accountId): ConciliarItem
    {
        $tmpItemsTable = 'conciliar_tmp_items_' . $companyId;

        if (!Schema::hasTable($tmpItemsTable)) {

            $this->reconciliationService->createTmpTableConciliarItems($companyId);
        }

        $tmpItemsTable = new ConciliarItem($tmpItemsTable);

        $tmpItems = $tmpItemsTable
            ->where('header_id', $headerId)
            ->where('account_id', $accountId)
            ->first();


        if (!$tmpItems) {

            $item = [
                'header_id' => $headerId,
                'account_id' => $accountId,
                'debit_externo' => 0,
                'debit_local' => 0,
                'credit_externo' => 0,
                'credit_local' => 0,
                'balance_externo' => 0,
                'balance_local' => 0,
                'total' => 0,
                'status' => ConciliarItem::OPEN_STATUS,

            ];

            $tmpItemsTable->insert($item);

            $tmpItems = $tmpItemsTable->where('header_id', $headerId)
                ->where('account_id', $accountId)
                ->first();
        }

        return $tmpItems;
    }

    public function fileToArray(User $user, String $path, Account $account, ConciliarItem $item)
    {
        $companyId = $account->company_id;

        $tmpExternalValuesTable = 'conciliar_tmp_external_values_' . $companyId;

        // TODO: is this necesary?
        if (!Schema::hasTable($tmpExternalValuesTable)) {

            $this->reconciliationService->createTmpTableConciliarExternalValues($companyId);
        }

        $header = $this->getCurrentReconciliation($user);
        $tmpItems = $this->getTempItems($user->current_company, $header->id, $account->id);

        $mapFile = MapFile::find($account->map_id);

        $map =  json_decode($mapFile->map, true);

        // Build new array with index to improve performance
        $indexMap =  [];
        foreach ($map as $value) {
            $indexMap[$value['fileColumn']] = $value['value'];
        }

        $separator = $mapFile->separator;

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();

        $startRow = 2;

        $mappedInfo = [];

        foreach ($worksheet->getRowIterator($startRow) as $rowKey => $row) {

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            $mappedRow = [];

            // Init  in -1 to start adding before conditions return
            $fileColumn = -1;
            foreach ($cellIterator as  $calumnKey => $cell) {
                $fileColumn++;

                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    $mappedRow[$indexMap[$fileColumn]] = date("Y-m-d H:i:s", \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($cell->getValue()));
                    continue;
                }
                $mappedRow[$indexMap[$fileColumn]] = $cell->getValue();

                if (in_array($indexMap[$fileColumn], ["VALOR CRÉDITO", "VALOR DEBITO", "VALOR (DEBITO/CREDITO)"])) {
                    $mappedRow[$indexMap[$fileColumn]] = $this->currencyToDecimal($mappedRow[$indexMap[$fileColumn]], $separator);
                }
            }

            if (count($mappedRow) == 0) continue;
            $tmpInsertCell  = $this->cellToInsertExterno($mappedRow);

            $txInfo = $this->getTxInfo($tmpInsertCell, $account->bank_id);

            $tmpInsertCell['item_id'] = $tmpItems['id'];
            if ($txInfo[0]) {
                $tmpInsertCell['tx_type_id'] = $txInfo[1]['id'];
                $tmpInsertCell['tx_type_name'] = $txInfo[1]['tx'];
            } else {
                $error = \Illuminate\Validation\ValidationException::withMessages(
                    ['No existe una transacción con descripción: ' . $tmpInsertCell['descripcion'], $tmpInsertCell, $txInfo]
                );
                throw $error;
            }

            $mappedInfo[]  = $tmpInsertCell;
        }

        return $mappedInfo;
    }

    //TODO: Improve this implementation
    public function currencyToDecimal($value, $separator)
    {
        $value = str_replace("$", "", $value);
        if ($separator == ".") {
            $value = str_replace(",", "", $value);
            $value = str_replace(".", ".", $value);
            return  $value;
        }
        $value = str_replace(".", "", $value);
        $value = str_replace(",", ".", $value);

        return  $value;
    }

    public function cellToInsertExterno($insertCell)
    {
        $insert =   [
            'tx_type_id' => '',
            'tx_type_name' => '',
            'item_id' => '',
            'descripcion' => $insertCell["TIPO DE TRANSACCION/DESCRIPCION"] ?? null,
            'operador' => '', // $insertCell["OPERADOR"],
            'valor_credito' => $insertCell["VALOR CRÉDITO"] ?? null,
            'valor_debito' => $insertCell["VALOR DEBITO"] ?? null,
            'valor_debito_credito' => $insertCell["VALOR (DEBITO/CREDITO)"] ?? null,
            'fecha_movimiento' => $insertCell["FECHA DEL MOVIMIENTO"] ?? null,
            'fecha_archivo' => $insertCell["FECHA DEL ARCHIVO"] ?? null,
            'codigo_tx' => $insertCell["CODIGO DE TRANSACCION"] ?? null,
            'referencia_1' => $insertCell["REFERENCIA 1"] ?? null,
            'referencia_2' => $insertCell["REFERENCIA 2"] ?? null,
            'referencia_3' => $insertCell["REFERENCIA 3"] ?? null,
            'nombre_titular' => $insertCell["NOMBRE TITULAR"] ?? null,
            'identificacion_titular' => $insertCell["IDENTIFICACION TITULAR"] ?? null,
            'numero_cuenta' => $insertCell["NUMERO DE CUENTA"] ?? null,
            'nombre_transaccion' => $insertCell["NOMBRE DE TRANSACCION"] ?? null,
            'consecutivo_registro' => $insertCell["CONSECUTIVO DE REGISTROS"] ?? null,
            'nombre_oficina' => $insertCell["NOMBRE OFICINA"] ?? null,
            'codigo_oficina' => $insertCell["CODIGO OFICINA"] ?? null,
            'canal' => $insertCell["CANAL"] ?? null,
            'nombre_proveedor' => $insertCell["NOMBRE PROVEEDOR"] ?? null,
            'id_proveedor' => $insertCell["IDENTIFICACION DE PROVEEDOR"] ?? null,
            'banco_destino' => $insertCell["BANCO DESTINO"] ?? null,
            'fecha_rechazo' => $insertCell["FECHA DE RECHAZO"] ?? null,
            'motivo_rechazo' => $insertCell["MOTIVO DE RECHAZO"] ?? null,
            'ciudad' => $insertCell["CIUDAD"] ?? null,
            'tipo_cuenta' => $insertCell["TIPO DE CUENTA"] ?? null,
            'numero_documento' => $insertCell["NUMERO DE DOCUMENTO"] ?? null,

        ];

        return $insert;
    }

    public function getTxInfo($values, $bank_id)
    {

        $externalTxTable = ExternalTxType::where('bank_id', $bank_id)
            ->where('reference', 'like', '%' . $values['codigo_tx'] . '%')
            ->get();

        if (is_numeric($values['codigo_tx'])) {

            for ($j = 0; $j < count($externalTxTable); $j++) {

                if (intval($externalTxTable[$j]['reference']) == intval($values['codigo_tx'])) {

                    return [true, $externalTxTable[0]];
                } else {

                    if ($externalTxTable[$j]['reference'] == $values['codigo_tx']) {

                        return [true, $externalTxTable[0]];
                    }
                }
            }
        }

        $externalTxTable = ExternalTxType::where('bank_id', $bank_id)
            ->where('reference', 'like', '%' . $values['codigo_tx'] . '%')
            ->get();


        if (count($externalTxTable) > 0) {

            return [true, $externalTxTable[0]];
        }

        $externalTxTable = ExternalTxType::where('bank_id', $bank_id)
            ->where('description', 'like', '%' . $values['descripcion'] . '%')
            ->get();



        if (count($externalTxTable) > 0) {

            return [true, $externalTxTable[0]];
        } else {

            return [false, $externalTxTable];
        }
    }

    public function save(String $companyId, $headerId, $accountId, $data)
    {
        $tmpItems = $this->getTempItems($companyId, $headerId, $accountId);

        $tmpExternalValues = 'conciliar_tmp_external_values_' . $companyId;
        $externalValuesTmp = new ConciliarItem($tmpExternalValues);

        $externalValuesTmp->where('item_id', $tmpItems->id)->delete();


        // TODO: BATCH INSERT
        $externalValuesTmp->insert($data);

        $query = DB::table($tmpExternalValues)
            ->select(DB::raw("valor_credito as credit, valor_debito as debit, valor_debito_credito as mix, item_id"))
            ->where('item_id', $tmpItems->id)
            ->get();

        $tmpItems->credit_externo = 0;
        $tmpItems->debit_externo = 0;
        foreach ($query as $key => $value) {
            if (floatval($value->credit) > 0 || floatval($value->debit) > 0) {
                $tmpItems->credit_externo += $value->credit ? $value->credit : 0;
                $tmpItems->debit_externo += $value->debit ? $value->debit : 0;
            } else {
                if ($value->mix <= 0) {
                    $tmpItems->debit_externo += $value->mix;
                } else {
                    $tmpItems->credit_externo += $value->mix;
                }
            }
        }
        $tmpItems->save();
        return $tmpItems;
    }
}
