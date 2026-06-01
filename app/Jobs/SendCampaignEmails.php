<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\EmailService;

class SendCampaignEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;
    protected $userSettings;
    protected $template;
    protected $batchSize = 5;
    protected $delayMs = 1000;

    public function __construct(Campaign $campaign, $userSettings, $template)
    {
        $this->campaign = $campaign;
        $this->userSettings = $userSettings;
        $this->template = $template;
    }

    private function renderTemplate($text, $contact)
    {
        return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) use ($contact) {
            $key = $matches[1];
            return $contact[$key] ?? '';
        }, $text ?? '');
    }

    public function handle(): void
    {
        $recipients = CampaignRecipient::where('campaign_id', $this->campaign->id)->get();
        $deliveredCount = 0;

        foreach ($recipients->chunk($this->batchSize) as $batch) {
            foreach ($batch as $recipient) {
                // Prepare contact array for template rendering
                $contactData = [
                    'name' => $recipient->name,
                    'email' => $recipient->email,
                    'company' => $recipient->company,
                ];

                $message = [
                    'to' => $recipient->email,
                    'from' => $this->template->senderEmail ?: ($this->userSettings->fromEmail ?: env('EMAIL_FROM', 'no-reply@mona-email.app')),
                    'subject' => $this->renderTemplate($this->template->subject, $contactData),
                    'text' => $this->renderTemplate($this->template->body, $contactData),
                    'html' => nl2br($this->renderTemplate($this->template->body, $contactData)),
                ];

                try {
                    EmailService::sendEmail($message, [
                        'provider' => $this->userSettings->provider,
                        'providerConfig' => $this->userSettings->providerConfig,
                    ]);

                    $recipient->status = ($this->userSettings->provider === 'console' || !$this->userSettings->provider) ? 'simulated' : 'sent';
                    if ($recipient->status === 'sent') {
                        $deliveredCount++;
                    }
                } catch (\Exception $e) {
                    \Log::error("[Queue Error] {$recipient->email}: " . $e->getMessage());
                    $recipient->status = 'failed';
                    $recipient->error = $e->getMessage();
                }

                $recipient->save();
            }

            // Simulate delay between batches if not the last batch
            usleep($this->delayMs * 1000);
        }

        $this->campaign->deliveredCount += $deliveredCount;
        $this->campaign->status = 'completed';
        $this->campaign->save();
    }
}
