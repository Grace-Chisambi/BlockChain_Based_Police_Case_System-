<?php

namespace App\Notifications;

use App\Models\Evidence;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class EvidenceReviewed extends Notification implements ShouldQueue
{
    use Queueable;

    public $evidence;

    public function __construct(Evidence $evidence)
    {
        $this->evidence = $evidence;
    }

    /**
     * Channels: database (add 'mail' if needed)
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * What’s stored in the database
     */
  public function toArray($notifiable)
{
    return [
        'title' => 'Evidence Reviewed',
        'message' => "Your evidence for Case{$this->evidence->case_number} has been {$this->evidence->review_status}.",
        'status' => $this->evidence->review_status,
        'comment' => $this->evidence->review_comment,
        'reviewed_at' => $this->evidence->reviewed_at,
    ];
}

}
