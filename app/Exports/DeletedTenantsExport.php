<?php

namespace App\Exports;

use App\Models\Tenant;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DeletedTenantsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Tenant::onlyTrashed()
            ->with(['subscription', 'admin'])
            ->latest('deleted_at');
    }

    public function headings(): array
    {
        return [
            'School Name',
            'Email',
            'Admin Name',
            'Admin Email',
            'Subscription Status',
            'Deleted At',
            'Days Until Permanent Deletion'
        ];
    }

    public function map($tenant): array
    {
        return [
            $tenant->name,
            $tenant->email,
            $tenant->admin?->name,
            $tenant->admin?->email,
            $tenant->subscription?->status ?? 'No subscription',
            $tenant->deleted_at->format('Y-m-d H:i:s'),
            $tenant->deleted_at->addDays(30)->diffInDays(now())
        ];
    }
} 