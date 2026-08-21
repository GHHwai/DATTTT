<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $messages = $request->user()->chatMessages()->orderBy('created_at')->get();

        return view('chat.index', compact('messages'));
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $user = $request->user();

        // Save the user's message.
        $user->chatMessages()->create([
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $reply = $this->askAi(
            $user->chatMessages()->orderBy('created_at')->get()
        );

        // Save the assistant's reply.
        $chatMessage = $user->chatMessages()->create([
            'role' => 'assistant',
            'content' => $reply,
        ]);

        return response()->json([
            'reply' => $reply,
            'created_at' => $chatMessage->created_at->toIso8601String(),
        ]);
    }

    /**
     * Call an OpenAI-compatible Chat Completions API.
     *
     * Defaults to Groq (https://console.groq.com/keys), which has a free
     * tier and an OpenAI-compatible endpoint. Swap the base_uri / model
     * in config/services.php (or the AI_* env vars) to use a different
     * provider if you like (e.g. OpenRouter, Gemini, or a local Ollama
     * server) — no code changes needed here.
     */
    private function askAi($history): string
    {
        $apiKey = config('services.ai.key');

        if (! $apiKey) {
            return "The chatbot isn't configured yet. Set AI_API_KEY in your .env file (get a free key at https://console.groq.com/keys) to enable it.";
        }

        $client = new Client([
            'base_uri' => config('services.ai.base_uri', 'https://api.groq.com/openai/v1/'),
            'timeout' => 30,
        ]);

        $messages = [
            ['role' => 'system', 'content' => 'You are a friendly, concise support assistant for this website.'],
        ];

        foreach ($history->take(-20) as $message) {
            $messages[] = ['role' => $message->role, 'content' => $message->content];
        }

        try {
            $response = $client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => config('services.ai.model', 'openai/gpt-oss-120b'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return $data['choices'][0]['message']['content'] ?? "Sorry, I couldn't generate a response.";
        } catch (\Throwable $e) {
            report($e);

            return 'Sorry, something went wrong talking to the AI service. Please try again in a moment.';
        }
    }
}
