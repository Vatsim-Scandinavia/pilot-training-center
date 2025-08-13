<?php

namespace App\Notifications;

use anlutro\LaravelSettings\Facade as Setting;
use App\Mail\PilotTrainingMail;
use App\Models\PilotTraining;
use App\Models\PilotTrainingInterest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PilotTrainingInterestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $training;

    private $interest;

    private $reminder;

    private $subjectPrefix = '';

    /**
     * Create a new notification instance.
     */
    public function __construct(PilotTraining $training, PilotTrainingInterest $interest, bool $reminder = false)
    {
        $this->training = $training;
        $this->interest = $interest;
        $this->reminder = $reminder;
        if ($this->reminder) {
            $this->subjectPrefix = 'Reminder: ';
        }

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $textLines = [
            'Periodically we are asking you to confirm the interest for your Pilot Training application with us.',
            'Please confirm your continued interest for your ' . $this->training->getInlineRatings() . ' training.',
            '**Deadline:** ' . $this->interest->deadline->toEuropeanDate(),
            '*If no confirmation is received within deadline, your training request will be automatically closed and your slot in the queue or training will be lost.',

        ];

        $contactMail = Setting::get('ptmEmail');
        $actionUrl = route('pilot.training.confirm.interest', ['training' => $this->training->id, 'key' => $this->interest->key]);

        return (new PilotTrainingMail($this->subjectPrefix . 'Confirm Continued Pilot Training Interest', $this->training, $textLines, $contactMail, $url1 = null, $url2 = null, $actionUrl, 'Confirm Interest', 'success'))
            ->to($this->training->user->notificationEmail, $this->training->user->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pilot_training_id' => $this->training->id,
            'key' => $this->interest->key,
            'deadline' => $this->interest->deadline,
            'reminder' => $this->reminder,
        ];
    }
}
