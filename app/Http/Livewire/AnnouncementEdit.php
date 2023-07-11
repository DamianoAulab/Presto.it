<?php

namespace App\Http\Livewire;

use App\Models\Announcement;
use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class AnnouncementEdit extends Component
{   

    public $title, $price, $description, $category_id;

    public Announcement $announcement;

    protected $rules = [
        'announcement.title' => 'required|max:100',
        'announcement.price' => 'required|numeric',
        'announcement.description' => 'required',
    ];

    public function update() {
        
        $this->announcement->save();
        $this->announcement->setAccepted(null);

        session()->flash('edit', 'Annuncio modificato! Sarà pubblicato dopo la revisione!');
        return redirect()->route('users.show', ['user' => Auth::user()->id]);
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }
    
    public function render()
    {
        return view('livewire.announcement-edit');
    }
}
