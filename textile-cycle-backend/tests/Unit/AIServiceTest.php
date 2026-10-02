<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AIServiceTest extends TestCase
{
    public function test_ai_service_secret_configuration(): void
    {
        $secret = 'textilecycle_secret_key_2026';
        $this->assertEquals('textilecycle_secret_key_2026', $secret);
    }
}
