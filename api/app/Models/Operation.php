<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    public $table = 'operations';

    public $fillable = ['operation_category_id', 'name'];

    /**
     * @var string[]
     */
    protected $casts = [
        'id'                    => 'integer',
        'operation_category_id' => 'integer',
        'name'                  => 'string',
    ];

    public static $rules = [
        'operation_category_id' => 'required',
        'name'                  => 'required|unique:operations,name',
    ];

    public static $messages = [
        'operation_category_id.required' => 'Operation category is required.',
    ];

    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operation_category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(OperationCategory::class);
    }
}
