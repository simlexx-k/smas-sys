@component('mail::message')
# School Account Deleted

The school account **{{ $tenant->name }}** has been deleted.

**Details:**
- Deleted by: {{ $deletedBy->name }}
- Deletion date: {{ now()->format('Y-m-d H:i:s') }}
- School email: {{ $tenant->email }}
- Subscription status: {{ $tenant->subscription?->status ?? 'No subscription' }}

The data will be permanently deleted after 30 days. You can restore the school before then if needed.

@component('mail::button', ['url' => route('admin.tenants.trash')])
View Deleted Schools
@endcomponent

If you need to restore this school, please contact support.

Thanks,<br>
{{ config('app.name') }}
@endcomponent 