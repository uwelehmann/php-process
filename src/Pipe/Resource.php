<?php

namespace UweLehmann\Process\Pipe;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 */
class Resource extends PipeAbstract
{
    /**
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!is_resource($resource)) {
            throw new \Exception('invalid resource');
        }

        $this->setDescriptor($resource);
    }
}
