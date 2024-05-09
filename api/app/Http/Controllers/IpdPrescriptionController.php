<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIpdPrescriptionRequest;
use App\Http\Requests\UpdateIpdPrescriptionRequest;
use App\Models\IpdPrescription;
use App\Models\Medicine;
use App\Queries\IpdPrescriptionDataTable;
use App\Repositories\IpdPrescriptionRepository;
use DataTables;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Throwable;

class IpdPrescriptionController extends AppBaseController
{
    /** @var IpdPrescriptionRepository */
    private $ipdPrescriptionRepository;

    public function __construct(IpdPrescriptionRepository $ipdPrescriptionRepo)
    {
        $this->ipdPrescriptionRepository = $ipdPrescriptionRepo;
    }

    /**
     * Display a listing of the IpdPrescription.
     *
     * @param  Request  $request
     * @return Response
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of((new IpdPrescriptionDataTable())->get($request->get('id')))->make(true);
        }
    }

    /**
     * Store a newly created IpdPrescription in storage.
     *
     * @param  CreateIpdPrescriptionRequest  $request
     * @return JsonResponse
     */
    public function store(CreateIpdPrescriptionRequest $request)
    {
        $input = $request->all();
        $arr = collect($input['medicine_id']);
        $duplicateIds = $arr->duplicates();
        foreach ($input['medicine_id'] as $key => $value) {
            $medicine = Medicine::find($input['medicine_id'][$key]);
            $qty = $input['day'][$key] * $input['dose_interval'][$key];
            if(!empty($duplicateIds)){
                foreach($duplicateIds as $key => $value){
                    $medicine = Medicine :: find($duplicateIds[$key]);

                    return $this->sendError(__('messages.medicine_bills.duplicate_medicine'));
                }
            }

            if ($medicine->available_quantity <  $qty) {
                $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;

                return $this->sendError(__('The available quantity of ' . $medicine->name . ' is ' . $available . '.'));

            }
        }
        $this->ipdPrescriptionRepository->store($input);
        $this->ipdPrescriptionRepository->createNotification($input);

        return $this->sendSuccess(__('messages.ipd_prescription').' '.__('messages.common.saved_successfully'));
    }

    /**
     * Display the specified IPD Prescription.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @return array|string
     *
     * @throws Throwable
     */
    public function show(IpdPrescription $ipdPrescription)
    {
        return view('ipd_prescriptions.show_ipd_prescription_data', compact('ipdPrescription'))->render();
    }

    /**
     * Show the form for editing the specified IpdPrescription.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @return JsonResponse
     */
    public function edit(IpdPrescription $ipdPrescription)
    {
        $ipdPrescriptionData = $this->ipdPrescriptionRepository->getIpdPrescriptionData($ipdPrescription);

        return $this->sendResponse($ipdPrescriptionData, 'Prescription retrieved successfully.');
    }

    /**
     * Update the specified IpdPrescriptionItem in storage.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @param  UpdateIpdPrescriptionRequest  $request
     * @return JsonResponse
     */
    public function update(IpdPrescription $ipdPrescription, UpdateIpdPrescriptionRequest $request)
    {
        $ipdPrescription->load('ipdPrescriptionItems');
        $prescriptionMedicineArray = [];
        $inputdoseAndMedicine = [];
        foreach ($ipdPrescription->ipdPrescriptionItems as $prescriptionMedicine) {
            $prescriptionMedicineArray[$prescriptionMedicine->medicine_id] = $prescriptionMedicine->dosage;
        }

        foreach ($request->medicine_id as $key => $value) {
            $inputdoseAndMedicine[$value] = $request->dosage[$key];
        }

        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;
        $arr = collect($input['medicine_id']);
        $duplicateIds = $arr->duplicates();
        foreach ($input['medicine_id'] as $key => $value) {
            $result = array_intersect($prescriptionMedicineArray, $inputdoseAndMedicine);

            $medicine = Medicine::find($input['medicine_id'][$key]);
            $qty = $input['day'][$key] * $input['dose_interval'][$key];
            if(!empty($duplicateIds)){
                foreach($duplicateIds as $key => $value){
                    $medicine = Medicine :: find($duplicateIds[$key]);

                    return $this->sendError(__('messages.medicine_bills.duplicate_medicine'));
                }
            }
            if ($medicine->available_quantity <  $qty && !array_key_exists($input['medicine_id'][$key], $result)) {
                $available = $medicine->available_quantity == null ? 0 : $medicine->available_quantity;

                return $this->sendError('The available quantity of ' . $medicine->name . ' is ' . $available . '.');
            }
        }

        $this->ipdPrescriptionRepository->updateIpdPrescriptionItems($request->all(), $ipdPrescription);

        return $this->sendSuccess(__('messages.ipd_prescription').' '.__('messages.common.updated_successfully'));
    }

    /**
     * Remove the specified IpdPrescriptionItem from storage.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(IpdPrescription $ipdPrescription)
    {
        $ipdPrescription->ipdPrescriptionItems()->delete();
        $ipdPrescription->delete();

        return $this->sendSuccess(__('messages.ipd_prescription').' '.__('messages.common.deleted_successfully'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getMedicineList(Request $request)
    {
        $chargeCategories = $this->ipdPrescriptionRepository->getMedicines($request->get('id'));

        return $this->sendResponse($chargeCategories, 'Retrieved successfully');
    }
}