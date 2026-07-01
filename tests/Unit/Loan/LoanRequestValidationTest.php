<?php

namespace Tests\Unit\Loan;

use App\Http\Requests\StoreLoanRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * WB-LOAN-05 s/d WB-LOAN-08
 */
class LoanRequestValidationTest extends TestCase
{
    private function rules(): array
    {
        return (new StoreLoanRequest())->rules();
    }

    private function validPayload(): array
    {
        return [
            'customer_id' => 1,
            'period' => 12,
            'amount' => 5_000_000,
            'installment' => 416_666,
            'return_amount' => 4_999_992,
            'name' => 'BPKB Motor',
            'value' => 8_000_000,
            'description' => 'Jaminan kendaraan',
        ];
    }

    public function test_amount_must_be_greater_than_zero(): void
    {
        $payload = $this->validPayload();
        $payload['amount'] = 0;

        $validator = Validator::make($payload, $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_collateral_value_must_exceed_loan_amount(): void
    {
        $payload = $this->validPayload();
        $payload['value'] = 4_000_000;

        $validator = Validator::make($payload, $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('value', $validator->errors()->toArray());
    }

    public function test_period_must_be_greater_than_zero(): void
    {
        $payload = $this->validPayload();
        $payload['period'] = 0;

        $validator = Validator::make($payload, $this->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('period', $validator->errors()->toArray());
    }

    public function test_valid_loan_payload_passes_validation(): void
    {
        $validator = Validator::make($this->validPayload(), $this->rules());

        $this->assertFalse($validator->fails());
    }
}
