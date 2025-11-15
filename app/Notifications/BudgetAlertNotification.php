<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Budget $budget,
        public string $alertLevel,
        public float $utilizationPercentage
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
        $subject = $this->getSubject();
        $message = $this->getMessage();

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($message)
            ->line('Project: '.$project->name)
            ->line('Budget Total: $'.number_format($this->budget->total_allocated, 2))
            ->line('Amount Spent: $'.number_format($this->budget->total_spent, 2))
            ->line('Utilization: '.$this->utilizationPercentage.'%');

        if ($this->alertLevel === 'critical') {
            $mail->line('⚠️ URGENT: Budget has been exceeded! Immediate action required.');
        } elseif ($this->alertLevel === 'warning') {
            $mail->line('⚠️ WARNING: Budget is approaching its limit. Please monitor spending.');
        }

        return $mail
            ->action('View Budget Details', url('/budgets/'.$this->budget->id))
            ->line('Please review and take appropriate action.')
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
            'type' => 'budget_alert',
            'alert_level' => $this->alertLevel,
            'budget_id' => $this->budget->id,
            'project_id' => $this->budget->project_id,
            'project_name' => $this->budget->project->name,
            'budget_total' => $this->budget->total_allocated,
            'amount_spent' => $this->budget->total_spent,
            'utilization_percentage' => $this->utilizationPercentage,
            'message' => $this->getMessage(),
        ];
    }

    /**
     * Get the subject line based on alert level.
     */
    private function getSubject(): string
    {
        return match ($this->alertLevel) {
            'critical' => '🚨 CRITICAL: Budget Exceeded - '.$this->budget->project->name,
            'warning' => '⚠️ WARNING: Budget Alert - '.$this->budget->project->name,
            default => 'Budget Alert - '.$this->budget->project->name,
        };
    }

    /**
     * Get the message based on alert level.
     */
    private function getMessage(): string
    {
        return match ($this->alertLevel) {
            'critical' => 'Budget utilization has exceeded 100% for project "'.$this->budget->project->name.'".',
            'warning' => 'Budget utilization has reached '.$this->utilizationPercentage.'% for project "'.$this->budget->project->name.'".',
            default => 'Budget alert for project "'.$this->budget->project->name.'".',
        };
    }
}
