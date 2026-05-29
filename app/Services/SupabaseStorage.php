<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseStorage
{
    private $url;
    private $key;
    private $bucket;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL') . '/storage/v1';
        $this->key = env('SUPABASE_SERVICE_ROLE');
        $this->bucket = env('SUPABASE_BUCKET');
    }

    public function upload($path, $file, $mime)
    {
        $endpoint = "{$this->url}/object/{$this->bucket}/{$path}";

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->key,
            'Content-Type' => $mime,
            'x-upsert' => 'true',
        ])
        ->withBody($file, $mime)   // ← ENVÍA RAW BINARIO
        ->put($endpoint);
    }

    public function getPublicUrl($path)
    {
        return "{$this->url}/object/public/{$this->bucket}/{$path}";
    }

    public function delete($path)
    {
        $endpoint = "{$this->url}/object/{$this->bucket}/{$path}";

        return Http::withHeaders([
            'Authorization' => 'Bearer '.$this->key,
        ])->delete($endpoint);
    }
}