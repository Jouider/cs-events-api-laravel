<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends BaseModel
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'event_id',
        'spots',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec l'événement
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Validation des données
    public function rules($id = null)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'event_id' => 'required|exists:events,id',
            'spots' => 'required|integer|min:1',
        ];
    }

    protected static function booted()
    {
        parent::booted();

        // Avant de créer une réservation, vérifier la disponibilité
        static::creating(function ($booking) {

            $event = Event::findOrFail($booking->event_id);

            if ($event->organizer_id == $booking->user_id) {
                throw new \Exception("L'organisateur ne peut pas réserver son propre événement.");
            }

            if ($booking->spots > $event->available_spots) {
                throw new \Exception("Il ne reste que {$event->available_spots} places disponibles.");
            }
        });

        static::created(function ($booking) {
            $user = auth()->user(); // L'utilisateur connecté
    
            $eventPermissions = [
                'bookings.' . $booking->id . '.read',
                'bookings.' . $booking->id . '.delete',
            ];
    
            // Donner les permissions à l'utilisateur
            foreach ($eventPermissions as $permissionName) {
                $user->givePermission($permissionName);
            }
        });

        // Lorsqu'une réservation est annulée, restaurer les places
        static::deleted(function ($booking) {
            $permissions = Permission::where('name', 'like', 'bookings.'.$booking->id.'.%')->get();
                DB::table('users_permissions')->whereIn('permission_id', $permissions->pluck('id'))->delete();
                Permission::destroy($permissions->pluck('id')); 

            $event = Event::find($booking->event_id);
            if ($event) {
                // Mettre à jour manuellement le nombre de places disponibles
                $bookedSpots = $event->bookings()->sum('spots');
                $availableSpots = max($event->capacity - $bookedSpots, 0);

                // Sauvegarder en base de données
                $event->update(['available_spots' => $availableSpots]);
            }
                });
            }
}
