<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AgentResponseParser
{
    /**
     * Parse an agent response (string/array/object) and return an associative array
     * with keys: ['action' => string|null, 'params' => array, 'raw' => string]
     */
    public static function parse(mixed $response): ?array
    {
        $raw = self::normalizeToString($response);
        if ($raw === null) return null;

        // Try several decoding strategies
        $decoded = self::decodeNestedJson($raw);
        if (!is_array($decoded)) {
            Log::error('AgentResponseParser: failed to decode', ['raw_preview' => substr($raw, 0, 400)]);
            return null;
        }

        $node = self::findActionNode($decoded);
        if ($node === null) {
            return ['action' => null, 'params' => [], 'raw' => $raw];
        }

        return ['action' => $node['action'] ?? null, 'params' => $node['params'] ?? ($node['parameters'] ?? []), 'raw' => $raw];
    }

    protected static function normalizeToString(mixed $resp): ?string
    {
        if (is_string($resp)) return $resp;
        if (is_array($resp) && isset($resp['text'])) return $resp['text'];
        if (is_array($resp)) return json_encode($resp);
        if (is_object($resp)) {
            if (method_exists($resp, 'toArray')) {
                $arr = $resp->toArray();
                return $arr['text'] ?? json_encode($arr);
            }
            if (method_exists($resp, 'toJson')) return $resp->toJson();
            if (property_exists($resp, 'text')) return $resp->text;
            if (method_exists($resp, '__toString')) return (string) $resp;
            <?php

            namespace App\Services;

            use Illuminate\Support\Facades\Log;

            class AgentResponseParser
            {
                /**
                 * Parse an agent response (string/array/object) and return an associative array
                 * with keys: ['action' => string|null, 'params' => array, 'raw' => string]
                 */
                public static function parse(mixed $response): ?array
                {
                    $raw = self::normalizeToString($response);
                    if ($raw === null) return null;

                    // Try several decoding strategies
                    $decoded = self::decodeNestedJson($raw);
                    if (!is_array($decoded)) {
                        Log::error('AgentResponseParser: failed to decode', ['raw_preview' => substr($raw, 0, 400)]);
                        return null;
                    }

                    $node = self::findActionNode($decoded);
                    if ($node === null) {
                        return ['action' => null, 'params' => [], 'raw' => $raw];
                    }

                    return ['action' => $node['action'] ?? null, 'params' => $node['params'] ?? ($node['parameters'] ?? []), 'raw' => $raw];
                }

                protected static function normalizeToString(mixed $resp): ?string
                {
                    if (is_string($resp)) return $resp;
                    if (is_array($resp) && isset($resp['text'])) return $resp['text'];
                    if (is_array($resp)) return json_encode($resp);
                    if (is_object($resp)) {
                        if (method_exists($resp, 'toArray')) {
                            $arr = $resp->toArray();
                            return $arr['text'] ?? json_encode($arr);
                        }
                        if (method_exists($resp, 'toJson')) return $resp->toJson();
                        if (property_exists($resp, 'text')) return $resp->text;
                        if (method_exists($resp, '__toString')) return (string) $resp;
                        return json_encode($resp);
                    }
                    return null;
                }

                protected static function decodeNestedJson(string $raw): ?array
                {
                    $s = trim($raw);
                    // unwrap outer quotes
                    if (preg_match('/^"(.*)"$/s', $s, $m)) $s = $m[1];
                    // unescape
                    $s = stripcslashes($s);

                    $decoded = json_decode($s, true);
                    if (is_array($decoded)) return $decoded;

                    // try original raw
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) return $decoded;

                    // try extract first JSON block
                    $block = self::extractFirstJson($raw);
                    if ($block) {
                        $decoded = json_decode($block, true);
                        if (is_array($decoded)) return $decoded;
                    }

                    // handle double-encoded structures (single-key with JSON string)
                    $attempt = json_decode($s, true);
                    if (is_array($attempt)) {
                        foreach ($attempt as $v) {
                            if (is_string($v)) {
                                $nested = json_decode(stripcslashes($v), true);
                                if (is_array($nested)) return $nested;
                            }
                        }
                    }

                    return null;
                }

                protected static function extractFirstJson(string $text): ?string
                {
                    $start = null; $depth = 0; $len = strlen($text);
                    for ($i = 0; $i < $len; $i++) {
                        $ch = $text[$i];
                        if ($ch === '{') {
                            if ($start === null) $start = $i;
                            $depth++;
                        } elseif ($ch === '}') {
                            if ($depth > 0) {
                                $depth--;
                                if ($depth === 0 && $start !== null) {
                                    return substr($text, $start, $i - $start + 1);
                                }
                            }
                        }
                    }
                    return null;
                }

                protected static function findActionNode(array $data): ?array
                {
                    if (isset($data['action'])) return $data;
                    foreach ($data as $v) {
                        if (is_array($v)) {
                            $res = self::findActionNode($v);
                            if ($res !== null) return $res;
                        }
                    }
                    return null;
                }
            }
