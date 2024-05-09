<?php

namespace App\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\IpdOperation;

class IpdOperationTable extends LivewireTableComponent
{
    protected $model = IpdOperation::class;

    public $showButtonOnHeader = true;

    public $buttonComponent = 'ipd_operation.add-button';

    public $ipdOperationId;

    protected $listeners = ['refresh' => '$refresh', 'resetPage'];

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setDefaultSort('ipd_operation.created_at', 'desc');
    }

    public function resetPage($pageName = 'page')
    {
        $rowsPropertyData = $this->getRows()->toArray();
        $prevPageNum = $rowsPropertyData['current_page'] - 1;
        $prevPageNum = $prevPageNum > 0 ? $prevPageNum : 1;
        $pageNum = count($rowsPropertyData['data']) > 0 ? $rowsPropertyData['current_page'] : $prevPageNum;

        $this->setPage($pageNum, $pageName);
    }

    public function mount(int $ipdOperationId)
    {
        $this->ipdOperationId = $ipdOperationId;
    }

    public function columns(): array
    {
        return [
            Column::make("Reference Id", "ref_no")
                ->sortable()
                ->searchable()
                ->view('ipd_operation.columns.ref_no'),
            Column::make("Operation date", "operation_date")
                ->sortable()
                ->searchable()
                ->view('ipd_operation.columns.operation_date'),
            Column::make("Operation Name", "operations.name")
                ->sortable(),
            Column::make("Operation category Name", "operations.operation_category.name")
                ->sortable(),
            Column::make("OT Technician", "ot_technician")
                ->sortable()
                ->searchable()
                ->view('ipd_operation.columns.ot_technician'),
            Column::make("Action", "id")
                ->view('ipd_operation.columns.action'),
        ];
    }

    public function builder(): Builder
    {
        return IpdOperation::with(['ipd_patient_department', 'operations.operation_category'])->where('ipd_patient_department_id',$this->ipdOperationId)->select('ipd_operation.*');
    }
}
