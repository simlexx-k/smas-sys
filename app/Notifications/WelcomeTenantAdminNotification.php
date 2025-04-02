<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Tenant;

class WelcomeTenantAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $tenant;
    protected $password;

    public function __construct(Tenant $tenant, ?string $password = null)
    {
        $this->tenant = $tenant;
        $this->password = $password;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . '!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Welcome to ' . config('app.name') . '. Your school "' . $this->tenant->name . '" has been successfully registered.')
            ->line('You can now access your school management dashboard using the following credentials:')
            ->line('Email: ' . $notifiable->email);

        if ($this->password) {
            $message->line('Password: ' . $this->password);
        }

        return $message
            ->line('Your school domain: ' . $this->tenant->domain . '.' . config('app.url'))
            ->line('Your trial period will end in 30 days.')
            ->action('Access Dashboard', route('tenant.dashboard'))
            ->line('If you have any questions, please don\'t hesitate to contact our support team.')
            ->line('Thank you for choosing our platform!');
    }
} 