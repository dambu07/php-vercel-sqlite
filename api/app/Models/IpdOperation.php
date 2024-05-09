<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpdOperation extends Model
{
    use HasFactory;

    protected $table = 'ipd_operation';

    protected $fillable = [
        'ref_no', 'operation_category_id', 'operation_id', 'ipd_patient_department_id', 'operation_date',
        'assistant_consultant_1', 'assistant_consultant_2', 'doctor_id', 'anesthetist', 'anesthesia_type',
        'ot_technician', 'ot_assistant', 'remark', 'result',
    ];

    public static $rules = [
        'operation_category_id' => 'required',
        'operation_id'          => 'required',
        'operation_date'        => 'required',
        'doctor_id'             => 'required',
    ];

    public static $messages = [
        'operation_category_id.required' => 'Operation category field is required.',
        'operation_id.required'          => 'Operation field is required.',
        'operation_date'                 => 'Please select operation date.',
        'doctor_id.required'             => 'Doctor field is required.',
    ];

    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ipd_patient_department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IpdPatientDepartment::class);
    }

    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operations(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Operation::class, 'operation_id');
    }
}
