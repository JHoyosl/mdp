<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Carbon;

trait DatesTrait
{

  function getDateFormat($mapId)
  {
  }

  function validDay(int $day)
  {
    if ($day <= 31 && $day >= 1) {
      return true;
    }
    throw new Exception('Day must be between 1 and 31');
  }

  function validMonth(int $month)
  {
    if ($month <= 12 && $month >= 1) {
      return true;
    }
    throw new Exception('Month must be between 1 and 12');
  }

  function validYear(int $year)
  {
    if ($year > 1900) {
      return true;
    }
    throw new Exception('Year must be greater than 1900');
  }
}
