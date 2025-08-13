<?php

namespace App\Console\Commands;

use App\Http\Controllers\PilotTrainingActivityController;
use App\Models\PilotTraining;
use App\Models\PilotTrainingInterest;
use App\Notifications\PilotTrainingClosedNotification;
use App\Notifications\PilotTrainingInterestNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPilotTrainingInterestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:traininginterests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out notifications to users regarding their continued interest.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $trainings = PilotTraining::where([['status', '>=', 0], ['status', '<=', 1], ['created_at', '<=', Carbon::now()->subDays(30)]])->get();

        foreach ($trainings as $training) {
            $lastInterestRequest = PilotTrainingInterest::where('pilot_training_id', $training->id)->orderBy('created_at')->get()->last();

            if ($lastInterestRequest == null) {
                // Notifcation not sent previously, send a new key and store it
                $key = sha1($training->id . now()->format('Ymd_His') . rand(0, 9999));

                $interest = PilotTrainingInterest::create([
                    'pilot_training_id' => $training->id,
                    'key' => $key,
                    'deadline' => now()->addDays(14),
                ]);

                $training->user->notify(new PilotTrainingInterestNotification($training, $interest));
            } else {
                $requestDeadline = $lastInterestRequest->deadline;
                $requestConfirmed = $lastInterestRequest->confirmed_at;
                $requestUpdated = $lastInterestRequest->updated_at;

                if ($requestDeadline->diffInMinutes(now(), false) >= 0 && $requestConfirmed == null && $lastInterestRequest->expired == false) {
                    $this->info('Closing training ' . $training->id);
                    $oldStatus = $training->status;

                    $training->updateStatus(-4, true);
                    $training->closed_reason = 'Continued training interest was not confirmed within deadline.';
                    $training->save();
                    $training->user->notify(new PilotTrainingClosedNotification($training, -4, 'Continued training interest was not confirmed within deadline.'));
                    PilotTrainingActivityController::create($training->id, 'STATUS', -4, $oldStatus, null, 'Continued training interest was not confirmed within deadline.');
                } elseif ($requestDeadline->diffInDays(now(), true) == 6 && $requestUpdated->diffInDays(now(), true) != 0 && $lastInterestRequest->expired == false && $requestConfirmed == null) {
                    $this->info('Reminding training ' . $training - id);

                    $lastInterestRequest->updated_at = now();
                    $lastInterestRequest->save();

                    $training->user->notify(new PilotTrainingInterestNotification($training, $lastInterestRequest, true));
                } elseif ($lastInterestRequest->created_at->diffInDays(now(), true) >= 30 && $lastInterestRequest->expired == true) {
                    $key = sha1($training->id . now()->format('Ymd_His') . rand(0, 9999));

                    $interest = PilotTrainingInterest::create([
                        'pilot_training_id' => $training->id,
                        'key' => $key,
                        'deadline' => now()->addDays(14),
                    ]);

                    $training->user->notify(new PilotTrainingInterestNotification($training, $interest));
                }
            }
        }

        $this->info('Training interests have been updated and followed up.');
    }
}
