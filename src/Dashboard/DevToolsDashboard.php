<?php

declare(strict_types=1);

namespace MAAF\DevTools\Dashboard;

use MAAF\DevTools\Log\ModuleLogView;
use MAAF\DevTools\Query\QueryProfiler;
use MAAF\DevTools\Request\RequestInspector;

/**
 * DevTools Dashboard
 * 
 * DevTools dashboard összes adattal.
 * 
 * @version 1.0.0
 */
final class DevToolsDashboard
{
    public function __construct(
        private readonly RequestInspector $requestInspector,
        private readonly ModuleLogView $logView,
        private readonly QueryProfiler $queryProfiler
    ) {
    }

    /**
     * Get dashboard data
     * 
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return [
            'requests' => $this->getRequestData(),
            'logs' => $this->getLogData(),
            'queries' => $this->getQueryData(),
        ];
    }

    /**
     * Get request data
     * 
     * @return array<string, mixed>
     */
    private function getRequestData(): array
    {
        $recentEntries = $this->requestInspector->getRecentEntries(20);
        
        return [
            'total' => count($this->requestInspector->getRecentEntries()),
            'recent' => array_map(fn($entry) => $entry->toArray(), $recentEntries),
        ];
    }

    /**
     * Get log data
     * 
     * @return array<string, mixed>
     */
    private function getLogData(): array
    {
        $allLogs = $this->logView->getAllLogs();
        $moduleStats = [];

        foreach ($allLogs as $module => $logs) {
            $moduleStats[$module] = [
                'total' => count($logs),
                'by_level' => $this->countByLevel($logs),
            ];
        }

        return [
            'modules' => $moduleStats,
        ];
    }

    /**
     * Get query data
     * 
     * @return array<string, mixed>
     */
    private function getQueryData(): array
    {
        $stats = $this->queryProfiler->getStatistics();
        $slowQueries = $this->queryProfiler->getSlowQueries(100.0);

        return [
            'statistics' => $stats,
            'slow_queries' => array_map(fn($q) => $q->toArray(), $slowQueries),
        ];
    }

    /**
     * Count logs by level
     * 
     * @param array<int, array<string, mixed>> $logs Logs
     * @return array<string, int>
     */
    private function countByLevel(array $logs): array
    {
        $counts = [];

        foreach ($logs as $log) {
            $level = $log['level'] ?? 'unknown';
            $counts[$level] = ($counts[$level] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * Render dashboard HTML
     * 
     * @return string
     */
    public function render(): string
    {
        $data = $this->getData();

        ob_start();
        include __DIR__ . '/templates/dashboard.php';
        return ob_get_clean();
    }
}
