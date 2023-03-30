<?php

namespace Lunar\Hub\Http\Livewire\Components\FieldTypes;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Lunar\Models\Asset;

class FileFieldtype extends Component
{
    use WithFileUploads;
    use WithPagination;

    /**
     * Whether to show the uploader.
     *
     * @var bool
     */
    public $showUploader = false;

    /**
     * The file to upload.
     *
     * @var UploadedFile
     */
    public $file = null;

    /**
     * Maximum number of files to upload.
     *
     * @var int
     */
    public $maxFiles = 1;

    /**
     * Array of selected assets.
     */
    public array $selected = [];

    /**
     * The unique signature for the component.
     *
     * @var string
     */
    public $signature;

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            'file' => 'file',
        ];
    }

    /**
     * Listener for when the file is updated.
     *
     * @return void
     */
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

    /**
     * Process the file upload.
     *
     * @return void
     */
    public function process()
    {
        $this->emit('updatedAttributes', [
            'path' => $this->signature,
            'data' => $this->selected,
        ]);

        $this->showUploader = false;
    }

    public function updatedshowUploader()
    {
        $this->resetPage('assetsPage');
    }

    /**
     * Return the available assets.
     *
     * @return Collection
     */
    public function getAssetsProperty()
    {
        return Asset::paginate(8, ['*'], 'assetsPage');
    }

    /**
     * Return the asset models that have been selected.
     *
     * @return Colletion
     */
    public function getSelectedModelsProperty()
    {
        return Asset::with(['file'])->findMany(
            $this->selected
        );
    }

    /**
     * Remove the selected asset.
     *
     * @param  int  $id
     * @return void
     */
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
