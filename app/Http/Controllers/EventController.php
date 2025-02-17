<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use PhpParser\Node\Stmt\Return_;
use Str;

class EventController extends CrudController
{
    protected $table = 'events';
    protected $modelClass = Event::class;
    protected $restricted = ['read_one', 'read_all', 'update', 'delete'];

    protected function getTable()
    {
        return $this->table;
    }

    protected function getModelClass()
    {
        return $this->modelClass;
    }

    protected function afterDeleteOne($modelClass, Request $request)
    {
        // Vérifie si l'événement a une image différente de l'image par défaut
        if ($request->image_cover && $request->image_cover !== 'default/event-cover.jpg') {
            try {
                // Supprimer l'image du stockage cloud
                Storage::disk('cloud')->delete($request->image_cover);
                
            } catch (\Exception $e) {
                Log::error('Error deleting image for event id '.$request->id.': '.$e->getMessage());
            }
        }
    }

    public function readAll(Request $request)
    {
        return Event::all();
    }

    public function getUserEvents()
    {
        $user = auth()->user();

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        // Récupérer ses événements
        $events = $user->events()->with('bookings')->get();

        return response()->json($events);
    }

    public function createOne(Request $request)
    {
        try {
            // Validation avec les règles du modèle Event
            $request->validate((new Event())->rules());
            // Gestion de l'upload de l'image de couverture
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $userId = auth()->id();
                $eventId = DB::table('events')->max('id')+1;
                $filename = time().'-'.Str::uuid().'.'.$file->getClientOriginalExtension();
                $path = "events/{$userId}/{$eventId}/{$filename}";
                Storage::disk('cloud')->put($path, file_get_contents($file));
                $request->merge(['cover_image' => "cloud/{$path}"]);
            }else {
                $request->merge(['cover_image' => "cloud/default/event-cover.jpg"]);
            }
            // Associer automatiquement l'organisateur à l'utilisateur connecté
            $request->merge(['organizer_id' => auth()->id()]);
    
            return parent::createOne($request);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création d’un événement : '.$e->getMessage());
            Log::error($e->getTraceAsString());
    
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]], 500);
        }
    }
    /** Custom validation for event update */ 
    public function updateOne($id, Request $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                // Récupérer l'événement à partir de l'ID
                $event = Event::find($id);
                if (!$event) {
                    return response()->json([
                        'success' => false,
                        'errors' => [__('events.not_found')],
                    ]);
                }
    
                // Récupérer l'ID de l'utilisateur connecté
                $userId = auth()->id(); 
    
                // Supprimer l'ancienne image si un nouveau fichier est envoyé
                if ($request->hasFile('cover_image')) {
                    // Supprimer l'ancienne image du cloud, si elle existe
                    if ($event->cover_image) {
                        $oldPath = str_replace('/cloud', '', $event->cover_image);
                        Storage::disk('cloud')->delete($oldPath);
                    }
    
                    // Télécharger la nouvelle image de couverture
                    $file = $request->file('cover_image');
                    $filename = time() . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                    
                    // Définir le chemin de stockage comme mentionné
                    $directory = "events/{$userId}/{$event->id}";
                    $path = Storage::disk('cloud')->putFileAs($directory, $file, $filename);
    
                    // Mettre à jour le chemin de l'image de couverture
                    $request->merge(['cover_image' => "/cloud/{$path}"]);
                }
    
                // Valider et mettre à jour les autres champs de l'événement
                $validated = $request->validate($event->rules($id)); // Validation des autres champs
                $event->update($validated);
    
                // Appel à la méthode parent pour gérer la mise à jour standard
                \Log::info('Données envoyées à CrudController:', $request->all());
                return parent::updateOne($id, $request);
            });
        } catch (\Exception $e) {
            Log::error('Error caught in function EventController.updateOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
    
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    /**
     * Supprimer un événement
     */ 
    
    
    
    
    
    
    
    

    /**
     * Lire tous les événements avec pagination et filtres
     */


    /**
     * Lire un événement spécifique
     */
    public function readOne($id, Request $request)
    {
        try {
            $event = Event::findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => ['event' => $event],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors'  => ['Event not found.']
            ], 404);
        }
    }

}
