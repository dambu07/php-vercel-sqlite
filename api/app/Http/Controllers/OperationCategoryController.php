<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperationCategoryRequest;
use App\Http\Requests\UpdateOperationCategoryRequest;
use App\Models\Doctor;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Repositories\OperationCategoryRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationCategoryController extends AppBaseController
{
    /**
     * @var OperationCategoryRepository
     */
    private OperationCategoryRepository $categoryRepository;

    public function __construct(OperationCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('operation_categories.index');
    }

    /**
     * @param StoreOperationCategoryRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreOperationCategoryRequest $request): JsonResponse
    {
        $input = $request->all();
        $this->categoryRepository->create($input);

        return $this->sendSuccess(__('messages.operation_category.operation_category').' '.__('messages.common.saved_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param OperationCategory $operationCategory
     * @return JsonResponse
     */
    public function edit(OperationCategory $operationCategory)
    {
        return $this->sendResponse($operationCategory, 'Operation Category retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateOperationCategoryRequest $request
     * @param OperationCategory $operationCategory
     * @return JsonResponse
     */
    public function update(UpdateOperationCategoryRequest $request, OperationCategory $operationCategory): JsonResponse
    {
        $input = $request->all();
        $this->categoryRepository->update($input, $operationCategory->id);

        return $this->sendSuccess(__('messages.operation_category.operation_category').' '.__('messages.common.updated_successfully'));
    }

    /**
     * @param OperationCategory $operationCategory
     *
     * @return JsonResponse
     */
    public function destroy(OperationCategory $operationCategory): JsonResponse
    {
        $operationModels = [
            Operation::class,
        ];
        $result = canDelete($operationModels, 'operation_category_id', $operationCategory->id);
        if ($result) {
            return $this->sendError(__('messages.operation_category.operation_category').' '.__('messages.common.cant_be_deleted'));
        }
        $operationCategory->delete();

        return $this->sendSuccess(__('messages.operation_category.operation_category').' '.__('messages.common.deleted_successfully'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     *
     * @return mixed
     */
    public function getOperationName(Request $request): mixed
    {
        return Operation::where('operation_category_id', $request->id)->get()->pluck('id', 'name');
    }
}
