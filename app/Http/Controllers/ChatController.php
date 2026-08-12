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
     * Call the OpenAI-compatible Chat Completions API.
     * Swap the base_uri / model to use a different provider if you like
     * (e.g. OpenRouter, Groq, Azure OpenAI, or a local Ollama server).
     */
    private function askAi($history): string
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return "The chatbot isn't configured yet. Set OPENAI_API_KEY in your .env file to enable it.";
        }

        $client = new Client([
            'base_uri' => config('services.openai.base_uri', 'https://api.openai.com/v1/'),
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
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
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
