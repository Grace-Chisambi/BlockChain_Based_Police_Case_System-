<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PoliceCase;

class CaseAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $caseNumber;

    public function __construct($caseNumber)
    {
        $this->caseNumber = $caseNumber ?: 'N/A';
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'New Case Assigned',
            'message' => "You’ve been assigned to Case {$this->caseNumber}.",
        ];
    }
}
