<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Jobs\ResizeImage;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;



class AnnouncementCreate extends Component
{
    use WithFileUploads;
    
    public $title, $price, $description, $category_id, $temporary_images,  $images=[];
    public $announcement;
    

    
    protected $rules = [
        'title' => 'required|max:100|min:5',
        'price' => 'required|numeric',
        'description' => 'required',
        'images.*' => 'image|max:5120',
        'temporary_images.*' => 'image|max:5120',
    ];

    protected $messages = [
        'required' => 'Il campo :attribute è richiesto',
        'max' => 'Il campo :attribute è troppo lungo',
        'min' => 'Il campo :attribute è troppo corto',
        'image' => 'Il campo :attribute deve essere un \'immagine',
        'temporary_images.*.max' => 'L\'immagine deve essere massimo di 5mb',
        'images.*.max' => 'L\'immagine deve essere massimo di 5mb',

        

    ];

    public function updatedTemporaryImages() {
        
        if ($this->validate([
            'temporary_images.*' => 'image|max:5120',
        ])) {
        foreach ($this->temporary_images as $image) {
            $this->images[] = $image;
        }
      }
    }

    public function removeImage($key) {
        if (in_array($key, array_keys($this->images))) {
            unset($this->images[$key]);
            
        }
    }

    public function store(){

        $this->validate();

        $announcement = Category::find($this->category_id)->announcements()->create([
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'user_id' => Auth::user()->id,
            'images' => $this->images,
            'temporary_images' => $this->temporary_images,
        ]);

        if (count($this->images)) {
            
            foreach ($this->images as $image) {
                $newFileName = "announcements/{$announcement->id}";
                $newImage = $announcement->images()->create(['path' => $image->store($newFileName, 'public')]);

                dispatch(new ResizeImage($newImage->path, 600, 600));
            }
            File::deleteDirectory(storage_path('/app/livewire-tmp'));
        }
       
        $this->cleanForm();
        session()->flash('success', 'Annuncio creato! Sarà pubblicato dopo la revisione');
        return redirect()->route('announcements.create');
        
    }

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function cleanForm() {
        $this->title = '';
        $this->description = '';
        $this->price = '';
        $this->category_id = '';
        $this->images = [];
        $this->temporary_images = [];
        
    }

    public function render()
    {
        return view('livewire.announcement-create');
    }
}
