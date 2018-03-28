<?php // -->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Composer;

use Composer\Console\Application;
use Symfony\Component\Console\Input\StringInput;

/**
 * Programmatically use Composer we
 * only provide the basic functionalities
 * here e.g require, install, remove, update.
 *
 * @vendor   Cradle
 * @package  Composer
 * @author   John Doe <john@doe.com>
 * @standard PSR-4
 */
class Command 
{
    /**
     * Composer home
     * 
     * @var string $home
     */
    protected $home = null;

    /**
     * Composer parameters
     * 
     * @var string $parameters
     */
    protected $parameters = [];

    /**
     * Set composer home
     * 
     * @param string $home
     * @return $this
     */
    public function __construct($home = null)
    {
        // if home is set
        if ($home) {
            // set the home
            $this->home = $home;
        } else {
            // figure out composer home
            $this->home = __DIR__ . '/../vendor/bin/composer';
        }

        // put composer home to env
        putenv(sprintf('COMPOSER_HOME=%s', $this->home));
    }

    /**
     * Add a composer command parameter
     * 
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addParameter($key, $value = null)
    {
        // has --?
        if (strpos($key, '--') === false) {
            // prepend --
            $key = '--' . $key;
        }

        // set parameter
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Set composer home
     * 
     * @param string $home
     * @return $this
     */
    public function setComposerHome($home)
    {
        // set composer home
        $this->home = $home;

        return $this;
    }

    /**
     * Runs composer install
     * 
     * @return void
     */
    public function install()
    {
        // execute the command
        return $this->command('install', $this->parameters);
    }

    /**
     * Runs composer remove
     * 
     * @param string|array $packages
     * @return void
     */
    public function remove($packages = null)
    {
        // array of packages?
        if (is_array($packages)) {
            // join packages
            $packages = implode(' ', $packages);
        }

        // build out the command
        $command = trim(sprintf('remove %s', $packages));

        // execute the command
        return $this->command($command, $this->parameters);
    }

    /**
     * Runs composer require
     * 
     * @param string|array $packages
     * @return void
     */
    public function require($packages = null)
    {
        // array of packages?
        if (is_array($packages)) {
            // join packages
            $packages = implode(' ', $packages);
        }

        // build out the command
        $command = trim(sprintf('require %s', $packages));

        // execute the command
        return $this->command($command, $this->parameters);
    }

    /**
     * Runs composer update
     * 
     * @param string|array $packages
     * @return void
     */
    public function update($packages = null)
    {
        // array of packages?
        if (is_array($packages)) {
            // join packages
            $packages = implode(' ', $packages);
        }

        // build out the command
        $command = trim(sprintf('update %s', $packages));

        // execute the command
        return $this->command($command, $this->parameters);
    }

    /**
     * Executes a composer command
     * 
     * @param string $command
     * @param array $parameters
     * @return void
     */
    public function command($command = null, $parameters = [])
    {
        // build out the command
        $parameters = $this->buildParameters($parameters);

        // set parameters to command
        $command = sprintf('%s %s', $command, $parameters);

        // set string input
        $input = new StringInput($command);

        // initialize composer app
        $app = new Application();
        // set auto exit
        $app->setAutoExit(false);
        // run the application
        $app->run($input);
    }

    /**
     * Build out command parameters
     * 
     * @param array $parameters
     * @return $this
     */
    private function buildParameters($parameters = [])
    {
        // results
        $results = [];

        // iterate on each parameters
        foreach($parameters as $key => $value) {
            // if value is null
            if (is_null($value)) {
                // append to results
                $results[] = $key;

                continue;
            }

            // append to results
            $results[] = sprintf('%s=%s', $key, $value);
        }

        return implode(' ', $results);
    }
}