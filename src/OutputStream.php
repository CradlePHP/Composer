<?php // -->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Composer;

use Symfony\Component\Console\Output\Output;

/**
 * Custom Output Stream Handler
 * 
 * This class is based off of:
 * https://gist.github.com/odan/3f5b2748f516398e93cb36716d38b6b5
 * 
 * @vendor   Cradle
 * @package  Composer
 * @author   John Doe <john@doe.com>
 * @standard PSR-4
 */
class OutputStream extends Output
{
    /**
     * Custom output handler
     * 
     * @var callable|null $outputHandler
     */
    protected $outputHandler = null;
    
    /**
     * Set Output Options
     * 
     * @param int $verbosity
     * @param bool $decorated
     * @param OutputFormatterInterface $formatter
     * 
     * @return $this
     */
    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = false, OutputFormatterInterface $formatter = null)
    {
        // call the parent output interface
        parent::__construct($verbosity, $decorated, $formatter);

        // tell php to automatically flush after every output
        $this->disableOutputBuffer();
    }

    /**
     * Tell's php to automatically
     * flush the buffer every output
     * 
     * @return void
     */
    protected function disableOutputBuffer()
    {
        // turn off output buffering
        ini_set('output_buffering', 'off');

        // implicitly flush the buffer
        ini_set('implicit_flush', true);
        ob_implicit_flush(true);


        // clear, and turn off output buffering
        while (ob_get_level() > 0) {
            // get the current level
            $level = ob_get_level();
            // end and clean the buffer
            ob_end_clean();

            // if the current level has not chaged, abort
            if (ob_get_level() == $level) {
                break;
            }
        }

        // disable apache specific compression / buffering
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
            apache_setenv('dont-vary', '1');
        }
    }

    /**
     * Set output handler
     * 
     * @param callable $handler
     * 
     * @return $this
     */
    public function setOutputHandler(callable $handler)
    {
        // set output handler
        $this->outputHandler = $handler;

        return $this;
    }

    /**
     * Writes the output with new line
     * 
     * @param string $message
     * 
     * @return void
     */
    public function writeln($message, $options = 0)
    {
        // write the message
        $this->write($message, true, $options);
    }
    
    /**
     * Writes the message
     * 
     * @param string $message
     * @param bool $newline
     * @param int $options
     * 
     * @return void
     */
    public function write($message, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        // call the custom writer
        $this->doWrite($message, $newline);
    }

    /**
     * Default output handler
     * 
     * @param string $message
     * @param bool $newline
     * 
     * @return void
     */
    protected function doWrite($message, $newline)
    {
        // if custom handler is set
        if (is_callable($this->outputHandler)) {
            // merge parameters
            $parameters = [$message, $newline];

            // call the output handler
            call_user_func_array($this->outputHandler, $parameters);
        } else {
            // print the message
            print $message;

            // newline?
            if ($newline) {
                print PHP_EOL;
            }
        }

        // if buffer is not empty
        if (ob_get_length()) {
            // flush it
            ob_flush();
            flush();
        }
    }
}