<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ApiController extends Controller
{
    use ApiResponser;

    const ARRAY_SEPARATOR = array(',', ';', '|');
    private $dateFormats = array('Ymd', 'dmY',);

    public function __construct()
    {

        $this->middleware('auth:api');
    }

    public function getMapBancIndexTable()
    {

        $base = DB::Table('map_banc_index')->get();

        return $base;
    }

    function utf8_fopen_read($fileName)
    {

        $fc = iconv('windows-1250', 'utf-8', file_get_contents($fileName));
        $handle = fopen("php://memory", "rw");
        fwrite($handle, $fc);
        fseek($handle, 0);
        return $handle;
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
