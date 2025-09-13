<?php

namespace Sediqzada\InertiaBlueprint\Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use Sediqzada\InertiaBlueprint\Enums\LanguageEnum;

class LanguageEnumTest extends TestCase
{
    public function test_only_typescript_is_supported(): void
    {
        $cases = LanguageEnum::cases();

        $this->assertCount(1, $cases);
        $this->assertEquals(LanguageEnum::TS, $cases[0]);
    }

    public function test_typescript_enum_has_correct_value(): void
    {
        $this->assertEquals('ts', LanguageEnum::TS->value);
    }

    public function test_extension_returns_tsx_for_typescript(): void
    {
        $this->assertEquals('tsx', LanguageEnum::TS->extension());
    }
}
