<?php

declare(strict_types=1);

namespace MAAF\DevTools\Request;

use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;

/**
 * Request Inspector
 * 
 * Request inspector Telescope szerű funkcionalitással.
 * 
 * @version 1.0.0
 */
final class RequestInspector
{
    /**
     * @var array<string, RequestEntry>
     */
    private array $entries = [];

    private bool $enabled = true;

    public function __construct(
        private readonly ?string $storagePath = null
    ) {
    }

    /**
     * Record request
     * 
     * @param Request $request Request instance
     * @param Response|null $response Response instance
     * @param float $duration Duration in milliseconds
     * @param int $memoryUsage Memory usage in bytes
     * @return RequestEntry
     */
    public function record(Request $request, ?Response $response = null, float $duration = 0.0, int $memoryUsage = 0): RequestEntry
    {
        if (!$this->enabled) {
            return new RequestEntry(uniqid(), $request, $response, $duration, $memoryUsage);
        }

        $id = uniqid('req_', true);
        $entry = new RequestEntry($id, $request, $response, $duration, $memoryUsage);
        
        $this->entries[$id] = $entry;

        if ($this->storagePath !== null) {
            $this->saveEntry($entry);
        }

        return $entry;
    }

    /**
     * Get entry
     * 
     * @param string $id Entry ID
     * @return RequestEntry|null
     */
    public function getEntry(string $id): ?RequestEntry
    {
        return $this->entries[$id] ?? null;
    }

    /**
     * Get recent entries
     * 
     * @param int $limit Limit
     * @return array<int, RequestEntry>
     */
    public function getRecentEntries(int $limit = 50): array
    {
        $entries = array_values($this->entries);
        usort($entries, fn($a, $b) => $b->getTimestamp() <=> $a->getTimestamp());
        return array_slice($entries, 0, $limit);
    }

    /**
     * Get entries by path
     * 
     * @param string $path Path pattern
     * @return array<int, RequestEntry>
     */
    public function getEntriesByPath(string $path): array
    {
        return array_filter(
            $this->entries,
            fn(RequestEntry $entry) => str_contains($entry->getRequest()->getPath(), $path)
        );
    }

    /**
     * Enable inspector
     * 
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable inspector
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
     * Save entry to storage
     * 
     * @param RequestEntry $entry Entry instance
     * @return void
     */
    private function saveEntry(RequestEntry $entry): void
    {
        if ($this->storagePath === null) {
            return;
        }

        $storageDir = dirname($this->storagePath);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $filename = $this->storagePath . '/requests-' . date('Y-m-d') . '.json';
        $entries = [];

        if (file_exists($filename)) {
            $entries = json_decode(file_get_contents($filename), true) ?? [];
        }

        $entries[] = $entry->toArray();
        file_put_contents($filename, json_encode($entries, JSON_PRETTY_PRINT));
    }

    /**
     * Clear entries
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->entries = [];
    }
}
