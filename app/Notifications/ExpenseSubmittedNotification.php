<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpenseSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('New Expense Submitted - '.$this->expense->expense_number)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new expense has been submitted for your review.')
            ->line('Expense Number: '.$this->expense->expense_number)
            ->line('Project: '.$this->expense->project->name)
            ->line('Amount: $'.number_format((float) $this->expense->amount, 2))
            ->line('Submitted by: '.$this->expense->submitter->name)
            ->action('Review Expense', url('/expenses/'.$this->expense->id))
            ->line('Please review and approve at your earliest convenience.')
            ->salutation('Best regards, CANZIM FinTrack Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'expense_submitted',
            'expense_id' => $this->expense->id,
            'expense_number' => $this->expense->expense_number,
            'project_name' => $this->expense->project->name,
            'amount' => $this->expense->amount,
            'submitter_name' => $this->expense->submitter->name,
            'message' => 'Expense '.$this->expense->expense_number.' submitted for review',
        ];
    }
}
