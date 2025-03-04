<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class DomainService
{
    public function create(Tenant $tenant, array $data): Domain
    {
        // Generate verification token
        $data['verification_token'] = Str::random(32);

        // Create domain
        $domain = $tenant->domains()->create([
            'domain' => $data['domain'],
            'is_primary' => $data['is_primary'],
            'verification_method' => $data['verification_method'],
            'verification_token' => $data['verification_token']
        ]);

        // If this is the first domain or set as primary
        if ($data['is_primary'] || $tenant->domains()->count() === 1) {
            $this->setPrimary($tenant, $domain);
        }

        return $domain;
    }

    public function setPrimary(Tenant $tenant, Domain $domain): void
    {
        // Remove primary status from all other domains
        $tenant->domains()->where('id', '!=', $domain->id)
            ->update(['is_primary' => false]);

        // Set this domain as primary
        $domain->update(['is_primary' => true]);

        // Update tenant's main domain
        $tenant->update(['domain' => $domain->domain]);
    }

    public function verify(Domain $domain): bool
    {
        $verified = false;

        if ($domain->verification_method === 'dns') {
            $verified = $this->verifyDNS($domain);
        } else {
            $verified = $this->verifyFile($domain);
        }

        if ($verified) {
            $domain->update([
                'is_verified' => true,
                'verified_at' => now()
            ]);
        }

        return $verified;
    }

    private function verifyDNS(Domain $domain): bool
    {
        try {
            $records = dns_get_record("_school-verify.{$domain->domain}", DNS_TXT);
            foreach ($records as $record) {
                if (isset($record['txt']) && $record['txt'] === $domain->verification_token) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            report($e);
        }

        return false;
    }

    private function verifyFile(Domain $domain): bool
    {
        try {
            $response = Http::get("https://{$domain->domain}/.well-known/school-verification.txt");
            return $response->successful() && trim($response->body()) === $domain->verification_token;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function delete(Domain $domain): void
    {
        // If this is the primary domain, set another domain as primary
        if ($domain->is_primary) {
            $newPrimary = $domain->tenant->domains()
                ->where('id', '!=', $domain->id)
                ->where('is_verified', true)
                ->first();

            if ($newPrimary) {
                $this->setPrimary($domain->tenant, $newPrimary);
            }
        }

        $domain->delete();
    }
} 