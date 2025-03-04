<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Domain;
use App\Services\DomainService;
use Illuminate\Http\Request;

class TenantDomainController extends Controller
{
    protected $domainService;

    public function __construct(DomainService $domainService)
    {
        $this->domainService = $domainService;
    }

    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain',
            'is_primary' => 'boolean',
            'verification_method' => 'required|in:dns,file'
        ]);

        $domain = $this->domainService->create($tenant, $validated);

        return back()->with('success', 'Domain added successfully.');
    }

    public function setPrimary(Tenant $tenant, Domain $domain)
    {
        $this->domainService->setPrimary($tenant, $domain);

        return back()->with('success', 'Primary domain updated successfully.');
    }

    public function verify(Request $request, Tenant $tenant, Domain $domain)
    {
        $verified = $this->domainService->verify($domain);

        return back()->with(
            $verified ? 'success' : 'error',
            $verified ? 'Domain verified successfully.' : 'Domain verification failed.'
        );
    }

    public function destroy(Tenant $tenant, Domain $domain)
    {
        $this->domainService->delete($domain);

        return back()->with('success', 'Domain deleted successfully.');
    }
} 