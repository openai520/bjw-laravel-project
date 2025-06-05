<?php

namespace App\Jobs;

use App\Models\ProductView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RecordProductView implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $productId;
    public $ipAddress;
    public $userAgent;
    public $referer;
    public $viewedAt;

    /**
     * Create a new job instance.
     */
    public function __construct($productId, $ipAddress, $userAgent = null, $referer = null, $viewedAt = null)
    {
        $this->productId = $productId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->referer = $referer;
        $this->viewedAt = $viewedAt ?? Carbon::now();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 检查是否为重复访问（同一IP在1小时内访问同一产品只记录一次）
            if (ProductView::isDuplicateView($this->productId, $this->ipAddress, 60)) {
                Log::debug("重复访问被过滤", [
                    'product_id' => $this->productId,
                    'ip_address' => $this->ipAddress
                ]);
                return;
            }

            // 记录访问
            ProductView::create([
                'product_id' => $this->productId,
                'ip_address' => $this->ipAddress,
                'user_agent' => $this->userAgent,
                'referer' => $this->referer,
                'viewed_at' => $this->viewedAt,
            ]);

            Log::debug("产品访问记录成功", [
                'product_id' => $this->productId,
                'ip_address' => $this->ipAddress
            ]);

        } catch (\Exception $e) {
            Log::error("记录产品访问失败", [
                'product_id' => $this->productId,
                'ip_address' => $this->ipAddress,
                'error' => $e->getMessage()
            ]);
            
            // 重新抛出异常以触发重试机制
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("产品访问统计任务失败", [
            'product_id' => $this->productId,
            'ip_address' => $this->ipAddress,
            'exception' => $exception->getMessage()
        ]);
    }
}
