<?php

declare(strict_types=1);

namespace MAAF\DevTools\Log;

/**
 * Module Log View
 * 
 * Modulonkénti log nézet.
 * 
 * @version 1.0.0
 */
final class ModuleLogView
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $moduleLogs = [];

    public function __construct(
        private readonly ?string $logPath = null
    ) {
    }

    /**
     * Add log entry for module
     * 
     * @param string $moduleName Module name
     * @param string $level Log level
     * @param string $message Log message
     * @param array<string, mixed> $context Context data
     * @return void
     */
    public function log(string $moduleName, string $level, string $message, array $context = []): void
    {
        if (!isset($this->moduleLogs[$moduleName])) {
            $this->moduleLogs[$moduleName] = [];
        }

        $this->moduleLogs[$moduleName][] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        if ($this->logPath !== null) {
            $this->writeToFile($moduleName, $level, $message, $context);
        }
    }

    /**
     * Get logs for module
     * 
     * @param string $moduleName Module name
     * @param string|null $level Log level filter (null = all levels)
     * @param int $limit Limit
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(string $moduleName, ?string $level = null, int $limit = 100): array
    {
        $logs = $this->moduleLogs[$moduleName] ?? [];

        if ($level !== null) {
            $logs = array_filter($logs, fn($log) => $log['level'] === $level);
        }

        return array_slice($logs, -$limit);
    }

    /**
     * Get all module logs
     * 
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getAllLogs(): array
    {
        return $this->moduleLogs;
    }

    /**
     * Clear logs for module
     * 
     * @param string|null $moduleName Module name (null = all modules)
     * @return void
     */
    public function clear(string $moduleName = null): void
    {
        if ($moduleName === null) {
            $this->moduleLogs = [];
        } else {
            unset($this->moduleLogs[$moduleName]);
        }
    }

    /**
     * Write log to file
     * 
     * @param string $moduleName Module name
     * @param string $level Log level
     * @param string $message Log message
     * @param array<string, mixed> $context Context data
     * @return void
     */
    private function writeToFile(string $moduleName, string $level, string $message, array $context): void
    {
        if ($this->logPath === null) {
            return;
        }

        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $filename = $this->logPath . '/' . $moduleName . '-' . date('Y-m-d') . '.log';
        $logLine = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );

        file_put_contents($filename, $logLine, FILE_APPEND);
    }
}
