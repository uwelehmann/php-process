<?php

namespace UweLehmann\Process;

use UweLehmann\Process\Pipes;
use UweLehmann\Process\Pipe\PipeAbstract;
use UweLehmann\Process\Pipe\Pipe;

/**
 *
 * @author Uwe Lehmann <lehmann.uwe@gmx.de>
 * @copyright (c) 2017, Uwe Lehmann
 */
class Process
{
    /** @var resource */
    protected $_process;

    /** @var string */
    protected $_cmd;

    /** @var string */
    protected $_cwd;

    /** @var array */
    protected $_env = [];

    /** @var UweLehmann\Process\Pipes */
    protected $_pipes;

    /**
     *
     * @param string $command
     * @param string $stdin [optional] data send to STDIN
     * @param string $path [optional] the initial working dir for the command
     * @param array $env [optional] an array with the environment variables for the command that will be run
     * @return UweLehmann\Process\Process
     */
    public static function factory($command, $stdin = null, $path = null, array $env = null)
    {
        return new static($command, $stdin, $path, $env);
    }

    /**
     *
     * @param string $command
     * @param string $stdin [optional] data send to STDIN
     * @param string $path [optional] the initial working dir for the command
     * @param array $env [optional] an array with the environment variables for the command that will be run
     */
    public function __construct($command, $stdin = null, $path = null, array $env = null)
    {
        $this->_cmd = $command;
        $this->_cwd = $path;
        $this->_env = $env;
        $this->_pipes = Pipes::getDefaultPipes($stdin);
    }

    /**
     * add a pipe to to list
     *
     * @param UweLehmann\Process\Pipe\Pipe $pipe
     */
    public function addPipe(Pipe $pipe)
    {
        $this->_pipes[] = $pipe;
        return $this;
    }

    /**
     * returns pipes used for this process
     *
     * @return UweLehmann\Process\Pipes
     */
    public function getPipes()
    {
        return $this->_pipes;
    }

    /**
     * returns data from STDOUT pipe
     *
     * @return string|null
     */
    public function getOutput()
    {
        if ($this->_pipes->offsetExists(Pipes::PIPE_STDOUT_INDEX)) {

            $stdOut = $this->_pipes->offsetGet(Pipes::PIPE_STDOUT_INDEX);
            if ($stdOut instanceof Pipe) {
                return $stdOut->getData();
            }
        }

        return null;
    }

    /**
     * returns data from STDERR pipe
     *
     * @return string|null
     */
    public function getError()
    {
        if ($this->_pipes->offsetExists(Pipes::PIPE_STDERR_INDEX)) {

            $stdErr = $this->_pipes->offsetGet(Pipes::PIPE_STDERR_INDEX);
            if ($stdErr instanceof Pipe) {
                return $stdErr->getData();
            }
        }

        return null;
    }

    /**
     * execute the process
     *
     * @return integer
     */
    public function run()
    {
        $pipes = null;

        // execute
        $this->_process = proc_open(
            $this->_cmd,
            $this->_getDescriptorSpec(),
            $pipes,
            $this->_cwd,
            $this->_env
        );

        if (!is_resource($this->_process)) {
            throw new \Exception('process can not be started');
        }

        // set pipes to non blocking mode and store streams
        foreach ($this->_pipes as $index => $pipe) {

            if ($pipe instanceof Pipe) {
                $pipe->setStream($pipes[$index]);
                stream_set_blocking($pipe->getStream(), 0);
            }
        }

        // handle process data
        do {

            // let's have a look if something changed in streams
            foreach ($this->_pipes as $index => $pipe) {

                if ($pipe instanceof Pipe && $this->_streamStateReady($pipe)) {
                    $this->_streamReadWrite($pipe);
                }
            }
            usleep(250);

        } while ($this->_isRunning());

        // read the rest of the pipes
        foreach ($this->_pipes as $index => $pipe) {

            if ($pipe instanceof Pipe
                && $pipe->getMode() == PipeAbstract::MODE_WRITE
                && $this->_streamStateReady($pipe)
            ) {
                $this->_streamReadWrite($pipe);
            }
        }

        // close all pipes and the process
        foreach ($this->_pipes as $index => $pipe) {

            if (is_resource($pipe->getStream())) {
                fclose($pipe->getStream());
            }
        }

        return proc_close($this->_process);
    }

    /**
     * get the descriptor information of all pipes
     *
     * @return array
     */
    private function _getDescriptorSpec()
    {
        $descriptorspec = [];

        /** @var \UweLehmann\Process\Pipe\PipeAbstract $pipe */
        foreach ($this->_pipes as $pipe) {
            $descriptorspec[] = $pipe->getDescriptor();
        }

        return $descriptorspec;
    }

    /**
     * check is process is still running
     *
     * @return boolean
     */
    private function _isRunning()
    {
        $status = proc_get_status($this->_process);
        return $status["running"];
    }

    /**
     * testing pipe stream state for any changes
     *
     * @param UweLehmann\Process\Pipe\Pipe $pipe
     * @return boolean
     * @throws Exception
     */
    private function _streamStateReady(Pipe $pipe)
    {
        $stream = $pipe->getStream();
        if (!is_resource($stream)) {
            return false;
        }

        $r = $w = $e = [];
        switch ($pipe->getMode()) {

            // check if pipe in read mode will accept data without blocking
            case PipeAbstract::MODE_READ:
                $w[] = $stream;
                break;

            // check if pipe in write mode will provide data without blocking
            case PipeAbstract::MODE_WRITE:
                $r[] = $stream;
                break;

            default:
                throw new \Exception('pipe has an unhandled/unknown pipe mode');
        }

        // execute test
        $n = @stream_select($r, $w, $e, 0, 200000);

        if ($n === false) {
            throw new \Exception('error on stream_select call');
        }

        return ($n > 0);
    }

    /**
     * execute read and write operation on the given pipe stream
     *
     * @param UweLehmann\Process\Pipe\Pipe $pipe
     * @return boolean
     * @throws Exception
     */
    private function _streamReadWrite(Pipe $pipe)
    {
        $stream = $pipe->getStream();
        if (!is_resource($stream)) {
            return false;
        }

        switch ($pipe->getMode()) {

            // pipe in read mode is waiting for some data, so we write data into it
            case PipeAbstract::MODE_READ:

                if ($pipe->getData() !== false && !empty($pipe->getData())) {
                    $pipe->setData(substr(
                        $pipe->getData(),
                        fwrite($stream, $pipe->getData())
                    ));
                }

                if ($pipe->getData() === false || empty($pipe->getData())) {
                    fclose($stream);
                }
                break;

            // pipe in write mode has data ready for reading
            case PipeAbstract::MODE_WRITE:

                while (($buffer = fgets($stream, 1024)) !== false) {
                    $pipe->setData( $pipe->getData() . $buffer);
                }
                break;

            default:
                throw new \Exception('pipe has an unhandled/unknown pipe mode');
        }

        return true;
    }
}
