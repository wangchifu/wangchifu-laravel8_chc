<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;

class Search extends Component
{
    public $search = '';

    protected $rules = ['search' => 'required|min:6'];

    public function render()
    {
        return view('livewire.search', [
            'users' => User::where('name', $this->search)->get(),
        ]);
    }
}
