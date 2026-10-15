<?php

namespace Tests\Support;

use Stripe\HttpClient\ClientInterface;

/** Exercises the real Stripe SDK/Cashier serialization without any network. */
final class FakeStripeHttpClient implements ClientInterface
{
    public array $requests = [];

    public array $responses = [];

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
    {
        $path = parse_url($absUrl, PHP_URL_PATH);
        $this->requests[] = compact('method', 'path', 'params');
        $response = array_shift($this->responses);
        if (! $response || $response['method'] !== $method || $response['path'] !== $path) {
            throw new \LogicException("Unexpected Stripe call blocked: {$method} {$path}");
        }

        return [json_encode($response['body'], JSON_THROW_ON_ERROR), $response['status'] ?? 200, []];
    }
}
