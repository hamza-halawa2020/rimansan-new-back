<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeFullCommandHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:fullCommandHelper {name?} {--controller} {--resource} {--request=} {--update-request} {--store-request}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dynamic combinations of controller, request, and resource for a given name.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        if ($this->option('controller')) {
            $folder = $this->ask('Specify folder for the controller (leave blank for default)', '');
            $controllerPath = $folder ? "{$folder}/{$name}Controller" : "{$name}Controller";
            $this->call('make:controller', [
                'name' => $controllerPath,
                '--api' => true,
                '--model' => $name,
            ]);
            $this->info("Controller created: {$controllerPath}.");
        }

        if ($this->option('resource')) {
            $this->call('make:resource', ['name' => "{$name}Resource"]);
            $this->info("{$name}Resource created.");
        }

        if ($requestName = $this->option('request')) {
            $this->call('make:request', ['name' => $requestName]);
            $this->info("{$requestName} created.");
        }

        if ($this->option('update-request')) {
            $this->call('make:request', ['name' => "Update{$name}Request"]);
            $this->info("Update{$name}Request created.");
        }

        if ($this->option('store-request')) {
            $this->call('make:request', ['name' => "Store{$name}Request"]);
            $this->info("Store{$name}Request created.");
        }

        $this->info("All selected files for {$name} have been created.");
    }
}
