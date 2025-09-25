<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SuspectReviewComplete extends Notification implements ShouldQueue
{
    use Queueable;

    protected $caseNumber;
    protected $reviewedAt;

    public function __construct(string $caseNumber, $reviewedAt = null)
    {
        $this->caseNumber = $caseNumber ?: 'N/A';
        $this->reviewedAt = $reviewedAt;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Suspect Review Complete',
            'message' => "All suspects in Case #{$this->caseNumber} have been reviewed.",
            'reviewed_at' => $this->reviewedAt,
        ];
    }
}
