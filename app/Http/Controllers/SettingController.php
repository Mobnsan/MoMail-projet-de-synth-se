<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::where('ownerId', Auth::id())->first();
        if (!$setting) {
            return response()->json([
                'provider' => 'console',
                'providerConfig' => [],
                'fromEmail' => ''
            ]);
        }
        return response()->json($setting);
    }

    public function providers()
    {
        $providers = [];

        if (env('SENDGRID_API_KEY')) {
            $providers[] = ['provider' => 'sendgrid', 'label' => 'SendGrid', 'configured' => true];
        }

        if (env('MAILGUN_API_KEY') && env('MAILGUN_DOMAIN')) {
            $providers[] = ['provider' => 'mailgun', 'label' => 'Mailgun', 'configured' => true];
        }

        if (env('SMTP_HOST') && env('SMTP_USER') && env('SMTP_PASS')) {
            $providers[] = ['provider' => 'smtp', 'label' => 'SMTP', 'configured' => true];
        }

        return response()->json($providers);
    }

    public function store(Request $request)
    {
        $setting = Setting::where('ownerId', Auth::id())->first();
        
        $data = [
            'provider' => $request->provider ?? 'console',
            'providerConfig' => $request->providerConfig ?? [],
            'fromEmail' => $request->fromEmail ?? '',
        ];

        if ($setting) {
            $setting->update($data);
        } else {
            $data['id'] = Str::uuid();
            $data['ownerId'] = Auth::id();
            $setting = Setting::create($data);
        }

        return response()->json($setting);
    }

    public function autoConnect(Request $request)
    {
        $provider = $request->provider;
        $user = Auth::user();
        $baseEmail = $user->email;
        $providerConfig = [];

        if ($provider === 'sendgrid') {
            if (!env('SENDGRID_API_KEY')) {
                return response()->json(['error' => 'SendGrid is not configured on the server.'], 400);
            }
            $providerConfig = ['sendgridApiKey' => env('SENDGRID_API_KEY')];
        } elseif ($provider === 'mailgun') {
            if (!env('MAILGUN_API_KEY') || !env('MAILGUN_DOMAIN')) {
                return response()->json(['error' => 'Mailgun is not configured on the server.'], 400);
            }
            $providerConfig = [
                'mailgunApiKey' => env('MAILGUN_API_KEY'),
                'mailgunDomain' => env('MAILGUN_DOMAIN'),
            ];
        } elseif ($provider === 'smtp') {
            if (!env('SMTP_HOST') || !env('SMTP_USER') || !env('SMTP_PASS')) {
                return response()->json(['error' => 'SMTP is not configured on the server.'], 400);
            }
            $providerConfig = [
                'smtpHost' => env('SMTP_HOST'),
                'smtpPort' => env('SMTP_PORT', '587'),
                'smtpUser' => env('SMTP_USER'),
                'smtpPass' => env('SMTP_PASS'),
                'smtpSecure' => env('SMTP_SECURE', false),
            ];
        } else {
            return response()->json(['error' => 'Unknown provider.'], 400);
        }

        $setting = Setting::where('ownerId', Auth::id())->first();
        
        $data = [
            'provider' => $provider,
            'providerConfig' => $providerConfig,
            'fromEmail' => $baseEmail,
        ];

        if ($setting) {
            $setting->update($data);
        } else {
            $data['id'] = Str::uuid();
            $data['ownerId'] = Auth::id();
            $setting = Setting::create($data);
        }

        return response()->json($setting);
    }
}
