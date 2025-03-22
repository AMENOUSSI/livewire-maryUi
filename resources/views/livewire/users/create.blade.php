<?php


use App\Models\User; 
use Mary\Traits\Toast;
use App\Models\Language;
use App\Models\Country;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;

new class extends Component {
     // We will use it later 
     use Toast, WithFileUploads;
     
 
    // Component parameter 
    Public User $user;

    // You could use Livewire "form object" instead.
    #[Rule('required')] 
    public string $name = '';
 
    #[Rule('required|email')]
    public string $email = '';
 
    #[Rule('required')]
    public string $password = '';
    // Optional
    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('nullable|image|max:8192')] 
    public $photo;
 

    // Selected languages 
    #[Rule('required')]
    public array $my_languages = [];
    
    // We also need this to fill Countries combobox on upcoming form
    

    public function mount(User $user): void
    {
        $this->user = $user;
        
        
        
 
        // Fill the selected languages property 
        $this->my_languages = $this->user->languages->pluck('id')->all();
    }

    //save method
    public function save(): void
    {
        // Validate
        $data = $this->validate();

        
    
       
        
        $user = new User();

        $user->name = $this->name;
        $user->email = $this->email;
        $user->password = Hash::make($this->password);
        $user->country_id = $this->country_id;
        
        
        $user->save();

        $this->user = $user;
        
        

        // Sync selection 
        if (!empty($this->my_languages)) {
            $user->languages()->sync($this->my_languages); // Utilisez l'ID de l'utilisateur nouvellement créé
        }

        // if ($this->photo) { 
        //     $url = $this->photo->store('users', 'public');
        //     $this->user->update(['avatar' => "/storage/$url"]);
        // }
        if ($this->photo) { 
            $url = $this->photo->store('users', 'public');
            $user->avatar = "/storage/$url"; // Assignez l'URL de l'avatar
        }
        
        
 
        // You can toast and redirect to any route
        $this->success('User updated with success.', redirectTo: '/users');
    }

    public function with(): array 
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(), // Available Languages 
        ];
    }

}; ?>


<div>
    <x-header title="Create new user " separator /> 

        <!-- Grid stuff from Tailwind -->
   <div class="grid gap-5 lg:grid-cols-2"> 
       <div>
           <x-form wire:submit="save"> 
               <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change> 
                   <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
               </x-file>
   
               <x-input label="Name" wire:model="name" />
               <x-input label="Email" wire:model="email" />
               <x-password label="Password" hint="It toggles visibility" wire:model="password" clearable />
               <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---" />

               {{-- Multi selection --}}
               <x-choices-offline
               label="My languages"
               wire:model="my_languages"
               :options="$languages"
               searchable />
        
               <x-slot:actions>
                   <x-button label="Cancel" link="/users" />
                   {{-- The important thing here is `type="submit"` --}}
                   {{-- The spinner property is nice! --}}
                   <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
               </x-slot:actions>
           </x-form>
       </div>

       <div class="hidden">
           {{-- Get a nice picture from `StorySet` web site --}}
           <img src="/edit-form.png" width="300" class="mx-auto" />
       </div>
   </div>
</div>
