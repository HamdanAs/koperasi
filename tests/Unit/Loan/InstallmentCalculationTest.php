<?php

namespace Tests\Unit\Loan;

use PHPUnit\Framework\TestCase;

/**
 * WB-LOAN-01 s/d WB-LOAN-04
 *
 * Spesifikasi bisnis dari resources/views/pages/transaction/loan/create.blade.php
 */
class InstallmentCalculationTest extends TestCase
{
    private function calculateInstallment(int $amount, int $period): int
    {
        return intdiv($amount, $period);
    }

    private function calculateReturnAmount(int $period, int $installment): int
    {
        return $period * $installment;
    }

    public function test_installment_equals_amount_divided_by_period(): void
    {
        $this->assertSame(100_000, $this->calculateInstallment(1_200_000, 12));
    }

    public function test_return_amount_equals_period_times_installment(): void
    {
        $installment = $this->calculateInstallment(1_200_000, 12);

        $this->assertSame(1_200_000, $this->calculateReturnAmount(12, $installment));
    }

    public function test_installment_uses_integer_division(): void
    {
        $this->assertSame(333_333, $this->calculateInstallment(1_000_000, 3));
    }

    public function test_return_amount_may_differ_from_amount_due_to_rounding(): void
    {
        $amount = 1_000_000;
        $period = 3;
        $installment = $this->calculateInstallment($amount, $period);
        $returnAmount = $this->calculateReturnAmount($period, $installment);

        $this->assertSame(999_999, $returnAmount);
        $this->assertNotSame($amount, $returnAmount);
    }
}
