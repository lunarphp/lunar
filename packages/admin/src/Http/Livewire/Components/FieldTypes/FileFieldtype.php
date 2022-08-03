<?php

namespace GetCandy\Hub\Http\Livewire\Components\FieldTypes;

use GetCandy\Models\Asset;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileFieldtype extends Component
{
    use WithFileUploads;

    public $showUploader = false;

    public $file = null;

    public $maxFiles = 1;

    public array $selected = [];

    public $signature;

    public function rules()
    {
        return [
            'file' => 'file',
        ];
    }

    public function updatedFile()
    {
        DB::transaction(function () {
            $asset = Asset::create();
            $asset
            ->addMedia($this->file->getRealPath())
            ->usingFileName($this->file->getClientOriginalName())
            ->toMediaCollection('uploads');

            $this->file = null;
        });
    }

    public function process()
    {
        $this->emit('updatedAttributes', [
            'path' => $this->signature,
            'data' => $this->selected,
        ]);

        $this->showUploader = false;
        // dd($this->signature);
    }

    public function getAssetsProperty()
    {
        return Asset::get();
    }

    public function getSelectedModelsProperty()
    {
        return Asset::with(['file'])->findMany(
            $this->selected
        );
    }

    public function removeSelected($id)
    {
        $index = collect($this->selected)->search($id);
        $selected = $this->selected;

        unset($selected[$index]);

        $this->selected = collect($selected)->values()->toArray();

        $this->emit('updatedAttributes', [
            'path' => $this->signature,
            'data' => $this->selected,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        return view('adminhub::livewire.components.field-types.file');
    }
}
