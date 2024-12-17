<?php

declare(strict_types=1);

namespace Coffreo\Challenge\Worker;

use Coffreo\Challenge\MessageQueue\Worker;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Consumes capital name from a MessageQueue;
 * Retrieves its capital data from restcountries.com API.
 * -- supposedly does something with the Capital data?
 */
class CapitalWorker implements Worker
{
    private const LOG_FILE = '/tmp/capital.log';

    private ?Process $process = null;

    public function run(): void
    {
        if (null !== $this->process && $this->process->isRunning()) {
            return;
        }

        // Creates log file if it doesn't exist
        if (!is_dir(\dirname(self::LOG_FILE))) {
            mkdir(\dirname(self::LOG_FILE), 0777, true);
        }
        if (!file_exists(self::LOG_FILE)) {
            touch(self::LOG_FILE);
        }

        // Starts capital worker in the background
        $this->process = new Process(['php', __DIR__.'/../capital.php']);
        $this->process->start(function ($type, $buffer) {
            file_put_contents(self::LOG_FILE, $buffer, \FILE_APPEND);
        });

        // Wait 0.1 seconds for worker to start
        usleep(100000);

        if (!$this->process->isRunning()) {
            throw new ProcessFailedException($this->process);
        }
    }

    public function stop(): void
    {
        if (null === $this->process || !$this->process->isRunning()) {
            return;
        }

        // Stops the worker, allowing up to 10 seconds for graceful shutdown
        $this->process->stop(10);
        $this->process = null;
    }
}
