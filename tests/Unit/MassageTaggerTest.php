<?php

namespace Tests\Unit;

use App\Support\MassageTagger;
use PHPUnit\Framework\TestCase;

class MassageTaggerTest extends TestCase
{
    public function test_extracts_tags_from_shop_name(): void
    {
        $tags = MassageTagger::extract('女性専用 完全個室マッサージサロン');

        $this->assertContains('女性専用', $tags);
        $this->assertContains('完全個室', $tags);
    }

    public function test_returns_empty_array_when_no_keywords_match(): void
    {
        $tags = MassageTagger::extract('ふつうの店');

        $this->assertSame([], $tags);
    }
}
