<?php

namespace App\Console\Commands;

use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneVisitorLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune-visitors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune visitor logs older than one month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pruning visitor logs older than one month...');

        try {
            // 计算一个月前的日期
            $cutoffDate = Carbon::now()->subMonth();

            // 删除早于该日期的日志
            $deletedCount = VisitorLog::where('visited_at', '<', $cutoffDate)->delete();

            $this->info("Successfully pruned {$deletedCount} old visitor log entries.");
            Log::info("Visitor logs pruned: {$deletedCount} entries deleted older than {$cutoffDate->toDateString()}.");

        } catch (\Exception $e) {
            $this->error('Failed to prune visitor logs: '.$e->getMessage());
            Log::error('Error pruning visitor logs: '.$e->getMessage());
        }

        return 0; // 返回0表示成功
    }
}
