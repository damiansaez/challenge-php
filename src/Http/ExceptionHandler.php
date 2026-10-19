<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use ErrorException;
use Throwable;

final class ExceptionHandler
{
    public static function register(): void
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $e): void
    {
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $error = ['code' => $status, 'message' => $e->getMessage()];

            if ($e->getDetails() !== []) {
                $error['details'] = $e->getDetails();
            }

            foreach ($e->getHeaders() as $name => $value) {
                header("{$name}: {$value}");
            }
        } else {
            error_log((string) $e);

            $status = 500;
            $error = ['code' => $status, 'message' => 'Error interno del servidor'];
        }

        JsonResponse::send(['error' => $error], $status);
    }
}