<?php

namespace App\Console\Commands;

use App\Models\DolarOficial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ExecProcessDolarValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:exec-process-dolar-value';

    /**sai
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get dolar oficial value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('https://dolarapi.com/v1/dolares/oficial');

        if ($response->successful()) {
            $dato = $response->json();
            $dolarValueExists = DolarOficial::where('value', $dato['venta'])->exists();

            if (!$dolarValueExists) {
                Redis::set('dolar_oficial', $dato['venta']);
                DolarOficial::create(['value' => $dato['venta']]);
                Log::info('Dato API:', $dato);
            } else {
                Log::info('Dato existente:', $dato);
            }
        } else {
            Log::error('Error API', ['status' => $response->status()]);
        }
    }
}
