<?php

namespace Tests\Unit;

use App\Support\CountryCodeResolver;
use PHPUnit\Framework\TestCase;

class CountryCodeResolverTest extends TestCase
{
    public function test_resolves_from_country_name(): void
    {
        $this->assertSame('MX', CountryCodeResolver::resolve(null, 'México'));
        $this->assertSame('CO', CountryCodeResolver::resolve(null, 'Colombia'));
    }

    public function test_prefers_explicit_country_code(): void
    {
        $this->assertSame('AR', CountryCodeResolver::resolve('ar', 'México'));
    }

    public function test_returns_null_for_unknown_country(): void
    {
        $this->assertNull(CountryCodeResolver::resolve(null, 'Unknownland'));
    }

    public function test_resolves_iso_code_in_pais_field(): void
    {
        $this->assertSame('CO', CountryCodeResolver::resolve(null, 'CO'));
        $this->assertSame('MX', CountryCodeResolver::resolve(null, 'mx'));
    }
}
