<?php

namespace UweLehmann\ProcessTest;

use UweLehmann\Process\Process;
use UweLehmann\Process\Pipes;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 * @covers UweLehmann\Process
 */
class ProcessTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    const PATH_TO_PLAYGROUND = __DIR__ . '/playground';

    /** @var string */
    const CONTENT = 'Hello world!';

    /**
     *
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (realpath(static::PATH_TO_PLAYGROUND) === false
            && mkdir(static::PATH_TO_PLAYGROUND, 0700, true) === false) {
            throw new \Exception('can\'t create folder "' . static::PATH_TO_PLAYGROUND . '"');
        }
    }

    /**
     * testing pipe preset
     */
    public function testPipes()
    {
        $pipes = Pipes::getDefaultPipes();

        $this->assertInstanceOf(Pipes::class, $pipes);
        $this->assertCount(3, $pipes);
    }

    /**
     * testing working path is been considered
     *
     * @depends testPipes
     */
    public function testEnv()
    {
        $process = Process::factory('pwd', null, static::PATH_TO_PLAYGROUND, null);
        $this->assertInstanceOf(Process::class, $process);

        $exitCode = $process->run();
        $this->assertInternalType('integer', $exitCode);

        $this->assertNotEmpty($process->getOutput());
        $this->assertEmpty($process->getError());
        $this->assertTrue(trim(static::PATH_TO_PLAYGROUND) == trim($process->getOutput()));
    }

    /**
     * testing STDIN data will be processed
     *
     * @depends testEnv
     */
    public function testStdin()
    {
        $process = new Process(
            'read -p "Enter something: " value && echo $value',
            static::CONTENT . PHP_EOL,
            static::PATH_TO_PLAYGROUND,
            null
        );
        $this->assertInstanceOf(Process::class, $process);

        $exitCode = $process->run();
        $this->assertInternalType('integer', $exitCode);

        $this->assertNotEmpty($process->getOutput());
        $this->assertEmpty($process->getError());
        $this->assertTrue(trim(static::CONTENT) == trim($process->getOutput()));
    }
}
