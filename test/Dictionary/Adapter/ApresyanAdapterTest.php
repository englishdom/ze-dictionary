<?php

namespace Dictionary\Test\Adapter;

use Dictionary\Adapter\ApresyanAdapter;
use PHPUnit\Framework\TestCase;

class ApresyanAdapterTest extends TestCase
{
    public function dataProvider()
    {
        return [
            ['cat', 'Nothing similar to cat, sorry'],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testParsing($word, $example)
    {
        $path = 'data/dictionaries/apresyan';
        $adapter = new ApresyanAdapter($path);
        $dictionary = $adapter($word);

        $this->assertEquals($example, $dictionary->getExample());
    }
}
