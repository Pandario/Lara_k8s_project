<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class InvoiceSanitizationTest extends TestCase
{
    public function it_sanitizes_description_and_calculates_line_total()
    {
        $desc = "<script>alert('x')</script> valid 123";
        $clean = htmlspecialchars(preg_replace('/[^a-zA-Z0-9\s\-,.]/u', '', $desc));
        $this->assertEquals("alertx valid 123", $clean);

        $qty = 2; $price = 10; $vat = 21;
        $lineTotal = $qty * $price * (1 + $vat / 100);
        $this->assertEquals(24.2, $lineTotal);
    }
}