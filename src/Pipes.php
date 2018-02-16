<?php

namespace UweLehmann\Process;

use UweLehmann\Process\Pipe\PipeAbstract;
use UweLehmann\Process\Pipe\Pipe;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 */
class Pipes implements \Iterator, \ArrayAccess
{
    // indexes of the STD pipes
    const PIPE_STDIN_INDEX  = 0;
    const PIPE_STDOUT_INDEX = 1;
    const PIPE_STDERR_INDEX = 2;

    /**
     * prebuild of STD pipes with STDIN, STDOUT and STDERR
     *
     * @param string $stdinData
     * @return UweLehmann\Process\Pipes
     */
    public static function getDefaultPipes($stdinData = null)
    {
        $stdin = new Pipe(PipeAbstract::MODE_READ);
        $stdin->setData($stdinData);

        return new static([
            $stdin,                             // STDIN
            new Pipe(PipeAbstract::MODE_WRITE), // STDOUT
            new Pipe(PipeAbstract::MODE_WRITE), // STDERR
        ]);
    }

    /** @var UweLehmann\Process\Pipe\PipeAbstract[] */
    private $_pipes;

    /**
     * @param array $pipes
     */
    public function __construct(array $pipes)
    {
        $this->_pipes = $pipes;
    }

    /**
     * @return UweLehmann\Process\Pipe\PipeAbstract
     */
    public function rewind()
    {
        return reset($this->_pipes);
    }

    /**
     * @return UweLehmann\Process\Pipe\PipeAbstract
     */
    public function current()
    {
        return current($this->_pipes);
    }

    /**
     * @return integer
     */
    public function key()
    {
        return key($this->_pipes);
    }

    /**
     * @return UweLehmann\Process\Pipe\PipeAbstract
     */
    public function next()
    {
        return next($this->_pipes);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return key($this->_pipes) !== null;
    }

    /**
     * @param integer $offset
     */
    public function offsetExists($offset)
    {
        return isset($this->_pipes[$offset]);
    }

    /**
     * @param integer $offset
     */
    public function offsetGet($offset)
    {
        return isset($this->_pipes[$offset]) ? $this->_pipes[$offset] : null;
    }

    /**
     * @param integer $offset
     * @param UweLehmann\Process\Pipe\PipeAbstract $value
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof PipeAbstract) {
            throw new \Exception('invalid parameter type, expecting "UweLehmann\Process\Pipe\PipeAbstract"');
        }

        if (is_null($offset)) {
            $this->_pipes[] = $value;
        } else {
            $this->_pipes[$offset] = $value;
        }
    }

    /**
     * @param integer $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_pipes[$offset]);
    }
}
