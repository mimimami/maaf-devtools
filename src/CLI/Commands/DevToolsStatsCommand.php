<?php

declare(strict_types=1);

namespace MAAF\DevTools\CLI\Commands;

use MAAF\Core\Cli\CommandInterface;
use MAAF\DevTools\Query\QueryProfiler;
use MAAF\DevTools\Request\RequestInspector;

/**
 * DevTools Stats Command
 * 
 * DevTools statisztikák megjelenítése.
 * 
 * @version 1.0.0
 */
final class DevToolsStatsCommand implements CommandInterface
{
    public function __construct(
        private readonly ?RequestInspector $requestInspector = null,
        private readonly ?QueryProfiler $queryProfiler = null
    ) {
    }

    public function getName(): string
    {
        return 'devtools:stats';
    }

    public function getDescription(): string
    {
        return 'Show DevTools statistics';
    }

    public function execute(array $args): int
    {
        echo "DevTools Statistics:\n";
        echo str_repeat("=", 80) . "\n\n";

        if ($this->requestInspector !== null) {
            $recentEntries = $this->requestInspector->getRecentEntries(10);
            echo "Recent Requests: " . count($recentEntries) . "\n";
            foreach ($recentEntries as $entry) {
                echo sprintf(
                    "  %s %s [%d] - %.2f ms\n",
                    $entry->getRequest()->getMethod(),
                    $entry->getRequest()->getPath(),
                    $entry->getResponse()?->getStatusCode() ?? 0,
                    $entry->getDuration()
                );
            }
            echo "\n";
        }

        if ($this->queryProfiler !== null) {
            $stats = $this->queryProfiler->getStatistics();
            echo "Query Statistics:\n";
            echo "  Total Queries: " . ($stats['total_queries'] ?? 0) . "\n";
            echo "  Total Duration: " . round($stats['total_duration'] ?? 0, 2) . " ms\n";
            echo "  Avg Duration: " . round($stats['avg_duration'] ?? 0, 2) . " ms\n";
            echo "  Slow Queries: " . ($stats['slow_queries'] ?? 0) . "\n";
            echo "\n";
        }

        return 0;
    }
}
