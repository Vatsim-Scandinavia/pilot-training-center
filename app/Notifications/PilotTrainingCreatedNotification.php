<?php

namespace App\Notifications;

use anlutro\LaravelSettings\Facade as Setting;
use App\Mail\PilotTrainingMail;
use App\Models\PilotTraining;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PilotTrainingCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $training;

    private $contactMail;

    /**
     * Create a new notification instance.
     */
    public function __construct(PilotTraining $training)
    {
        $this->training = $training;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $textLines = [
            'We hereby confirm that we have received your training request for the ' . $this->training->getInlineRatings() . ' rating. Your personal callsign for training purposes is **' . $this->training->callsign->callsign . '** (SPT: Lightwings).',

            'You will now be placed in the waiting queue for training. While waiting, we expect you to begin studying flight theory. You can start by reviewing the materials available on our wiki.',

            '[Wiki](https://wiki.vatsim-scandinavia.org/shelves/pilot-training)',

            'Once a Flight Instructor becomes available, you will enter the pre-training phase. During this phase, you are required to complete and pass the theory exam. Only after successfully passing the theory exam will you be eligible to begin practical training. Please note that we cannot place you into active training without a completed and passed theory exam. Ideally, the pre-training phase should take no longer than one to two weeks.',

            'Very important: Before we can grant you access to the theory exam, you must log in to Moodle at least once.',

            '[Moodle](https://moodle.vatsim-scandinavia.org/)',

            'If you have any questions, feel free to contact one of our Flight Instructors or ask in the pilot training channel on Discord.',

            '[Discord](http://discord.vatsim-scandinavia.org/)',

            'Best regards,',
            'Pilot Training Department',
            'VATSIM Scandinavia',

        ];

        $bcc = User::allWithGroup(4)->where('setting_notify_newreq', true);
        foreach ($bcc as $key => $user) {
            if (! $user->isAdmin()) {
                $bcc->pull($key);
            }
        }
        $contactMail = Setting::get('ptmEmail');

        return (new PilotTrainingMail('New Training Request Confirmation', $this->training, $textLines, $contactMail, $url1 = null, $url2 = null))
            ->to($this->training->user->notificationEmail, $this->training->user->name)
            ->bcc($bcc->pluck('notificationEmail'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'training_id' => $this->training->id,
        ];
    }
}
