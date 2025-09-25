<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProgressApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $progress;
    protected $status;
    protected $recommendations;
    protected $caseNumber;

    /**
     * Create a new notification instance.
     *
     * @param  object  $progress
     * @param  bool  $approved
     * @param  string|null  $recommendations
     */
    public function __construct($progress, $approved, $recommendations = null)
    {
        $this->progress = $progress;
        $this->status = $approved ? 'approved' : 'rejected';
        $this->recommendations = $recommendations;

        // Load case number from DB (if not already passed in progress)
        $case = \DB::table('cases')->where('case_id', $progress->case_id)->first();
        $this->caseNumber = $case ? $case->case_number : 'Unknown';
    }

    /**
     * Define notification channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Store data in the database.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => "Progress Update {$this->status}",
            'message' => "Your progress update for Case #{$this->caseNumber} has been {$this->status}.",
            'case_number' => $this->caseNumber,
            'progress_id' => $this->progress->progress_id,
            'recommendations' => $this->recommendations ?: 'No recommendations provided.',
        ];
    }
}
