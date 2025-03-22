<?php

use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination; 
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;


new class extends Component {
    use Toast;
    use WithPagination; 

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    // Create a public property.
    public int $country_id = 0;

    // Reset pagination when any component property changes
    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
        }
    }


    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->resetPage(); 
        $this->success('Filters cleared.', position: 'toast-top');
    }

    // Delete action
    public function delete(User $user): void
    {
    $user->delete();
    $this->warning("$user->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            // ['key' => 'country_name', 'label' => 'Country'], 
            ['key' => 'country_name', 'label' => 'Country', 'class' => 'hidden lg:table-cell'], 
            ['key' => 'email', 'label' => 'E-mail', 'sortable' => false],
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    public function users(): LengthAwarePaginator 
    {
        

        return User::query()
        ->withAggregate('country', 'name')
        ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
        ->when($this->country_id, fn(Builder $q) => $q->where('country_id', $this->country_id))
        ->orderBy(...array_values($this->sortBy))
        ->paginate(5);;
    }

    //Filter count method
    public function countSelectedFilters(): int
    {
        $count = 0;

        if (!empty($this->search)) {
            $count++;
        }
        if ($this->country_id > 0) {
            $count++;
        }

        return $count;

        
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
            'countries' => Country::all(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Users" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" badge="{{ $this->countSelectedFilters() }}" />
            <x-button icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination>
            @scope('actions', $user)
            <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-red-500" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        
        <div class="grid gap-5"> 
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />
            <x-select placeholder="Country" wire:model.live="country_id" :options="$countries" icon="o-flag" placeholder-value="0" /> 

            <x-slot:actions>
                <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
                <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
            </x-slot:actions>
        </div>
    </x-drawer>
</div>
