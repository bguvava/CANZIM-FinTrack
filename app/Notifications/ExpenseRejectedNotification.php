<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Expense $expense,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Expense Rejected - '.$this->expense->expense_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your expense has been rejected.')
            ->line('Expense Number: '.$this->expense->expense_number)
            ->line('Project: '.$this->expense->project->name)
            ->line('Amount: $'.number_format((float) $this->expense->amount, 2))
            ->line('Rejection Reason: '.$this->reason)
            ->action('Edit & Resubmit', url('/expenses/'.$this->expense->id.'/edit'))
            ->salutation('Best regards, CANZIM FinTrack Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'expense_rejected',
            'expense_id' => $this->expense->id,
            'expense_number' => $this->expense->expense_number,
            'project_name' => $this->expense->project->name,
            'amount' => $this->expense->amount,
            'rejection_reason' => $this->reason,
            'message' => 'Expense '.$this->expense->expense_number.' has been rejected',
        ];
    }
}
