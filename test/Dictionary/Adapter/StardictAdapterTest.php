<?php

namespace Dictionary\Test\Adapter;

use Dictionary\Adapter\StardictAdapter;
use PHPUnit\Framework\TestCase;

class StardictAdapterTest extends TestCase
{
    public function dataProvider()
    {
        return [
            ['cat', 'wild cat', 'kæt'],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testParsing($word, $example, $transcription)
    {
        $path = 'data/dictionaries/stardict';
        $adapter = new StardictAdapter($path);
        $dictionary = $adapter($word);

        $this->assertEquals($example, $dictionary->getExample());
        $this->assertEquals($transcription, $dictionary->getTranscription());
    }
}
