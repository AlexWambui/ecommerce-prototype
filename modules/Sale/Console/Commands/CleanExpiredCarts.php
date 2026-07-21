<?php

namespace Modules\Sale\Console\Commands;

use Illuminate\Console\Command;
use Modules\Sale\Models\Cart;
use Illuminate\Support\Facades\Log;

class CleanExpiredCarts extends Command
{
    protected $signature = 'sale:clean-carts';
    protected $description = 'Delete expired carts from the database';

    public function handle()
    {
        $this->info('Cleaning expired carts...');

        // Delete carts older than 30 days
        $deleted = Cart::where('expires_at', '<', now())
            ->orWhere(function ($query) {
                $query->whereNull('expires_at')
                    ->where('updated_at', '<', now()->subDays(30));
            })
            ->delete();

        $this->info("Deleted {$deleted} expired carts.");
        Log::info("Cleaned {$deleted} expired carts");

        return Command::SUCCESS;
    }
}