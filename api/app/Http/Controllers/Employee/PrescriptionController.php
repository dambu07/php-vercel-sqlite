<?php

namespace App\Http\Controllers\Employee;

use App\Exports\PrescriptionExport;
use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Repositories\PrescriptionRepository;
use Illuminate\Support\Facades\App;

class PrescriptionController extends Controller
{
    private $prescriptionRepository;

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index()
    {
        return view('employee_prescription_list.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Factory|View
     */
    public function show($id)
    {

    /** @var PrescriptionRepository $prescriptionRepository */
    $prescriptionRepository = App::make(PrescriptionRepository::class);
    $data = $prescriptionRepository->getSettingList();

    $prescription = $prescriptionRepository->getData($id);

    $medicines = $prescriptionRepository->getMedicineData($id);

    return view('prescriptions.view', compact('prescription', 'medicines', 'data'));

    }

    /**
     * @return BinaryFileResponse
     */
    public function prescriptionExport()
    {
        return Excel::download(new PrescriptionExport, 'prescriptions-' . time() . '.xlsx');
    }
}
