<?php

namespace App\Http\Livewire\Cellars;

use App\Models\BottleInCellar;
use Livewire\Component;
use App\Models\Cellar;

class SingleCellar extends Component
{
    public $cellarId;
    public $cellar;
    public $count;
    public $newName;
    protected $listeners = ['bottleDeleted' => 'handleBottleDeleted', 'updateCellarName' => 'updateName'];
  

    public $editing ;

    // Recupère l'id dans le URL de la page directement à l'ouverture
    public function mount($cellar_id)
    {
        $this->cellarId = $cellar_id;
    }
      
    // Permet de faire l'édition du nom du cellier
    public function updateName($newName){
        $this->newName = $newName;

        $this->validate([
            'newName' => 'required|max:100', 
        ]);
        $cellar = Cellar::findOrFail($this->cellarId);
        $cellar->name = $this->newName;
        $cellar->save();
    }
    

    public function handleBottleDeleted()
    {
        // refresh the list of bottles by re-fetching the cellar
        $this->cellar = Cellar::with(['bottles' => function ($query) {
            $query->whereNull('bottle_in_cellars.deleted_at');
        }])->where('id', $this->cellarId)->first();   
    }

    // public function increment($bottle_id)
    // {
    //     $bottleInCellar = BottleInCellar::where('cellar_id', $this->cellarId)
    //     ->where('bottle_id', $bottle_id)
    //     ->first();
        
    //     if ($bottleInCellar) {
    //         $bottleInCellar->quantity += 1;
    //         $bottleInCellar->save();
    //     }
    // }

    // public function decrement($bottle_id)
    // {
    //     $bottleInCellar = BottleInCellar::where('cellar_id', $this->cellarId)
    //         ->where('bottle_id', $bottle_id)
    //         ->first();
    
    //     if ($bottleInCellar) {
    //         $bottleInCellar->quantity -= 1;
    //         if ($bottleInCellar->quantity < 0) {
    //             $bottleInCellar->quantity = 0;
    //         }
    //         $bottleInCellar->save();
    //     }

    // }

    // montre les bouteilles associées au cellier qui n'ont pas de valeur dans la colonne 'deleted_at'
    public function render()
    {
        $this->cellar = Cellar::with(['bottles' => function ($query) {
            $query->whereNull('bottle_in_cellars.deleted_at');
        }])->where('id', $this->cellarId)->first();
        
        return view('livewire.Cellars.single-cellar', ['cellar' => $this->cellar]);
    }    
}
