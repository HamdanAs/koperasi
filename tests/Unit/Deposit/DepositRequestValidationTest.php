<?php

namespace Tests\Unit\Deposit;

use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\UpdateDepositRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * WB-DEP-01 s/d WB-DEP-07
 */
class DepositRequestValidationTest extends TestCase
{
    public function test_amount_must_be_greater_than_zero(): void
    {
        $validator = Validator::make(
            ['amount' => 0, 'type' => 'sukarela', 'customer_id' => 1],
            (new StoreDepositRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_loan_id_required_when_type_is_wajib(): void
    {
        $validator = Validator::make(
            ['amount' => 100_000, 'type' => 'wajib', 'customer_id' => 1],
            (new StoreDepositRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('loan_id', $validator->errors()->toArray());
    }

    public function test_loan_id_not_required_for_sukarela(): void
    {
        $validator = Validator::make(
            ['amount' => 100_000, 'type' => 'sukarela', 'customer_id' => 1],
            (new StoreDepositRequest())->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_customer_id_is_required(): void
    {
        $validator = Validator::make(
            ['amount' => 100_000, 'type' => 'sukarela'],
            (new StoreDepositRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('customer_id', $validator->errors()->toArray());
    }

    public function test_update_requires_balance_fields(): void
    {
        $validator = Validator::make(
            ['amount' => 100_000, 'type' => 'sukarela'],
            (new UpdateDepositRequest())->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('current_balance', $validator->errors()->toArray());
        $this->assertArrayHasKey('previous_balance', $validator->errors()->toArray());
    }

    public function test_valid_deposit_payload_passes(): void
    {
        $validator = Validator::make(
            [
                'amount' => 250_000,
                'type' => 'wajib',
                'customer_id' => 1,
                'loan_id' => 1,
            ],
            (new StoreDepositRequest())->rules()
        );

        $this->assertFalse($validator->fails());
    }
}
