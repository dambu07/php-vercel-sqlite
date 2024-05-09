<?php

namespace App\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\IpdOperation;

class OverviewIpdOperationTable extends LivewireTableComponent
{
    public $ipdOperationId;

    protected $model = IpdOperation::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setSearchVisibilityDisabled()
            ->setDefaultSort('created_at', 'desc')
            ->setPerPageVisibilityDisabled();

        $this->setThAttributes(function (Column $column) {
            if ($column->isField('ipd_patient_department_id')) {
                return [
                    'class' => 'd-flex justify-content-end w-75 ps-125 text-center',
                    'style' =>  'width: 85% !important'
                ];
            }

            return [
                'class' => 'w-100',
            ];
        });
    }

    public function mount(int $ipdOperationId)
    {
        $this->ipdOperationId = $ipdOperationId;
    }

    public function columns(): array
    {
        return [
            Column::make("Reference Id", "id")
                ->view('ipd_operation.columns.ref_no'),
            Column::make("Operation date", "ref_no")
                ->view('ipd_operation.columns.operation_date'),
            Column::make("Operation Name", "operations.name"),
            Column::make("Operation category Name", "operations.operation_category.name"),
            Column::make("OT Technician", "ipd_patient_department_id")
                ->view('ipd_operation.columns.ot_technician'),
        ];
    }

    public function builder(): Builder
    {
        $query = IpdOperation::with('operations.operation_category')->where('ipd_patient_department_id',
            $this->ipdOperationId)->latest()->take(5)->get();
        if (count($query) > 0) {
            return $query->toQuery()->select('ipd_operation.*');
        }
        else{
            return IpdOperation::with('operations.operation_category')->where('ipd_patient_department_id',
                $this->ipdOperationId)->select('ipd_operation.*');
        }
    }
}