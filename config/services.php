<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Chat Provider
    |--------------------------------------------------------------------------
    |
    | Free by default: Groq (https://console.groq.com/keys) exposes an
    | OpenAI-compatible Chat Completions endpoint with a generous free
    | tier, so no code changes are needed beyond this config block.
    |
    | Want a different free/paid provider instead? Just change the base_uri,
    | model, and key here — the rest of the app is provider-agnostic:
    |   - OpenRouter (has free models): https://openrouter.ai/api/v1/
    |   - Google Gemini (OpenAI-compat): https://generativelanguage.googleapis.com/v1beta/openai/
    |   - Local Ollama: http://localhost:11434/v1/
    |
    */

    'ai' => [
        'key' => env('AI_API_KEY'),
        'base_uri' => env('AI_BASE_URI', 'https://api.groq.com/openai/v1/'),
        'model' => env('AI_MODEL', 'llama-3.3-70b-versatile'),
    ],

];
