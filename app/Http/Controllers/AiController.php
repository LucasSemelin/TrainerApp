<?php

namespace App\Http\Controllers;

use App\Actions\CreateClient;
use App\Agents\IntentParserAgent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function handlePrompt(Request $request, CreateClient $createClient)
    {
        $data = $request->validate(['prompt' => 'required|string']);
        $prompt = $data['prompt'];

        $sessionId = 'user_' . ($request->user() ? $request->user()->id . '_' . $request->session()->getId() : 'guest');

        $response = IntentParserAgent::run($prompt)
            ->forUser($request->user())
            ->withSession($sessionId)
            ->go();

        Log::info('AiController: Raw IntentParserAgent response', ['resp' => $response]);

        return redirect()->back();

        $parsedResponse = json_decode($response);

        Log::info('AiController: IntentParserAgent response', ['parsed' => $parsedResponse]);

        // DEVOLVER ERROR 422 EN CASO DE ERROR O MISSING DATA
        if ($parsedResponse->action === 'clarify') {
            return redirect()->back()->with('info', 'AI needs clarification: ' . ($parsedResponse->params->question ?? ''));
        }

        if ($parsedResponse->action === 'create_client') {

            $params = (array) $parsedResponse->params;
            $fakeRequest = Request::create('/', 'POST', $params);
            try {
                $newUser = $createClient->handle($fakeRequest);
                return redirect()->back()->with('success', 'Client created via AI: ' . $newUser->email);
            } catch (\Throwable $e) {
                Log::error('AiController: CreateClient failed', ['err' => $e->getMessage(), 'params' => $params]);
                return redirect()->back()->with('error', 'CreateClient failed: ' . $e->getMessage());
            }
        }

        return redirect()->back();
    }

    protected function extractFirstJson(string $text): ?string
    {
        // Attempt to find first balanced JSON object in $text
        $start = strpos($text, '{');
        if ($start === false) return null;

        $stack = 0;
        $inString = false;
        $escape = false;
        $end = null;
        $len = strlen($text);

        for ($i = $start; $i < $len; $i++) {
            $ch = $text[$i];
            if ($escape) {
                $escape = false;
                continue;
            }
            if ($ch === '\\') {
                $escape = true;
                continue;
            }
            if ($ch === '"') {
                $inString = !$inString;
                continue;
            }
            if ($inString) continue;
            if ($ch === '{') {
                $stack++;
            }
            if ($ch === '}') {
                $stack--;
                if ($stack === 0) {
                    $end = $i;
                    break;
                }
            }
        }

        if ($end === null) return null;
        return substr($text, $start, $end - $start + 1);
    }

    public function parse(Request $request)
    {
        $data = $request->validate(['prompt' => 'required|string']);
        $prompt = $data['prompt'];

        $response = IntentParserAgent::run($prompt)->forUser($request->user())->go();

        $raw = null;
        if (is_array($response) && isset($response['text'])) $raw = $response['text'];
        elseif (is_string($response)) $raw = $response;
        elseif (is_object($response) && property_exists($response, 'text')) $raw = $response->text;

        if (empty($raw)) {
            Log::error('AiController: Empty response from IntentParserAgent', ['resp' => $response]);
            return response()->json(['error' => 'Empty response from agent'], 500);
        }

        // Try direct decode first; if fails, try to extract first JSON block
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            $jsonBlock = $this->extractFirstJson($raw);
            if ($jsonBlock) {
                $decoded = json_decode($jsonBlock, true);
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('AiController: Failed to decode agent JSON', ['raw' => $raw, 'error' => json_last_error_msg()]);
            return response()->json(['error' => 'Agent returned invalid JSON', 'raw' => $raw], 500);
        }

        $action = $decoded['action'] ?? null;
        $params = $decoded['params'] ?? [];

        return response()->json(['status' => 'parsed', 'action' => $action, 'params' => $params, 'raw' => $raw]);
    }

    public function execute(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string',
            'params' => 'required|array'
        ]);

        $action = $data['action'];
        $params = $data['params'];

        // Simple role check: only trainers can create clients
        if ($action === 'create_client') {
            $user = $request->user();
            if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('trainer')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            $fakeRequest = Request::create('/', 'POST', $params);
            $create = new CreateClient();
            try {
                $newUser = $create->handle($fakeRequest);
                return response()->json(['status' => 'ok', 'user' => $newUser]);
            } catch (\Throwable $e) {
                Log::error('AiController: CreateClient failed', ['err' => $e->getMessage(), 'params' => $params]);
                return response()->json(['error' => 'CreateClient failed', 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => 'ignored', 'action' => $action]);
    }
}
