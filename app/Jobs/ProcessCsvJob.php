<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use App\Events\FileProcessingUpdate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProcessCsvJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FileUpload $fileUpload
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $this->fileUpload->update(['status' => 'processing']);
            $this->broadcastUpdate();

            // Get the file path - files are stored in private/uploads directory
            $filePath = storage_path('app/private/uploads/' . $this->fileUpload->filename);
            
            if (!file_exists($filePath)) {
                throw new Exception("File not found: " . $filePath);
            }

            // Count total records first
            $totalRecords = $this->countCsvRecords($filePath);
            $this->fileUpload->update(['total_records' => $totalRecords]);
            $this->broadcastUpdate();

            // Process CSV file
            $this->processCsvFile($filePath);

            // Mark as completed
            $this->fileUpload->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            $this->broadcastUpdate();

        } catch (Exception $e) {
            Log::error('CSV processing failed', [
                'file_upload_id' => $this->fileUpload->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->fileUpload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            $this->broadcastUpdate();
        }
    }

    /**
     * Count total records in CSV file
     */
    private function countCsvRecords(string $filePath): int
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file: " . $filePath);
        }

        $count = 0;
        fgetcsv($handle); // Skip header row
        while (fgetcsv($handle) !== false) {
            $count++;
        }
        fclose($handle);

        return $count;
    }

    /**
     * Process CSV file
     */
    private function processCsvFile(string $filePath): void
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Cannot open file: " . $filePath);
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new Exception("Invalid CSV file: no headers found");
        }

        // Clean headers
        $headers = array_map([$this, 'cleanUtf8'], $headers);

        $processedCount = 0;
        $failedCount = 0;
        $chunkSize = 100;
        $chunk = [];

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map([$this, 'cleanUtf8'], $row);
            $chunk[] = array_combine($headers, $row);

            if (count($chunk) >= $chunkSize) {
                $results = $this->processChunk($chunk);
                $processedCount += $results['processed'];
                $failedCount += $results['failed'];
                
                $this->updateProgress($processedCount, $failedCount);
                $chunk = [];
            }
        }

        // Process remaining chunk
        if (!empty($chunk)) {
            $results = $this->processChunk($chunk);
            $processedCount += $results['processed'];
            $failedCount += $results['failed'];
            
            $this->updateProgress($processedCount, $failedCount);
        }

        fclose($handle);
    }

    /**
     * Process a chunk of CSV data
     */
    private function processChunk(array $chunk): array
    {
        $processed = 0;
        $failed = 0;

        foreach ($chunk as $row) {
            try {
                DB::transaction(function () use ($row) {
                    $this->upsertProduct($row);
                });
                $processed++;
            } catch (Exception $e) {
                $failed++;
                Log::warning('Failed to process CSV row', [
                    'error' => $e->getMessage(),
                    'row' => $row
                ]);
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    /**
     * Upsert product data
     */
    private function upsertProduct(array $row): void
    {
        $uniqueKey = $row['UNIQUE_KEY'] ?? null;
        if (!$uniqueKey) {
            throw new Exception("Missing UNIQUE_KEY in row");
        }

        $productData = [
            'unique_key' => $uniqueKey,
            'product_title' => $row['PRODUCT_TITLE'] ?? null,
            'product_description' => $row['PRODUCT_DESCRIPTION'] ?? null,
            'style_number' => $row['STYLE#'] ?? null,
            'sanmar_mainframe_color' => $row['SANMAR_MAINFRAME_COLOR'] ?? null,
            'size' => $row['SIZE'] ?? null,
            'color_name' => $row['COLOR_NAME'] ?? null,
            'piece_price' => $this->parsePrice($row['PIECE_PRICE'] ?? null),
            'file_upload_id' => $this->fileUpload->id,
        ];

        Product::updateOrCreate(
            ['unique_key' => $uniqueKey],
            $productData
        );
    }

    /**
     * Parse price from string
     */
    private function parsePrice(?string $price): ?float
    {
        if (!$price) {
            return null;
        }

        // Remove any non-numeric characters except decimal point
        $cleaned = preg_replace('/[^\d.]/', '', $price);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Clean UTF-8 characters
     */
    private function cleanUtf8(string $text): string
    {
        // Remove BOM if present
        $text = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $text);
        
        // Convert to UTF-8 and remove invalid characters
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Remove or replace problematic characters
        $text = preg_replace('/[^\x20-\x7E\x{00A0}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $text);
        
        return trim($text);
    }

    /**
     * Update progress
     */
    private function updateProgress(int $processed, int $failed): void
    {
        $this->fileUpload->update([
            'processed_records' => $processed,
            'failed_records' => $failed,
        ]);
        
        $this->broadcastUpdate();
    }

    /**
     * Broadcast update via WebSocket
     */
    private function broadcastUpdate(): void
    {
        broadcast(new FileProcessingUpdate($this->fileUpload));
    }
}
