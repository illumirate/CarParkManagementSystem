<?php
//  Author: Ng Ian Kai

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmation extends Notification
{
    use Queueable;

    protected Booking $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;
        $slot = $booking->parkingSlot;
        $zone = $slot->zone;

        return (new MailMessage)
            ->subject('Booking Confirmed - ' . $booking->booking_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your parking booking has been confirmed.')
            ->line('**Booking Details:**')
            ->line('Booking Number: ' . $booking->booking_number)
            ->line('Parking Slot: ' . $slot->slot_id)
            ->line('Zone: ' . $zone->zone_name)
            ->line('Date: ' . $booking->booking_date->format('l, d M Y'))
            ->line('Time: ' . $booking->start_time->format('H:i') . ' - ' . $booking->end_time->format('H:i'))
            ->line('Total Fee: RM ' . number_format($booking->total_fee, 2))
            ->action('View Booking', route('bookings.show', $booking))
            ->line('Please arrive on time. Your booking will expire if you do not check in.')
            ->line('Thank you for using TARUMT Car Park!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_number,
        ];
    }
}
