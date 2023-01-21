<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CalculatorControllerTest extends TestCase
{
    private const ROUTE = "/api/calculator";

    public function testBasicOperations()
    {
        $response = $this->post(self::ROUTE, ["expression" => "1+1"]);
        $response->assertJson(["result" => "2"]);

        $response = $this->post(self::ROUTE, ["expression" => "1-3"]);
        $response->assertJson(["result" => "-2"]);

        $response = $this->post(self::ROUTE, ["expression" => "11*2"]);
        $response->assertJson(["result" => "22"]);

        $response = $this->post(self::ROUTE, ["expression" => "15/3"]);
        $response->assertJson(["result" => "5"]);

        $response = $this->post(self::ROUTE, ["expression" => "15*-2"]);
        $response->assertJson(["result" => "-30"]);

        $response = $this->post(self::ROUTE, ["expression" => "15/-2"]);
        $response->assertJson(["result" => "-7.5"]);
    }

    public function testSqrt()
    {
        $response = $this->post(self::ROUTE, ["expression" => "sqrt(4)"]);
        $response->assertJson(["result" => "2"]);

        $response = $this->post(self::ROUTE, ["expression" => "sqrt(-4)"]);
        $response->assertJson(["error" => "Invalid operation: sqrt of negative number"]);
    }

    public function testSpaces()
    {
        $response = $this->post(self::ROUTE, ["expression" => "2 +3"]);
        $response->assertJson(["result" => "5"]);

        $response = $this->post(self::ROUTE, ["expression" => "10- 2.1"]);
        $response->assertJson(["result" => "7.9"]);
    }

    public function testMultipleOperations()
    {
        $response = $this->post(self::ROUTE, ["expression" => "2 + 3 * 5 /2"]);
        $response->assertJson(["result" => "9.5"]);

        $response = $this->post(self::ROUTE, ["expression" => "-10 + sqrt(16)"]);
        $response->assertJson(["result" => "-6"]);

        $response = $this->post(self::ROUTE, ["expression" => "1 - sqrt(121)"]);
        $response->assertJson(["result" => "-10"]);
    }

    public function testDivisionByZero()
    {
        $response = $this->post(self::ROUTE, ["expression" => "10/0"]);
        $response->assertJson(["error" => "Cannot divide by zero"]);
    }

    public function testInvalidExpression()
    {
        $response = $this->post(self::ROUTE, ["expression" => "2.2.2 + 3 * 5 /2"]);
        $response->assertJson(["error" => "Invalid number"]);

        $response = $this->post(self::ROUTE, ["expression" => "2aaa + 3 * 5 /2"]);
        $response->assertStatus(302);
    }

    public function testParentheses()
    {
        $response = $this->post(self::ROUTE, ["expression" => "(2 + 3) * 5 /2"]);
        $response->assertJson(["result" => "12.5"]);

        $response = $this->post(self::ROUTE, ["expression" => "2 - (-2 -2)"]);
        $response->assertJson(["result" => "6"]);
    }

    public function testNesting()
    {
        $response = $this->post(self::ROUTE, ["expression" => "1 + sqrt(sqrt(32* (2+1) + 1)) - 5 * 6 / 10 + sqrt(15+1) + (4/(1+ 1))"]);
        $response->assertJson(["result" => "7.138288992715"]);
    }
}
