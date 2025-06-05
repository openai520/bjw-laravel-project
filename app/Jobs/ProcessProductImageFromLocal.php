<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Throwable;

class ProcessProductImageFromLocal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大尝试次数
     */
    public $tries = 2;

    /**
     * 超时时间（秒）
     */
    public $timeout = 300;

    /**
     * 产品ID
     */
    protected $productId;

    /**
     * 本地临时文件路径数组
     */
    protected $localTempFilePaths;

    /**
     * 创建一个新的任务实例
     *
     * @param int $productId 产品ID
     * @param array $localTempFilePaths 本地临时文件路径数组
     * @return void
     */
    public function __construct(int $productId, array $localTempFilePaths)
    {
        $this->productId = $productId;
        $this->localTempFilePaths = $localTempFilePaths;
        Log::info("[JOB_INIT] Job created for PID: {$this->productId}.");
    }

    /**
     * Execute the job.
     * Dependency Injection for ImageService added.
     * @param ImageService $imageService Injected ImageService instance
     * @return void
     */
    public function handle(ImageService $imageService)
    {
        Log::info("[JOB_START] Processing images for Product ID: {$this->productId}");

        if (empty($this->localTempFilePaths)) {
            Log::warning("[JOB_WARN] No temp paths provided for Product ID: {$this->productId}. Skipping.");
            return;
        }

        $isFirstImage = true;

        foreach ($this->localTempFilePaths as $index => $tempPath) {
            Log::info("[JOB_LOOP] Processing path #{$index}: {$tempPath} for Product ID: {$this->productId}");

            if (!file_exists($tempPath)) {
                Log::error("[JOB_ERROR] Temp file not found: {$tempPath} for Product ID: {$this->productId}");
                continue;
            }

            try {
                $originalName = basename($tempPath);
                $mimeType = @mime_content_type($tempPath) ?: 'application/octet-stream';
                $fileSize = @filesize($tempPath);
                if ($fileSize === false) {
                     Log::error("[JOB_ERROR] Could not get filesize for temp file: {$tempPath} for Product ID: {$this->productId}");
                     continue;
                }
                $uploadedFile = new UploadedFile($tempPath, $originalName, $mimeType, UPLOAD_ERR_OK, true);
                if (!$uploadedFile->isValid()) {
                    Log::error("[JOB_ERROR] UploadedFile created from temp path is invalid: {$tempPath}. Product ID: {$this->productId}");
                    continue;
                }

                Log::info("[JOB_PROCESS] Calling ImageService->saveOptimizedImage for WebP conversion of {$originalName}, Product ID: {$this->productId}");
                $imagePaths = $imageService->saveOptimizedImage(
                    $uploadedFile,
                    'products', 
                    true, // 创建缩略图
                    true, // 调整尺寸
                    'webp' // 转换为WebP格式
                );
                
                if (empty($imagePaths) || !isset($imagePaths['main']) || !isset($imagePaths['thumbnail'])) {
                     Log::error("[JOB_ERROR] ImageService returned empty or invalid paths: " . json_encode($imagePaths) . " for Product ID: {$this->productId}, Temp file: {$tempPath}");
                    continue; 
                }
                Log::info("[JOB_PROCESS] ImageService returned: " . json_encode($imagePaths) . " for Product ID: {$this->productId}");
                
                $mainPathToCheck = $imagePaths['main'];
                $thumbPathToCheck = $imagePaths['thumbnail'];
                $mainExists = Storage::disk('public')->exists($mainPathToCheck);
                $thumbExists = Storage::disk('public')->exists($thumbPathToCheck);
                Log::info("[JOB_DEBUG] Checking existence after save. Main exists: " . ($mainExists ? 'yes' : 'no') . " at '{$mainPathToCheck}'. Thumb exists: " . ($thumbExists ? 'yes' : 'no') . " at '{$thumbPathToCheck}'. Product ID: {$this->productId}");
                if (!$mainExists || !$thumbExists) {
                     Log::error("[JOB_ERROR] File(s) not found in public storage after ImageService call. Paths: " . json_encode($imagePaths) . ". Product ID: {$this->productId}");
                    continue;
                }

                Log::info("[JOB_SAVE_ATTEMPT] Attempting to save ProductImage record for Product ID: {$this->productId}, Image Path: {$imagePaths['main']}, Is Main: " . ($isFirstImage ? 'Yes' : 'No') );
                $productImage = new ProductImage();
                $productImage->product_id = $this->productId;
                $productImage->image_path = $imagePaths['main'];
                $productImage->thumbnail_path = $imagePaths['thumbnail'];
                $productImage->is_main = $isFirstImage;

                if ($productImage->save()) {
                    Log::info("[JOB_SAVE_SUCCESS] Saved ProductImage record. ID: {$productImage->id}, ProductID: {$this->productId}, IsMain: " . ($isFirstImage ? 'Yes' : 'No'));
                    $isFirstImage = false;
                } else {
                    Log::error("[JOB_SAVE_FAILURE] Failed to save ProductImage record for Product ID: {$this->productId}, Image Path: {$imagePaths['main']}");
                }

            } catch (Throwable $e) {
                 Log::error("[JOB_EXCEPTION] Exception occurred while processing {$tempPath} for Product ID: {$this->productId}. Error: " . $e->getMessage() . "\nStack Trace Snippet: " . substr($e->getTraceAsString(), 0, 500));
            }
        }

         Log::info("[JOB_END] Finished processing all images for Product ID: {$this->productId}");
    }

    /**
     * Handle a job failure.
     *
     * @param  Throwable  $exception // Use Throwable for broader catch
     * @return void
     */
     public function failed(Throwable $exception)
     {
         // Log the failure with more details
         Log::critical("[JOB_FAILED] Job for Product ID: {$this->productId} has failed permanently. Error: {$exception->getMessage()}\nFile: {$exception->getFile()}:{$exception->getLine()}\nStack Trace: {$exception->getTraceAsString()}");
     }
}