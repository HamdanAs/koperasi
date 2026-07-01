<?php

namespace Tests\Unit\Loan;

use App\Models\Collateral;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Loan;
use App\Traits\LoanTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WB-TRAIT-01 s/d WB-TRAIT-03
 */
class LoanTraitTest extends TestCase
{
    use RefreshDatabase;

    private function traitInstance(): object
    {
        return new class {
            use LoanTrait;
        };
    }

    private function createLoan(): Loan
    {
        $customer = Customer::create([
            'nik' => '3201010101010001',
            'name' => 'Nasabah Uji',
            'number' => 'NSB-001',
            'gender' => 'L',
            'status' => 'active',
        ]);

        $collateral = Collateral::create([
            'name' => 'BPKB',
            'value' => 10_000_000,
            'customer_id' => $customer->id,
        ]);

        return Loan::create([
            'period' => 12,
            'amount' => 5_000_000,
            'installment' => 416_666,
            'return_amount' => 4_999_992,
            'paid' => 0,
            'customer_id' => $customer->id,
            'collateral_id' => $collateral->id,
        ]);
    }

    public function test_paid_loan_sums_wajib_deposits_only(): void
    {
        $loan = $this->createLoan();

        Deposit::create([
            'type' => 'wajib',
            'amount' => 100_000,
            'previous_balance' => 0,
            'current_balance' => 100_000,
            'customer_id' => $loan->customer_id,
            'loan_id' => $loan->id,
        ]);

        Deposit::create([
            'type' => 'wajib',
            'amount' => 150_000,
            'previous_balance' => 100_000,
            'current_balance' => 250_000,
            'customer_id' => $loan->customer_id,
            'loan_id' => $loan->id,
        ]);

        $this->traitInstance()->paidLoan($loan->id);

        $this->assertSame(250_000, $loan->fresh()->paid);
    }

    public function test_paid_loan_ignores_non_wajib_deposits(): void
    {
        $loan = $this->createLoan();

        Deposit::create([
            'type' => 'wajib',
            'amount' => 100_000,
            'previous_balance' => 0,
            'current_balance' => 100_000,
            'customer_id' => $loan->customer_id,
            'loan_id' => $loan->id,
        ]);

        Deposit::create([
            'type' => 'sukarela',
            'amount' => 500_000,
            'previous_balance' => 0,
            'current_balance' => 500_000,
            'customer_id' => $loan->customer_id,
            'loan_id' => null,
        ]);

        $this->traitInstance()->paidLoan($loan->id);

        $this->assertSame(100_000, $loan->fresh()->paid);
    }

    public function test_paid_loan_updates_loan_paid_column(): void
    {
        $loan = $this->createLoan();

        $result = $this->traitInstance()->paidLoan($loan->id);

        $this->assertTrue((bool) $result);
        $this->assertSame(0, $loan->fresh()->paid);
    }
}
