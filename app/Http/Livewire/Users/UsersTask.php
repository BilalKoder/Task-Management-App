<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\UserTask;
use App\Models\UserAssignedTask;
use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Carbon\Carbon;

class UsersTask extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $users, $title, $color, $image, $user_id,$first_name,$last_name,$email,$password,$phone;
    public $isOpen = 0;

    // public function render()
    // {
    //     $this->users = User::where('user_type','user')->get();
    //     return view('livewire.users.users');
    // }

    public $cid;

    public function mount($id)
    {
        $this->cid = $id;
    }


    public function render(){

        return view('livewire.users.tasks.index', [
            'tasks' => UserAssignedTask::orderBy('id', 'desc')
            ->where('user_id',$this->cid)
            ->whereBetween('created_at',[Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()])->paginate(),
            'user_id' => $this->cid

        ]);
    }
  
}
