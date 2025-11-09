<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;
use Monolog\LogRecord;

class JsonFormatter extends MonologJsonFormatter
{
    /**
     * Format the log record.
     */
    public function format(LogRecord $record): string
    {
        $formatted = [
            'timestamp' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
            'level' => $record->level->getName(),
            'level_value' => $record->level->value,
            'message' => $record->message,
            'channel' => $record->channel,
            'context' => $record->context,
            'extra' => array_merge($record->extra, [
                'correlation_id' => request()->header('X-Correlation-ID'),
                'request_id' => request()->id(),
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'user_agent' => request()->userAgent(),
            ]),
        ];

        // Add exception details if present
        if (isset($record->context['exception']) && $record->context['exception'] instanceof \Throwable) {
            $exception = $record->context['exception'];
            $formatted['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return $this->toJson($formatted, true) . "\n";
    }
}
