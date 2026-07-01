<?php

namespace Tests\Unit\Controller;

use App\Http\Controllers\Controller;
use Tests\TestCase;

/**
 * WB-CTRL-01 s/d WB-CTRL-02
 */
class TransactionCodeTest extends TestCase
{
    private function buildCode(int $id): string
    {
        $controller = new class extends Controller {
            public function code(int $id): string
            {
                return $this->buildTransactionCode($id);
            }
        };

        return $controller->code($id);
    }

    public function test_build_transaction_code_format(): void
    {
        $this->assertSame('SK-00007', $this->buildCode(7));
    }

    public function test_build_transaction_code_zero_pads_id(): void
    {
        $this->assertSame('SK-12345', $this->buildCode(12345));
    }
}
