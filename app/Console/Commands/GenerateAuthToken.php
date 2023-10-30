<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateAuthToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new api client auth token to consume our api';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Generate a new long token
        $token = Str::random(60);

        //check if token alredy exist
        $count = DB::table('api_clients')->where('api_token', hash('sha256', $token))->count();
        while($count > 0){
            $token = Str::random(60);
            $count = DB::table('api_clients')->where('api_token', hash('sha256', $token))->count();
        }

        // Create a new entry with the hashed token value
        // so we don't store the token in plain text
        DB::table('api_clients')->insert([
            'api_token' => hash('sha256', $token),
        ]);

        // Spit out the token so we can use it
        $this->info($token);

        return 0;
    }
}
