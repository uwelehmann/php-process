<?php

namespace UweLehmann\Process\Pipe;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 */
class File extends PipeAbstract
{
    /**
     * @param string $filename
     * @param string $mode
     */
    public function __construct($filename, $mode)
    {
        if (!is_file($filename)) {
            throw new Exception('invalid file "' . $filename . '"');
        }

        $this->setMode($mode);
        $this->setDescriptor([self::TYPE_FILE, $filename, $this->getMode()]);
    }
}
