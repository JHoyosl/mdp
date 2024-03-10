<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

trait ApiResponser
{

	private function successResponse($data, $code)
	{

		return response()->json($data, $code);
	}

	protected function errorResponse($message, $code)
	{

		return response()->json($message, ($code > 200 &&  $code < 600) ? $code : 500);
	}

	protected function showAll(Collection $collection, $message = '', $status = true, $code = 200)
	{

		return $this->successResponse(['data' => $collection, 'message' => $message, 'status' => $status], $code);
	}

	protected function showOne(Model $instance, $message = '', $status = true, $code = 200)
	{

		return $this->successResponse(['data' => $instance, 'message' => $message, 'status' => $status], $code);
	}

	protected function showMessage($message, $status = true, $code = 200)
	{

		return $this->successResponse(['data' => $message, 'status' => $status], $code);
	}

	protected function showArray($array, $message = '', $status = true, $code = 200)
	{

		return $this->successResponse(['data' => $array, 'message' => $message, 'status' => $status], $code);
	}

	protected function badRequestResponse($message = "Bad Request")
	{
		return response()->json($message, 400);
	}
}
