<?php

namespace App\Repositories;

use App\Models\OperationCategory;

/**
 * Class OperationCategoryRepository
 */
class OperationCategoryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
    ];

    /**
     * @return array|string[]
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * @return string
     */
    public function model()
    {
        return OperationCategory::class;
    }
}
