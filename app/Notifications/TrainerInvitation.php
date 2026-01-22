<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainerInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $trainer,
        public string $invitationToken
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $acceptUrl = route('invitations.accept', ['token' => $this->invitationToken]);
        $rejectUrl = route('invitations.reject', ['token' => $this->invitationToken]);

        $trainerName = $this->trainer->profile->first_name . ' ' . $this->trainer->profile->last_name;

        return (new MailMessage)
            ->subject('Invitación para unirte a los entrenamientos')
            ->greeting('¡Hola ' . $notifiable->profile->first_name . '!')
            ->line($trainerName . ' te invitó a unirte a sus entrenamientos.')
            ->line('¿Querés aceptar la invitación?')
            ->action('Aceptar invitación', $acceptUrl)
            ->line('Si preferís rechazar la invitación, hacé clic en el siguiente enlace:')
            ->action('Rechazar invitación', $rejectUrl)
            ->line('Si no esperabas esta invitación, podés ignorar este correo.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $trainerName = $this->trainer->profile->first_name . ' ' . $this->trainer->profile->last_name;

        return [
            'type' => 'trainer_invitation',
            'trainer_id' => $this->trainer->id,
            'trainer_name' => $trainerName,
            'invitation_token' => $this->invitationToken,
            'message' => $trainerName . ' te invitó a unirte a sus entrenamientos. Aceptá o rechazá la invitación.',
        ];
    }
}
