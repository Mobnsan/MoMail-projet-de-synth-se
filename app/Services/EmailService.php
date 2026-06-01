<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailService
{
    public static function sendEmail($message, $config = [])
    {
        if (empty($message['to']) || empty($message['subject']) || empty($message['text'])) {
            throw new \Exception('Email message is missing required fields.');
        }

        $activeProvider = $config['provider'] ?? env('EMAIL_PROVIDER', 'console');
        $providerConfig = $config['providerConfig'] ?? [];
        $fromAddress = $message['from'] ?? env('EMAIL_FROM', 'no-reply@mona-email.app');

        if ($activeProvider === 'smtp') {
            return self::sendWithSmtp($message, $providerConfig, $fromAddress);
        }

        if ($activeProvider === 'sendgrid') {
            return self::sendWithSendGrid($message, $providerConfig, $fromAddress);
        }

        if ($activeProvider === 'mailgun') {
            return self::sendWithMailgun($message, $providerConfig, $fromAddress);
        }

        // Default: simulated send
        \Log::info('Email simulated send', ['to' => $message['to'], 'subject' => $message['subject']]);
        return ['accepted' => [$message['to']], 'message' => 'Simulated send'];
    }

    private static function sendWithSmtp($message, $config, $fromAddress)
    {
        Config::set('mail.mailers.dynamic_smtp', [
            'transport' => 'smtp',
            'host' => $config['smtpHost'] ?? env('SMTP_HOST'),
            'port' => $config['smtpPort'] ?? env('SMTP_PORT', 587),
            'encryption' => ($config['smtpSecure'] ?? env('SMTP_SECURE', false)) ? 'tls' : null,
            'username' => $config['smtpUser'] ?? env('SMTP_USER'),
            'password' => $config['smtpPass'] ?? env('SMTP_PASS'),
            'timeout' => null,
        ]);

        Config::set('mail.from.address', $fromAddress);

        Mail::mailer('dynamic_smtp')->raw($message['text'], function($m) use ($message, $fromAddress) {
            $m->to($message['to'])
              ->subject($message['subject'])
              ->from($fromAddress);
              
            if (!empty($message['html'])) {
                $m->html($message['html']);
            }
        });

        return ['success' => true];
    }

    private static function sendWithSendGrid($message, $config, $fromAddress)
    {
        $apiKey = trim($config['sendgridApiKey'] ?? env('SENDGRID_API_KEY') ?? '');
        
        $response = Http::withToken($apiKey)->post('https://api.sendgrid.com/v3/mail/send', [
            'personalizations' => [
                [
                    'to' => [['email' => $message['to']]],
                    'subject' => $message['subject']
                ]
            ],
            'from' => ['email' => $fromAddress],
            'content' => [
                ['type' => 'text/plain', 'value' => $message['text']],
                ['type' => 'text/html', 'value' => $message['html'] ?? $message['text']]
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception('SendGrid send failed with status ' . $response->status());
        }

        return $response->json();
    }

    private static function sendWithMailgun($message, $config, $fromAddress)
    {
        $apiKey = trim($config['mailgunApiKey'] ?? env('MAILGUN_API_KEY') ?? '');
        $domain = trim($config['mailgunDomain'] ?? env('MAILGUN_DOMAIN') ?? '');
        
        $response = Http::withBasicAuth('api', $apiKey)->asForm()
            ->post("https://api.mailgun.net/v3/{$domain}/messages", [
                'from' => $fromAddress,
                'to' => $message['to'],
                'subject' => $message['subject'],
                'text' => $message['text'],
                'html' => $message['html'] ?? $message['text'],
            ]);

        if ($response->failed()) {
            throw new \Exception('Mailgun send failed with status ' . $response->status());
        }

        return $response->json();
    }
}
