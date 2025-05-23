<?php

namespace App\Console\Commands;

use anlutro\LaravelSettings\Facade as Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for Pilot Training Center updates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $currentVersion = 'v' . config('app.version');
        $releasedData = Http::get('https://api.github.com/repos/Vatsim-Scandinavia/pilot-training-center/releases')->json();

        if (isset($releasedData) && isset($releasedData[0]) && isset($releasedData[0]['name'])) {
            $releasedVersion = $releasedData[0]['name'];

            if ($currentVersion != $releasedVersion) {
                $this->info("There's a new version of Pilot Training Center available! Please update to $releasedVersion.");
                Setting::set('_updateAvailable', $releasedVersion);
            } else {
                $this->info('Pilot Training Center is up to date.');
                Setting::forget('_updateAvailable');
            }

            Setting::save();

        }

        return Command::SUCCESS;
    }
}
