<?php

namespace UweLehmann\Process\Pipe;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 */
class PipeAbstract
{
    /** */
    const TYPE_PIPE = 'pipe';
    const TYPE_FILE = 'file';

    /** */
    const MODE_READ = 'r';
    const MODE_WRITE = 'w';

    /**
     * @var array|resource
     */
    protected $_descriptor;

    /**
     * @var string|null
     */
    protected $_mode;

    /**
     * @param array|resource $descriptor
     */
    protected function setDescriptor($descriptor)
    {
        $this->_descriptor = $descriptor;
    }

    /**
     * @return array|resource
     */
    public function getDescriptor()
    {
        return $this->_descriptor;
    }

    /**
     * @param string $mode
     * @throws Exception
     */
    public function setMode($mode)
    {
        if (!in_array($mode, [self::MODE_READ, self::MODE_WRITE])) {
            throw new \Exception('invalid mode "' . $mode . '"');
        }
        $this->_mode = $mode;
    }

    /**
     * @return string|null
     */
    public function getMode()
    {
        return $this->_mode;
    }
}
