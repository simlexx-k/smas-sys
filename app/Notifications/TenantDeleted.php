<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TenantDeleted extends Notification
{
    use Queueable;

    protected $tenant;
    protected $deletedBy;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
        $this->deletedBy = auth()->user();
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('School Account Deleted')
            ->line("The school account '{$this->tenant->name}' has been deleted.")
            ->line("Deleted by: {$this->deletedBy->name}")
            ->line("Deletion date: " . now()->format('Y-m-d H:i:s'))
            ->line('The data will be permanently deleted after 30 days.')
            ->action('View Deleted Schools', route('admin.tenants.trash'))
            ->line('Contact support if you need to restore this school.');
    }

    public function toArray($notifiable)
    {
        return [
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'deleted_by' => $this->deletedBy->name,
            'deleted_at' => now()->toDateTimeString()
        ];
    }
} 