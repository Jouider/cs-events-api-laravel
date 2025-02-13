<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'organizer_id',
        'cover_image',
        'capacity', // Ajout du champ capacity
    ];

    protected $hidden = [
        // exemple : 'organizer_id'
    ];

    protected $appends = [
        'cover_image_url', // Ajout de l'attribut personnalisé cover_image_url
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
    
    /**
     * Définit le chemin de stockage pour l'image de couverture.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @return void
     */
    public function setCoverImage($image)
    {
        if ($image) {
            // On stocke l'image dans le dossier 'events' sur le disque 'cloud'
            $path = $image->store('events', 'cloud');
            $this->cover_image = $path;
        }
    }

    /**
     * Récupère l'URL de l'image de couverture.
     *
     * @return string
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            // On génère l'URL pour l'image stockée dans le cloud
            return Storage::disk('cloud')->url($this->cover_image);
        }

        return null; // Si aucune image n'est définie, on retourne null
    }
}
