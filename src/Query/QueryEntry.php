<?php

declare(strict_types=1);

namespace MAAF\DevTools\Query;

/**
 * Query Entry
 * 
 * Query bejegyzés profiler-hez.
 * 
 * @version 1.0.0
 */
final class QueryEntry
{
    public function __construct(
        private readonly string $sql,
        private readonly array $bindings = [],
        private readonly float $duration = 0.0,
        private readonly ?string $module = null,
        private readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Get formatted SQL with bindings
     * 
     * @return string
     */
    public function getFormattedSql(): string
    {
        $sql = $this->sql;
        
        foreach ($this->bindings as $binding) {
            $value = is_string($binding) ? "'{$binding}'" : (string)$binding;
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }

    /**
     * Convert to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'sql' => $this->sql,
            'bindings' => $this->bindings,
            'formatted_sql' => $this->getFormattedSql(),
            'duration' => $this->duration,
            'module' => $this->module,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }
}
