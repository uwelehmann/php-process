<?php

namespace UweLehmann\Process\Pipe;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 */
class Pipe extends PipeAbstract
{
    /** @var string */
    protected $_data;

    /** @var resource */
    protected $_stream;

    /**
     * @param string $mode
     */
    public function __construct($mode)
    {
        $this->setMode($mode);
        $this->setDescriptor([self::TYPE_PIPE, $this->getMode()]);
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param resource $stream
     */
    public function setStream($stream)
    {
        $this->_stream = $stream;
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->_stream;
    }
}
