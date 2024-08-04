<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $this->assertTrue(\Meirelles\EnumMapper\Test::getTrue());
    }
}
