<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Carbon;

trait DatesTrait
{

  private function transformDate($format, $date)
  {
    if($format == 'yyyymmdd') {
      return $this->parseFromYYYYMMDD($date);
    }

    $fixDate = $date;
    if (strlen($date) > 10) {
      $fixDate = substr($date, 0, 10);
    }
    return Carbon::parse($fixDate);
  }

  /**
   * Parse date from yyyymmdd format to Y-m-d
   * @param string|int $date Date in yyyymmdd format (e.g., "20251001" or 20251001)
   * @return string Date in Y-m-d format (e.g., "2025-10-01")
   */
  function parseFromYYYYMMDD($date)
  {
    $dateString = (string) $date;
    
    if (strlen($dateString) !== 8) {
      throw new Exception('Date must be in yyyymmdd format (8 digits)');
    }
    
    return Carbon::createFromFormat('Ymd', $dateString);
  }

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
