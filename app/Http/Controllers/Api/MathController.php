<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class MathController extends Controller
{
    public function getRangeWithPostRequest(Request $request): JsonResponse
    {
        TimeService::start();
        $memory = memory_get_usage();

        $data = $request->input();
        $rules = [
            "x" => "required|numeric|max:9223372036854775807",
            "n" => "required|numeric|max:9223372036854775807",
            "m" => "required|numeric|max:9223372036854775807",
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                $validator->errors(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $expression = $this->expressionCount($data['x'],$data['n'],$data['m']);
        if (is_string($expression)) {
            return response()->json(
                $expression,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return response()->json(
            ['Результат' => [
                'Выражение' => $expression,
                'Время выполнения' => TimeService::finish() . 'sec',
                'Потрачено памяти' => (memory_get_usage() - $memory) . ' байт',
                'Максимальное значение для типа integer' =>  PHP_INT_MAX,
                'Размер целого числа в байтах в текущей сборке PHP' => PHP_INT_SIZE . ' байт',

            ]],
            Response::HTTP_OK
        );

    }

    public function getRangeWithGetRequest(Request $request): JsonResponse
    {
        TimeService::start();
        $x = $request->get('x');
        $n = $request->get('n');
        $m = $request->get('m');
        $memory = memory_get_usage();

        if ($x >= PHP_INT_MAX) {
            return response()->json(
                'Значенение вашей переменоой х превышает максимально доступное значение для типа integer',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $xVarValidate = $this->checkVariables($x,'x');
        $nVarValidate = $this->checkVariables($n,'n');
        $mVarValidate = $this->checkVariables($m,'m');

        if($xVarValidate) {
            return $xVarValidate;
        }

        if ($nVarValidate) {
            return $nVarValidate;
        }

        if ($mVarValidate) {
            return $mVarValidate;
        }

        $expression = $this->expressionCount($x,$n,$m);
        if (is_string($expression)) {
            return response()->json(
                $expression,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return response()->json(
            ['Результат' => [
                'Выражение' => $expression,
                'Время выполнения' => TimeService::finish() . 'sec',
                'Потрачено памяти' => (memory_get_usage() - $memory) . ' байт',
                'Максимальное значение для типа integer' =>  PHP_INT_MAX,
                'Размер целого числа в байтах в текущей сборке PHP' => PHP_INT_SIZE . ' байт',

            ]],
            Response::HTTP_OK
        );
    }

    public function getRangeWithRequestBubbleStyle(Request $request): JsonResponse
    {
        TimeService::start();
        $memory = memory_get_usage();

        $data = $request->input();
        $rules = [
            "x" => "required|numeric|max:9223372036854775807",
            "n" => "required|numeric|max:9223372036854775807",
            "m" => "required|numeric|max:9223372036854775807",
        ];

        $validator = Validator::make($data, $rules);
        $exponentiation = $data['x']**$data['n'];
        $moduloDivision = $exponentiation % $data['m'];

        if ($validator->fails()) {
            return response()->json(
                $validator->errors(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }elseif ($exponentiation > PHP_INT_MAX) {
            return response()->json(
                'Максимально возможное целое число при возведении в спень  в PHP равно'.' '. PHP_INT_MAX,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }else {
            return response()->json(
                ['Результат' => [
                    'Выражение' => $moduloDivision,
                    'Время выполнения' => TimeService::finish() . 'sec',
                    'Потрачено памяти' => (memory_get_usage() - $memory) . ' байт',
                    'Максимальное значение для типа integer' =>  PHP_INT_MAX,
                    'Размер целого числа в байтах в текущей сборке PHP' => PHP_INT_SIZE . ' байт',

                ]],
                Response::HTTP_OK
            );
        }
    }

    private function checkVariables($variable,$name): ?JsonResponse
    {
        if ($variable >= PHP_INT_MAX) {
            return response()->json(
                'Значенение вашей переменоой'.' '.$name.' '.'превышает максимально доступное значение для типа integer',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        return null;
    }

    private function expressionCount($x,$n,$m): int | string
    {
        $exponentiation = $x**$n;
        $moduloDivision = $exponentiation % $m;
        $validationNumber = is_int($exponentiation);
        switch ($validationNumber) {
            case (true):
                $result = $moduloDivision;
                break;
            case  (false):
                $result = 'Максимально возможное целое число в PHP равно'.' '. PHP_INT_MAX;
                break;
        }
            return $result;
    }
}
