<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveExpiredDataTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired data tokens (data_tokens table in database)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // for postgres
        DB::delete(
            'DELETE
             FROM data_tokens
             WHERE expires_at < extract(epoch from now())'
        );

        // for mysql
        // DB::delete(
        //     'DELETE
        //      FROM data_tokens
        //      WHERE expires_at < unix_timestamp()'
        // );
    }
}
