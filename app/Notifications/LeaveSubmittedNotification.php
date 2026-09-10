<?php

namespace App\Notifications;

use App\Models\Leaves;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveSubmittedNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(public Leaves $leave) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $personnel = $this->leave->personnel;

        return [
            'icon' => 'leave',
            'title' => 'New Leave Request',
            'message' => ($personnel?->full_name ?? 'A soldier').' filed a '
                .($this->leave->leaveType?->leave_name ?? 'leave').' request.',
            'url' => route('leaves.index'),
            'leave_id' => $this->leave->leave_id,
            'personnel_id' => $personnel?->personnel_id,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $personnel = $this->leave->personnel;

        return (new MailMessage)
            ->subject('New Leave Request from '.($personnel?->full_name ?? 'a soldier'))
            ->markdown('mail.notifications.leave-submitted', [
                'leave' => $this->leave,
                'soldier' => $personnel,
            ]);
    }
}
