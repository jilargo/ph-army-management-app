<?php

namespace App\Notifications;

use App\Models\Personnel;
use App\Models\Task;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Task $task,
        public Personnel $personnel,
        public bool $mentioned = false,
    ) {}

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
        return [
            'icon' => 'task',
            'title' => $this->mentioned ? 'You were mentioned in a task' : 'New Task Assigned',
            'message' => rtrim($this->task->title).($this->mentioned ? ' mentions you.' : ' has been assigned to you.'),
            'url' => route('tasks'),
            'task_id' => $this->task->task_id,
            'personnel_id' => $this->personnel->personnel_id,
            'mentioned' => $this->mentioned,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->mentioned ? 'You were mentioned in a task' : 'New Task Assigned')
            ->markdown('mail.notifications.task-assigned', [
                'task' => $this->task,
                'personnel' => $this->personnel,
                'mentioned' => $this->mentioned,
            ]);
    }
}
