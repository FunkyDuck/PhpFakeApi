<?php
namespace Logger;

class JsonLogger {
    private string $logDir;
    private string $filePrefix;

    public function __construct(string $logDir = __DIR__ . '../../logs', string $filePrefix = 'app') {
        $this->logDir = $logDir;
        $this->filePrefix = $filePrefix;

        if(!is_dir($this->logDir)) {
            mkdir($this->logDir, 0775, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void {
        $date = date('Y-m-d');
        $timestamp = date('c'); // ISO 8601 date
        $filename = "{$this->logDir}/{$this->filePrefix}-{$date}.log";

        $entry = [
            "timestamp" => $timestamp,
            "level" => strtolower($level),
            "message" => $message,
            "context" => $context
        ];

        file_put_contents($filename, json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    // Helpers
    public function debug(string $message, array $context = []): void { $this->log("debug", $message, $context); }
    public function info(string $message, array $context = []): void { $this->log("info", $message, $context); }
    public function notice(string $message, array $context = []): void { $this->log("notice", $message, $context); }
    public function warning(string $message, array $context = []): void { $this->log("warning", $message, $context); }
    public function error(string $message, array $context = []): void { $this->log("error", $message, $context); }
    public function critical(string $message, array $context = []): void { $this->log("critical", $message, $context); }
}