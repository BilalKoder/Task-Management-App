<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $users, $title, $color, $image, $user_id,$first_name,$last_name,$email,$password,$phone;
    public $isOpen = 0;

    public function render()
    {
        $this->users = User::all();
        return view('livewire.users.users');
    }

    public function store()
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'image' => ['required'],
            'password' => ['min:6'],
        ]);

        $storedImage = $this->image->store('public/users');

        $updateData = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'profile_photo_path' => url('storage'. Str::substr($storedImage, 6)),
        ];

        // if(!empty($this->password)){ unset($updateData['password']); }

        User::updateOrCreate(['id' => $this->user_id], $updateData);

        session()->flash(
            'message',
            $this->user_id ? 'User Updated Successfully.' : 'User Created Successfully.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        User::find($id)->delete();
        session()->flash('message', 'User Deleted Successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $id;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->password = $user->password;
        $this->email = $user->email;
        $this->phone = $user->phone;

        $this->openModal();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->first_name = '';
        $this->last_name = '';
        $this->user_id = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
    }
}
