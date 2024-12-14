<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePayableType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:payable-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update payable_type in payments table to use fully qualified class names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Update payable_type for Invoice
        $this->info('Updating payable_type for invoices...');
        DB::table('payments')
            ->where('payable_type', 'Invoice')
            ->update(['payable_type' => 'App\Models\Invoice']);
        $this->info('Invoice updates completed.');

        // Update payable_type for Bill
        $this->info('Updating payable_type for bills...');

        
        DB::table('payments')
            ->where('payable_type', 'Bill')
            ->update(['payable_type' => 'App\Models\Bill']);
        $this->info('Bill updates completed.');

        $this->info('Payable type updates completed successfully!');
    }
}
