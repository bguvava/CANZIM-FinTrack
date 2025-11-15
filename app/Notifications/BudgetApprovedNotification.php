<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Budget $budget,
        public ?string $notes = null
    ) {
        //
    }

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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $project = $this->budget->project;

        return (new MailMessage)
            ->subject('Budget Approved - '.$project->name)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your budget for project "'.$project->name.'" has been approved.')
            ->line('Budget Total: $'.number_format($this->budget->total_allocated, 2))
            ->when($this->notes, function ($mail) {
                return $mail->line('Approval Notes: '.$this->notes);
            })
            ->action('View Budget', url('/budgets/'.$this->budget->id))
            ->line('You can now proceed with budget execution.')
            ->salutation('Best regards, CANZIM FinTrack Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'budget_approved',
            'budget_id' => $this->budget->id,
            'project_id' => $this->budget->project_id,
            'project_name' => $this->budget->project->name,
            'budget_total' => $this->budget->total_allocated,
            'notes' => $this->notes,
            'message' => 'Budget for "'.$this->budget->project->name.'" has been approved',
        ];
    }
}
