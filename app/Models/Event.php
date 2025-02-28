<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends BaseModel
{
    use HasFactory;

    // Validation rules
    public function rules($id = null)
    {
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'location' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
                'image' => 'nullable|image',
                'organizer_id' => 'exists:users,id',
                'cover_image' => 'nullable|string',
            ];
        return $rules;
    }

    // Fillable properties
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'organizer_id',
        'cover_image',
        'capacity',
    ];

    // Hidden properties
    protected $hidden = [
        'organizer',
    ];

    // Appended attributes
    protected $appends = [
        'cover_image_url', // Add the custom cover_image_url attribute
        'available_spots', // Add the available_spots attribute
        "organiser_name",
    ];

    // Dates
    protected $dates = [
        'start_date',
        'end_date',
    ];

    // Relations
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'event_id');
    }

    public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) {
            return null;
        }
    
        return str_starts_with($this->cover_image, 'http') ? $this->cover_image : url($this->cover_image);
    }
    public function getOrganiserNameAttribute()
    {
    return $this->organizer->email;
    }

    public function getAvailableSpotsAttribute()
    {
        // Nombre total de places initiales (capacity)
        $initialSpots = $this->capacity;

        // Nombre de places réservées
        $bookedSpots = $this->bookings()->sum('spots');

        // Nombre de places restantes
        return max($initialSpots - $bookedSpots, 0);
    }
    public function updateAvailableSpots()
{
    // Calculer les places disponibles
    $bookedSpots = $this->bookings()->sum('spots');
    $available = max($this->capacity - $bookedSpots, 0);

    // Mettre à jour la base de données si nécessaire
    $this->update([
        'capacity' => $available + $bookedSpots, // Ajuster si nécessaire
    ]);
}

     protected static function booted()
    {
        parent::booted();

        // Lors de la création de l'événement, attribuer les permissions à l'utilisateur connecté
        static::created(function ($event) {
            $user = auth()->user(); // L'utilisateur connecté
            $eventPermissions = [
                'events.' . $event->id . '.read',
                'events.' . $event->id . '.update',
                'events.' . $event->id . '.delete',
            ];

            // Donner les permissions à l'utilisateur
            foreach ($eventPermissions as $permissionName) {
                $user->givePermission($permissionName);
            }
        });
        static::deleted(
            function ($event) {
                $permissions = Permission::where('name', 'like', 'events.'.$event->id.'.%')->get();
                DB::table('users_permissions')->whereIn('permission_id', $permissions->pluck('id'))->delete();
                Permission::destroy($permissions->pluck('id'));
            }
        );
    }


}
