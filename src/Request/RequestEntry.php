<?php

declare(strict_types=1);

namespace MAAF\DevTools\Request;

use MAAF\Core\Http\Request;
use MAAF\Core\Http\Response;

/**
 * Request Entry
 * 
 * Request bejegyzés Telescope szerű inspector-hoz.
 * 
 * @version 1.0.0
 */
final class RequestEntry
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $queries = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $logs = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $events = [];

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(
        private readonly string $id,
        private readonly Request $request,
        private readonly ?Response $response = null,
        private readonly float $duration = 0.0,
        private readonly int $memoryUsage = 0,
        private readonly \DateTimeImmutable $timestamp = new \DateTimeImmutable()
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getMemoryUsage(): int
    {
        return $this->memoryUsage;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Add query
     * 
     * @param array<string, mixed> $query Query data
     * @return void
     */
    public function addQuery(array $query): void
    {
        $this->queries[] = $query;
    }

    /**
     * Get queries
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Add log
     * 
     * @param array<string, mixed> $log Log data
     * @return void
     */
    public function addLog(array $log): void
    {
        $this->logs[] = $log;
    }

    /**
     * Get logs
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Add event
     * 
     * @param array<string, mixed> $event Event data
     * @return void
     */
    public function addEvent(array $event): void
    {
        $this->events[] = $event;
    }

    /**
     * Get events
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Set data
     * 
     * @param string $key Key
     * @param mixed $value Value
     * @return void
     */
    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Get data
     * 
     * @param string|null $key Key (null = all data)
     * @return mixed
     */
    public function getData(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    /**
     * Convert to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'method' => $this->request->getMethod(),
            'path' => $this->request->getPath(),
            'status_code' => $this->response?->getStatusCode(),
            'duration' => $this->duration,
            'memory_usage' => $this->memoryUsage,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'queries' => $this->queries,
            'logs' => $this->logs,
            'events' => $this->events,
            'data' => $this->data,
        ];
    }
}
