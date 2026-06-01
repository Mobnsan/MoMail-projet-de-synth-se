<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Template;
use App\Models\Contact;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Jobs\SendCampaignEmails;

class CampaignController extends Controller
{
    private function renderTemplate($text, $contact)
    {
        return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) use ($contact) {
            $key = $matches[1];
            return $contact[$key] ?? '';
        }, $text ?? '');
    }

    public function index()
    {
        $campaigns = Campaign::with('recipients')->where('ownerId', Auth::id())->get();
        return response()->json($campaigns);
    }

    public function store(Request $request)
    {
        $templateId = $request->templateId;
        $scheduledAt = $request->scheduledAt;
        $contactIds = $request->contactIds;

        $template = Template::where('id', $templateId)->where('ownerId', Auth::id())->first();
        if (!$template) {
            return response()->json(['error' => 'Template not found.'], 404);
        }

        $allContactsQuery = Contact::where('ownerId', Auth::id());
        if (is_array($contactIds) && count($contactIds) > 0) {
            $allContactsQuery->whereIn('id', $contactIds);
        }
        $selectedContacts = $allContactsQuery->get();

        if ($selectedContacts->isEmpty()) {
            return response()->json(['error' => 'No contacts selected for the campaign.'], 400);
        }

        $userSettings = Setting::where('ownerId', Auth::id())->first() ?? (object)[
            'provider' => 'console',
            'providerConfig' => [],
            'fromEmail' => ''
        ];

        $now = now();
        $scheduledDate = $scheduledAt ? \Carbon\Carbon::parse($scheduledAt) : $now;
        $isScheduled = $scheduledAt && $scheduledDate->gt($now);
        $campaignStatus = $isScheduled ? 'scheduled' : 'sending';

        $campaign = Campaign::create([
            'id' => Str::uuid(),
            'ownerId' => Auth::id(),
            'name' => $template->name,
            'templateId' => $template->id,
            'status' => $campaignStatus,
            'scheduledAt' => $scheduledDate,
            'recipientCount' => $selectedContacts->count(),
            'deliveredCount' => 0,
        ]);

        foreach ($selectedContacts as $c) {
            $contactData = array_merge(
                $c->extra_fields ?? [],
                ['name' => $c->name, 'email' => $c->email, 'company' => $c->company]
            );

            CampaignRecipient::create([
                'id' => Str::uuid(),
                'campaign_id' => $campaign->id,
                'email' => $c->email,
                'name' => $c->name,
                'company' => $c->company,
                'status' => $isScheduled ? 'pending' : 'queued',
                'subject' => $this->renderTemplate($template->subject, $contactData),
            ]);
        }

        if (!$isScheduled) {
            SendCampaignEmails::dispatch($campaign, $userSettings, $template);
        }

        $campaign->load('recipients');
        return response()->json($campaign);
    }
}
