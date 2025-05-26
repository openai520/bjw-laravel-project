<?php

namespace App\Jobs;

use App\Models\VisitorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;

class ProcessGeoIpLookup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 访问日志ID
     */
    protected $visitorLogId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $visitorLogId)
    {
        $this->visitorLogId = $visitorLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 查找访问日志记录
            $visitorLog = VisitorLog::find($this->visitorLogId);
            
            if (!$visitorLog) {
                Log::warning("VisitorLog with ID {$this->visitorLogId} not found");
                return;
            }
            
            // 如果IP为空或已经有国家信息，则跳过
            if (empty($visitorLog->ip_address) || !empty($visitorLog->country) && $visitorLog->country !== 'Unknown') {
                Log::info("Skipping geo lookup for visitor log {$this->visitorLogId}: IP is empty or country already set");
                return;
            }
            
            // 使用IP地址获取国家信息
            $location = Location::get($visitorLog->ip_address);
            
            if ($location && !empty($location->countryName)) {
                // 更新国家信息
                $visitorLog->country = $location->countryName;
                $visitorLog->save();
                
                Log::info("Updated country for visitor log {$this->visitorLogId}: {$location->countryName}");
            } else {
                // 更新为未知国家
                $visitorLog->country = 'Unknown';
                $visitorLog->save();
                
                Log::info("Could not determine country for IP: {$visitorLog->ip_address}");
            }
        } catch (\Exception $e) {
            Log::error("Error processing geo IP lookup for visitor log {$this->visitorLogId}: " . $e->getMessage());
        }
    }
}
