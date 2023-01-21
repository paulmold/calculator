<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function calculate(Request $request): JsonResponse {
        $validated = $request->validate([
            "expression" => "required|regex:/^[0-9+-\/* ()\bsqrt\b]+$/",
        ]);

        $expression = str_replace(" ", "", $validated["expression"]);

        try {
            $result = $this->computeFromString($expression);
        } catch (\Exception $exception) {
            return response()->json(["error" => $exception->getMessage()]);
        }


        return  response()->json(["result" => $result]);
    }

    /**
     * Recursively computes an expression that contains parentheses and sqrt
     *
     * @param string $expression
     * @return string
     * @throws \Exception
     */
    private function computeFromString(string $expression): string {
        $countParentheses = 0;
        $expression = preg_replace_callback("/[^t]\(([0-9+-\/*]+)\)/", function($matches) {
            return substr($matches[0], 0, 1) . $this->computeBasicOperationFromString($matches[1]);
        }, $expression, -1, $countParentheses);

        if ($countParentheses != 0) {
            return $this->computeFromString($expression);
        }

        $countSqrt = 0;
        $expression = preg_replace_callback("/sqrt\(([0-9+-\/*]+)\)/", function($matches) {
            return sqrt(floatval($this->computeBasicOperationFromString($matches[1])));
        }, $expression, -1, $countSqrt);

        if ($countSqrt != 0) {
            return $this->computeFromString($expression);
        }

        return $this->computeBasicOperationFromString($expression);
    }

    /**
     * Recursively compute an expression that contains only numbers and operators (+, -, /, *)
     * Throws exception in case of invalid number or division by zero
     *
     * @param string $expression
     * @return string
     * @throws \Exception
     */
    private function computeBasicOperationFromString(string $expression): string {
        $countMulDiv = 0;
        $countAddSub = 0;

        $expression = preg_replace_callback("/(-*[0-9.]+)([*\/])(-*[0-9.]+)/", function ($matches) {
            $number1 = floatval($matches[1]);
            $number2 = floatval($matches[3]);
            if ($number1 != $matches[1] || $number2 != $matches[3]) {
                throw new \Exception("Invalid number");
            }
            if ($matches[2] == "/") {
                if ($number2 == 0) {
                    throw new \Exception("Cannot divide by zero");
                }

                return $number1 / $number2;
            }

            if ($number1 < 0 && $number2 < 0) {
                return "+" . $number1 * $number2;
            }

            return $number1 * $number2;
        }, $expression, 1, $countMulDiv);

        if ($countMulDiv == 0) {
            $expression = preg_replace_callback("/(-*[0-9.]+)([+-])(-*[0-9.]+)/", function ($matches) {
                $number1 = floatval($matches[1]);
                $number2 = floatval($matches[3]);
                if ($number1 != $matches[1] || $number2 != $matches[3]) {
                    throw new \Exception("Invalid number");
                }
                if ($matches[2] == "-") {
                    return $number1 - $number2;
                }

                return $number1 + $number2;
            }, $expression, 1, $countAddSub);

            if ($countAddSub == 0) {
                return $expression;
            }

            return $this->computeBasicOperationFromString($expression);
        }

        return $this->computeBasicOperationFromString($expression);
    }
}
