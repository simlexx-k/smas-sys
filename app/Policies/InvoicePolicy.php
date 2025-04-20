<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function generate(User $user, Invoice $invoice)
    {
        // Update role checking to use your custom implementation
        $isLandlord = $user->role === User::ROLE_LANDLORD;
        $isTenantAdmin = $user->role === User::ROLE_TENANT_ADMIN;
        
        \Log::info('Policy Check', [
            'user_id' => $user->id,
            'user_role' => $user->role, // Changed from roles to single role
            'user_tenant' => $user->tenant_id,
            'invoice_tenant' => $invoice->tenant_id,
            'is_landlord' => $isLandlord,
            'is_tenant_admin' => $isTenantAdmin,
            'tenant_match' => $invoice->tenant_id === $user->tenant_id
        ]);

        // Landlord can generate any invoice
        if ($isLandlord) {
            return true;
        }

        // Tenant admin can only generate their tenant's invoices
        if ($isTenantAdmin) {
            return $invoice->tenant_id === $user->tenant_id;
        }

        // Default deny
        return false;
    }
} 