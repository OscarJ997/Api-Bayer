<?php

namespace Tests\Unit;

use App\Support\NormalizeUrl;
use PHPUnit\Framework\TestCase;

class NormalizeUrlTest extends TestCase
{
    public function test_prepends_https_when_missing(): void
    {
        $this->assertSame('https://www.invima.gov.co', NormalizeUrl::apply('www.invima.gov.co'));
    }

    public function test_keeps_existing_https(): void
    {
        $this->assertSame('https://invima.gov.co', NormalizeUrl::apply('https://invima.gov.co'));
    }

    public function test_keeps_existing_http(): void
    {
        $this->assertSame('http://invima.gov.co', NormalizeUrl::apply('http://invima.gov.co'));
    }

    public function test_empty_returns_null(): void
    {
        $this->assertNull(NormalizeUrl::apply(''));
        $this->assertNull(NormalizeUrl::apply('   '));
    }
}
