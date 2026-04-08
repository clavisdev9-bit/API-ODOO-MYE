<?php

namespace App\Services;

use Exception;
use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;

class OdooService
{
    protected string $url;
    protected string $db;
    protected string $username;
    protected string $password;
    protected ?int $uid = null;
    protected Encoder $encoder;

    public function __construct()
    {
        $this->url      = rtrim(config('services.odoo.url'), '/');
        $this->db       = config('services.odoo.db');
        $this->username = config('services.odoo.user');
        $this->password = config('services.odoo.password');
        $this->encoder  = new Encoder();
    }

    // ---------------------------------------------------------------
    // Internal: kirim XML-RPC request
    // ---------------------------------------------------------------
    private function call(string $endpoint, string $method, array $params): mixed
    {
        $client = new Client("{$this->url}/xmlrpc/2/{$endpoint}");
        $client->setSSLVerifyPeer(false);
        $client->setSSLVerifyHost(false);

        $encodedParams = array_map(
            fn($p) => $p instanceof Value ? $p : $this->encoder->encode($p),
            $params
        );

        $request  = new Request($method, $encodedParams);
        $response = $client->send($request);

        if ($response->faultCode()) {
            throw new Exception(
                "Odoo Error [{$response->faultCode()}]: {$response->faultString()}"
            );
        }

        return $this->encoder->decode($response->value());
    }

    // ---------------------------------------------------------------
    // Encode domain — handle operator '|', '&', '!'
    // ---------------------------------------------------------------
    private function encodeDomain(array $domain): Value
    {
        $items = [];
        foreach ($domain as $item) {
            if (is_string($item)) {
                // Operator '|', '&', '!'
                $items[] = new Value($item, 'string');
            } elseif (is_array($item)) {
                // Kondisi ['field', 'operator', 'value']
                $items[] = new Value([
                    new Value($item[0], 'string'),
                    new Value($item[1], 'string'),
                    $this->encoder->encode($item[2]),
                ], 'array');
            }
        }
        return new Value($items, 'array');
    }

    // ---------------------------------------------------------------
    // Authenticate — dapatkan UID
    // ---------------------------------------------------------------
    public function authenticate(): int
    {
        if ($this->uid !== null) {
            return $this->uid;
        }

        $uid = $this->call('common', 'authenticate', [
            $this->db,
            $this->username,
            $this->password,
            [],
        ]);

        if (!$uid) {
            throw new Exception('Odoo authentication failed. Periksa kembali credentials kamu.');
        }

        $this->uid = (int) $uid;
        return $this->uid;
    }

    // ---------------------------------------------------------------
    // Search & Read
    // ---------------------------------------------------------------
    public function searchRead(
        string $model,
        array  $domain  = [],
        array  $fields  = [],
        // $limit dan $offset untuk pagination (0 = no limit tinggal sesuaikan misal $limit = 10 untuk 10 record per halaman)
        int    $limit   = 10,
        int    $offset  = 0
    ): array {
        $uid = $this->authenticate();

        return $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            'search_read',
            [$this->encodeDomain($domain)],
            [
                'fields' => $fields,
                'limit'  => $limit,
                'offset' => $offset,
            ],
        ]);
    }

    // ---------------------------------------------------------------
    // Count
    // ---------------------------------------------------------------
    public function searchCount(string $model, array $domain = []): int
    {
        $uid = $this->authenticate();

        return (int) $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            'search_count',
            [$this->encodeDomain($domain)],
        ]);
    }

    // ---------------------------------------------------------------
    // Read by ID
    // ---------------------------------------------------------------
    public function read(string $model, array $ids, array $fields = []): array
    {
        $uid = $this->authenticate();

        return $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            'read',
            [$ids],
            ['fields' => $fields],
        ]);
    }

    // ---------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------
    public function create(string $model, array $data): int
    {
        $uid = $this->authenticate();

        return (int) $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            'create',
            [$data],
        ]);
    }

    // ---------------------------------------------------------------
    // Update
    // ---------------------------------------------------------------
    public function write(string $model, array $ids, array $data): bool
    {
        $uid = $this->authenticate();

        return (bool) $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            'write',
            [$ids, $data],
        ]);
    }

    // ---------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------
    public function unlink(string $model, array $ids): bool
    {
        $uid = $this->authenticate();

        return (bool) $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            'unlink',
            [$ids],
        ]);
    }

    // ---------------------------------------------------------------
    // Call method custom (action_confirm, dll)
    // ---------------------------------------------------------------
    public function callMethod(
        string $model,
        string $method,
        array  $args   = [],
        array  $kwargs = []
    ): mixed {
        $uid = $this->authenticate();

        return $this->call('object', 'execute_kw', [
            $this->db,
            $uid,
            $this->password,
            $model,
            $method,
            $args,
            $kwargs,
        ]);
    }
}