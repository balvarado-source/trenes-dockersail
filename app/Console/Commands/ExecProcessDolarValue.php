<?php

namespace App\Console\Commands;

use App\Models\DolarOficial;
use Exception;
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

    /**
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

            try {
                $lastDolar = DolarOficial::latest()->first();

                if ($lastDolar->value != $dato['venta']) {
                    Redis::set('dolar_oficial', $dato['venta']);
                    DolarOficial::create(['value' => $dato['venta']]);
                    Log::info('Dato API:', $dato);
                } else {
                    Log::info('Dato existente:', $dato);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                throw new Exception("Se cayó la base");
            }
        } else {
            Log::error('Error API', ['status' => $response->status()]);
        }
    }
}
