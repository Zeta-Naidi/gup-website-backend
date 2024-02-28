<?php

namespace App\Logging;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\Logging\PsrLogger;
use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Handler\HandlerInterface;

class CreateGoogleCloudLogger
{
    public function __invoke()
    {
        $logging = new LoggingClient([
            'projectId' => config('logging.googleProjectId') //TODO SHOULD BE IN ENV
        ]);

        $logger = $logging->psrLogger(config('app.name'), [
            'batchEnabled' => true,
        ]);

        return new Logger(
            config('app.name'),
            [
                new class ($logger) implements HandlerInterface {
                    public function __construct(
                        public PsrLogger $logger
                    )
                    {
                    }

                    public function isHandling(LogRecord $record): bool
                    {
                        return true;
                    }

                    public function handle(LogRecord $record): bool
                    {
                        $this->logger->log(
                            $record->level->name,
                            $record->message,
                            $record->context
                        );

                        return true;
                    }

                    public function handleBatch(array $records): void
                    {
                        foreach ($records as $record) {
                            $this->handle($record);
                        }
                    }

                    public function close(): void
                    {
                    }
                },
            ]
        );
    }
}
