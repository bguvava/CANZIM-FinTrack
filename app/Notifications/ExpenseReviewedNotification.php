<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseReviewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Expense $expense) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Ready for Approval - '.$this->expense->expense_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('An expense has been reviewed and is ready for your final approval.')
            ->line('Expense Number: '.$this->expense->expense_number)
            ->line('Project: '.$this->expense->project->name)
            ->line('Amount: $'.number_format((float) $this->expense->amount, 2))
            ->line('Reviewed by: '.$this->expense->reviewer->name)
            ->action('Approve Expense', url('/expenses/'.$this->expense->id))
            ->salutation('Best regards, CANZIM FinTrack Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'expense_reviewed',
            'expense_id' => $this->expense->id,
            'expense_number' => $this->expense->expense_number,
            'project_name' => $this->expense->project->name,
            'amount' => $this->expense->amount,
            'reviewer_name' => $this->expense->reviewer->name,
            'message' => 'Expense '.$this->expense->expense_number.' ready for approval',
        ];
    }
}
