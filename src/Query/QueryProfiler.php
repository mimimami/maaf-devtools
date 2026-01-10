<?php

declare(strict_types=1);

namespace MAAF\DevTools\Query;

/**
 * Query Profiler
 * 
 * Query profiler adatbázis lekérdezésekhez.
 * 
 * @version 1.0.0
 */
final class QueryProfiler
{
    /**
     * @var array<int, QueryEntry>
     */
    private array $queries = [];

    private bool $enabled = true;

    public function __construct(
        private readonly ?string $storagePath = null
    ) {
    }

    /**
     * Record query
     * 
     * @param string $sql SQL query
     * @param array<string, mixed> $bindings Query bindings
     * @param float $duration Duration in milliseconds
     * @param string|null $module Module name
     * @return void
     */
    public function record(string $sql, array $bindings = [], float $duration = 0.0, ?string $module = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $entry = new QueryEntry($sql, $bindings, $duration, $module);
        $this->queries[] = $entry;

        if ($this->storagePath !== null) {
            $this->saveQuery($entry);
        }
    }

    /**
     * Get queries
     * 
     * @param string|null $module Module name filter (null = all modules)
     * @param int $limit Limit
     * @return array<int, QueryEntry>
     */
    public function getQueries(?string $module = null, int $limit = 100): array
    {
        $queries = $this->queries;

        if ($module !== null) {
            $queries = array_filter($queries, fn(QueryEntry $q) => $q->getModule() === $module);
        }

        return array_slice($queries, -$limit);
    }

    /**
     * Get slow queries
     * 
     * @param float $threshold Threshold in milliseconds
     * @return array<int, QueryEntry>
     */
    public function getSlowQueries(float $threshold = 100.0): array
    {
        return array_filter(
            $this->queries,
            fn(QueryEntry $q) => $q->getDuration() > $threshold
        );
    }

    /**
     * Get query statistics
     * 
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        $totalQueries = count($this->queries);
        $totalDuration = array_sum(array_map(fn(QueryEntry $q) => $q->getDuration(), $this->queries));
        $avgDuration = $totalQueries > 0 ? $totalDuration / $totalQueries : 0;
        $slowQueries = count($this->getSlowQueries());

        return [
            'total_queries' => $totalQueries,
            'total_duration' => $totalDuration,
            'avg_duration' => $avgDuration,
            'slow_queries' => $slowQueries,
        ];
    }

    /**
     * Enable profiler
     * 
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable profiler
     * 
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Save query to storage
     * 
     * @param QueryEntry $entry Query entry
     * @return void
     */
    private function saveQuery(QueryEntry $entry): void
    {
        if ($this->storagePath === null) {
            return;
        }

        $storageDir = dirname($this->storagePath);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $filename = $this->storagePath . '/queries-' . date('Y-m-d') . '.json';
        $queries = [];

        if (file_exists($filename)) {
            $queries = json_decode(file_get_contents($filename), true) ?? [];
        }

        $queries[] = $entry->toArray();
        file_put_contents($filename, json_encode($queries, JSON_PRETTY_PRINT));
    }

    /**
     * Clear queries
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->queries = [];
    }
}
