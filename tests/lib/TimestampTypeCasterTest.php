<?php
use PHPUnit\Framework\TestCase;

class TimestampTypeCasterTest extends TestCase
{

    public function test_cast()
    {
        $this->assertSame(false, \PhpInjector\TimestampTypeCaster::cast('invalid date'));
        $this->assertSame(null, \PhpInjector\TimestampTypeCaster::cast(null));

        // hopefully that runs in the same second...
        $exp = strtotime('now');
        $this->assertSame($exp, \PhpInjector\TimestampTypeCaster::cast('now'));

        $this->assertSame(1420138800, \PhpInjector\TimestampTypeCaster::cast('1.1.2015 20:00:00+0100'));
        $exp = strtotime('1.1.2015');
        $this->assertSame($exp, \PhpInjector\TimestampTypeCaster::cast('1.1.2015'));
    }
}
