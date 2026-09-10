<?php

namespace App\Notifications;

use App\Models\Leaves;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
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
        $status = ucfirst($this->leave->status);

        return [
            'icon' => 'leave',
            'title' => "Leave Request {$status}",
            'message' => 'Your '.($this->leave->leaveType?->leave_name ?? 'leave').' request for '
                .optional($this->leave->start_date)->format('M j, Y').' - '
                .optional($this->leave->end_date)->format('M j, Y')." has been {$this->leave->status}.",
            'url' => route('user-dashboard'),
            'leave_id' => $this->leave->leave_id,
            'personnel_id' => $this->leave->personnel_id,
            'status' => $this->leave->status,
            'remarks' => $this->leave->remarks,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Leave Request {$this->leave->status}")
            ->markdown('mail.notifications.leave-status', [
                'leave' => $this->leave,
            ]);
    }
}
