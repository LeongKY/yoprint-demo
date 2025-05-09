<?php

namespace App\Jobs;

use App\Events\CsvProcessed;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected string $filePath;
    protected string $guestId;
    protected int $uploadId;

    public function __construct(string $filePath, string $guestId, int $uploadId)
    {
        $this->filePath = $filePath;
        $this->guestId = $guestId;
        $this->uploadId = $uploadId;
    }

    public function handle(): void
    {
        Upload::where('id', $this->uploadId)->update(['status' => 'processing']);
        broadcast(new \App\Events\CsvProcessed($this->guestId, $this->filePath));
        Log::info("Processing CSV for guest: {$this->guestId}");

        $content = Storage::get($this->filePath);
        $cleanContent = preg_replace('/^\xEF\xBB\xBF/', '', $content); // strip BOM
        $cleanContent = mb_convert_encoding($cleanContent, 'UTF-8', 'UTF-8');

        $rows = array_map('str_getcsv', explode(PHP_EOL, $cleanContent));
        $header = array_map('trim', array_shift($rows));

        $csvToDbMap = [
            'UNIQUE_KEY' => 'unique_key',
            'PRODUCT_TITLE' => 'product_title',
            'PRODUCT_DESCRIPTION' => 'product_description',
            'STYLE#' => 'style_number',
            'AVAILABLE_SIZES' => 'available_sizes',
            'BRAND_LOGO_IMAGE' => 'brand_logo_image',
            'THUMBNAIL_IMAGE' => 'thumbnail_image',
            'COLOR_SWATCH_IMAGE' => 'color_swatch_image',
            'PRODUCT_IMAGE' => 'product_image',
            'SPEC_SHEET' => 'spec_sheet',
            'PRICE_TEXT' => 'price_text',
            'SUGGESTED_PRICE' => 'suggested_price',
            'CATEGORY_NAME' => 'category_name',
            'SUBCATEGORY_NAME' => 'subcategory_name',
            'COLOR_NAME' => 'color_name',
            'COLOR_SQUARE_IMAGE' => 'color_square_image',
            'COLOR_PRODUCT_IMAGE' => 'color_product_image',
            'COLOR_PRODUCT_IMAGE_THUMBNAIL' => 'color_product_image_thumbnail',
            'SIZE' => 'size',
            'QTY' => 'qty',
            'PIECE_WEIGHT' => 'piece_weight',
            'PIECE_PRICE' => 'piece_price',
            'DOZENS_PRICE' => 'dozens_price',
            'CASE_PRICE' => 'case_price',
            'PRICE_GROUP' => 'price_group',
            'CASE_SIZE' => 'case_size',
            'INVENTORY_KEY' => 'inventory_key',
            'SIZE_INDEX' => 'size_index',
            'SANMAR_MAINFRAME_COLOR' => 'sanmar_mainframe_color',
            'MILL' => 'mill',
            'PRODUCT_STATUS' => 'product_status',
            'COMPANION_STYLES' => 'companion_styles',
            'MSRP' => 'msrp',
            'MAP_PRICING' => 'map_pricing',
            'FRONT_MODEL_IMAGE_URL' => 'front_model_image_url',
            'BACK_MODEL_IMAGE' => 'back_model_image',
            'FRONT_FLAT_IMAGE' => 'front_flat_image',
            'BACK_FLAT_IMAGE' => 'back_flat_image',
            'PRODUCT_MEASUREMENTS' => 'product_measurements',
            'PMS_COLOR' => 'pms_color',
            'GTIN' => 'gtin',
        ];

        $expectedColumns = array_keys($csvToDbMap);
        $missing = array_diff($expectedColumns, $header);

        if (!empty($missing)) {
            Upload::where('id', $this->uploadId)->update([
                'status' => 'failed',
            ]);

            Log::error("CSV upload failed. Missing columns: " . implode(', ', $missing));
            broadcast(new \App\Events\CsvProcessed($this->guestId, $this->filePath));
            return;
        }

        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) continue;
            if (count($row) !== count($header)) continue;

            $data = array_combine($header, array_map('trim', $row));

            if (!isset($data['UNIQUE_KEY']) || empty($data['UNIQUE_KEY'])) continue;

            $mapped = [];
            foreach ($csvToDbMap as $csvKey => $dbKey) {
                $mapped[$dbKey] = $data[$csvKey] ?? null;
            }

            Product::updateOrCreate(
                ['unique_key' => $mapped['unique_key']],
                $mapped
            );
        }

        Upload::where('id', $this->uploadId)->update(['status' => 'completed']);
        broadcast(new \App\Events\CsvProcessed($this->guestId, $this->filePath));
        Log::info('📡 Broadcast sent for: ' . $this->guestId);
    }
}
