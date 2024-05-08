<?php

namespace App\Http\Controllers\MapFile;

use Exception;
use App\Models\MapFile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Services\MappingFile\MappingFileService;
use App\Http\Requests\MapFile\MapFileIndexRequest;
use App\Http\Requests\MapFile\PatchMappingRequest;
use App\Http\Requests\MapFile\MappingUploadRequest;
use App\Http\Resources\MapFile\MapFileIndexCollection;
use App\Http\Requests\MapFile\MappingFileToArrayRequest;

class MapFileController extends ApiController
{

    const ARRAY_SEPARATOR = array(',', ';', '|');
    private $user;
    private $companyId;
    private MappingFileService $mappingFileService;

    public function __construct(MappingFileService $mappingFileService)
    {
        $this->middleware('auth:api')->only(
            [
                'index',
                'show',
                'update',
                'store',
                'patch',
                'getMapIndex',
                'MappingFileToArray',
                'uploadMappingFile',
                'uploadFile',
                'saveMap',
            ]
        );

        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->companyId = $this->user->current_company;
            return $next($request);
        });

        $this->mappingFileService = $mappingFileService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(MapFileIndexRequest $request)
    {
        $mapFiles = $this->mappingFileService->index($this->companyId, $request->source);
        return $this->showArray(new MapFileIndexCollection($mapFiles), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MappingUploadRequest $request)
    {
        $type = $request->type == MapFile::TYPE_EXTERNAL
            ? MapFile::TYPE_CONCILIAR_EXTERNO
            : MapFile::TYPE_CONCILIAR_INTERNO;
        try {
            $mapFile = $this->mappingFileService->storeMapping(
                $this->user->id,
                $type,
                $request->description,
                $request->dateFormat,
                $request->separator,
                $request->skipTop,
                $request->skipBottom,
                $request->map,
                $request->base,
                $this->companyId,
                $type ==  MapFile::TYPE_CONCILIAR_EXTERNO ? $request->bankId : null
            );
            return $this->showOne($mapFile, 200);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MapFile $mapFile)
    {
        $rules = [
            'bank_id' => 'required',
            'base' => 'required',
            'description' => 'required',
            'type' => 'required',
            'map' => 'required',
        ];

        $request->validate($rules);

        $fields = $request->all();

        if ($fields['type'] == MapFile::TYPE_CONCILIAR_EXTERNO && !$fields['bank_id']) {

            $error = \Illuminate\Validation\ValidationException::withMessages([
                'bank_id' => ['El campo bank_id es obligatorio para archivos exgternos'],
            ]);
            throw $error;
        }

        if ($fields['type'] == MapFile::TYPE_CONCILIAR_INTERNO) {

            $mapFile->bank_id = null;
        } else {

            $mapFile->bank_id = $fields['bank_id'];
        }


        $mapFile->description = $fields['description'];
        $mapFile->type = $fields['type'];
        $mapFile->base = $fields['base'];
        $mapFile->map = $fields['map'];


        $mapFile->save();

        return $this->showOne($mapFile);
    }

    public function patch(PatchMappingRequest $request, MapFile $map)
    {
        $patched = $this->mappingFileService->patchMapping(
            $map,
            $request->description,
            $request->dateFormat,
            $request->separator,
            $request->skipTop,
            $request->skipBottom,
            $request->map
        );

        return $patched;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 
    }

    //TODO: MOVER AL GRUPO QUE NO SE ELIMINA, NO ELIMINAR SIN VALIDAR
    public function getMapIndex(Request $request)
    {
        $valiated = $request->validate([
            'type' => ['required', Rule::in([MapFile::TYPE_EXTERNAL, MapFile::TYPE_INTERNAL])]
        ]);

        $data = $this->mappingFileService->getMapIndex($request->type);
        return $this->showAll($data);
    }

    public function saveMap(Request $request)
    {

        $user = Auth::user();


        $rules = [
            'bank_id' => 'required',
            'description' => 'required',
            'type' => 'required',
            'map' => 'required',
        ];



        $request->validate($rules);


        $fields = $request->all();



        if ($fields['type'] == MapFile::TYPE_CONCILIAR_EXTERNO && !$fields['bank_id']) {

            $error = \Illuminate\Validation\ValidationException::withMessages([
                'bank_id' => ['El campo bank_id es obligatorio para archivos externos'],
            ]);
            throw $error;
        }

        if ($fields['type'] == MapFile::TYPE_CONCILIAR_INTERNO) {

            $fields['bank_id'] = null;
        }
        $fields['created_by'] = $user->id;
        $fields['header'] = false;
        $fields['company_id'] = (int)$user->current_company;
        $fields['separator'] = "";
        $fields['extension'] = "";

        // return $fields;
        $mapFile = MapFile::create($fields);

        return $this->showOne($mapFile);
    }

    public function MappingFileToArray(MappingFileToArrayRequest $request)
    {
        $data = $this->mappingFileService->MappingFileToArray($request->file, $request->skipTop);

        return $this->showMessage($data, 200);
    }

    public function uploadFile(Request $request)
    {


        $user = Auth::user();

        $ext = $request->file->extension() == 'txt' ? 'csv' : $request->file->extension();


        $request->file->storeAs('tmpUpload', $user->id . 'mapFile.' . $ext, 'local');


        $file = storage_path('app/tmUpload/' . $user->id . 'mapFile.' . $ext);

        if ($ext == 'csv' || $ext == 'txt') {

            $content = $this->getFormatSeparator($request->file);
            return $this->showArray($content);
        }

        $excel = \Excel::toArray(null, $request->file);

        $tmpArray = [$excel[0][0], $excel[0][1]];


        return $this->showArray($tmpArray);
    }

    public function formatsExterno($id)
    {

        $formats = MapFile::where('bank_id', $id)
            ->orWhere('bank_id', 29)
            ->get();

        return $this->showAll($formats);
    }

    public function formatsLocal($id)
    {

        $formats = MapFile::where('company_id', $id)
            ->where('type', MapFile::TYPE_CONCILIAR_INTERNO)
            ->get();

        return $this->showAll($formats);
    }

    private function getFormatSeparator($file)
    {

        $content = array();
        if (($gestor = $this->utf8_fopen_read($file)) !== FALSE) {

            $tmpArray = MapFileController::ARRAY_SEPARATOR;

            for ($i = 0; $i < count($tmpArray); $i++) {
                $content = array();
                $gestor = $this->utf8_fopen_read($file);
                for ($j = 0; $j < 2; $j++) {

                    if ($gestor) {

                        $content[] = fgetcsv($gestor, 0, $tmpArray[$i]);
                    }
                }


                if (is_array($content[0]) && count($content[0]) > 4) {

                    return $content;
                }
            }

            $error = \Illuminate\Validation\ValidationException::withMessages([
                'separador' => ['Solo se permiteeen los siguiente separadorees: ' . json_encode(MapFileController::ARRAY_SEPARATOR)],
            ]);
            throw $error;
        }
    }
}
