<?php

namespace App\Repositories;

use App;
use App\Models\Bed;
use App\Models\BedAssign;
use App\Models\BedType;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\IpdOperation;
use App\Models\IpdPatientDepartment;
use App\Models\Notification;
use App\Models\OperationCategory;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\IpdCharge;
use App\Models\IpdConsultantRegister;
use App\Models\IpdDiagnosis;
use App\Models\IpdPayment;
use App\Models\IpdPrescription;
use App\Models\IpdTimeline;
use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class IpdPatientDepartmentRepository
 *
 * @version September 8, 2020, 6:42 am UTC
 */
class IpdPatientDepartmentRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'patient_id',
        'ipd_number',
        'height',
        'weight',
        'bp',
        'symptoms',
        'notes',
        'admission_date',
        'case_id',
        'is_old_patient',
        'doctor_id',
        'bed_group_id',
        'bed_id',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return IpdPatientDepartment::class;
    }

    /**
     * @return mixed
     */
    public function getAssociatedData()
    {
        $data['patients'] = Patient::with('patientUser')->get()->where('patientUser.status', '=',
            1)->pluck('patientUser.full_name',
            'id')->sort();
        $data['doctors'] = Doctor::with('doctorUser')->get()->where('doctorUser.status', '=',
            1)->pluck('doctorUser.full_name',
            'id')->sort();
        $data['bedTypes'] = BedType::pluck('title', 'id')->toArray();
        natcasesort($data['bedTypes']);
        $data['ipdNumber'] = $this->model->generateUniqueIpdNumber();

        return $data;
    }

    /**
     * @param int $patientId
     * @return Collection
     */
    public function getPatientCases($patientId)
    {
        return PatientCase::where('patient_id', $patientId)->where('status', 1)->get()->pluck('case_id', 'id');
    }

    /**
     * @param int $bedTypeId
     * @param bool $isEdit
     * @param int $bedId
     * @param int $ipdPatientBedTypeId
     * @return Collection
     */
    public function getPatientBeds($bedTypeId, $isEdit, $bedId, $ipdPatientBedTypeId)
    {
        $beds = null;
        if (!$isEdit) {
            $beds = Bed::orderBy('name')->where('bed_type', $bedTypeId)->where('is_available', 1)->pluck('name', 'id');
        } else {
            $beds = Bed::orderBy('name')->where('bed_type', $bedTypeId)->where('is_available', 1);
            if ($bedTypeId == $ipdPatientBedTypeId) {
                $beds->orWhere('id', $bedId);
            }
            $beds = $beds->pluck('name', 'id');
        }

        return $beds;
    }

    /**
     * @return Collection
     */
    public function getDoctorsData()
    {
        return Doctor::with('doctorUser')->get()->where('doctorUser.status', '=', 1)->pluck('doctorUser.full_name',
            'id');
    }

    /**
     * @return Collection
     */
    public function getPatientsData()
    {
        return Patient::with('patientUser')->get()->where('patientUser.status', '=', 1)->pluck('patientUser.full_name',
            'id');
    }

    /**
     * @return array
     */
    public function getDoctorsList()
    {
        $result = Doctor::with('doctorUser')->get()
            ->where('doctorUser.status', '=', 1)->pluck('doctorUser.full_name', 'id')->toArray();

        $doctors = [];
        foreach ($result as $key => $item) {
            $doctors[] = [
                'key'   => $key,
                'value' => $item,
            ];
        }

        return $doctors;
    }

    /**
     * @return Collection
     */
    public function getMedicinesCategoriesData()
    {
        return Category::where('is_active', '=', 1)->pluck('name', 'id');
    }

    /**
     * @return array
     */
    public function getMedicineCategoriesList()
    {
        $result = Category::where('is_active', '=', 1)->pluck('name', 'id')->toArray();

        $medicineCategories = [];
        foreach ($result as $key => $item) {
            $medicineCategories[] = [
                'key'   => $key,
                'value' => $item,
            ];
        }

        return $medicineCategories;
    }

    /**
     * @param array $input
     * @return bool
     */
    public function store($input)
    {
//        try {
        $input['is_old_patient'] = isset($input['is_old_patient']) ? true : false;
        $ipdPatientDepartment = IpdPatientDepartment::create($input);
        $bedAssignData = [
            'bed_id'                    => $input['bed_id'],
            'patient_id'                => $input['patient_id'],
            //                'ipd_patient_id' => $ipdPatientDepartment->patientCase ? $ipdPatientDepartment->patientCase->case_id : '',
            'assign_date'               => $input['admission_date'],
            'ipd_patient_department_id' => $ipdPatientDepartment->id,
            'status'                    => true,
        ];
        /** @var BedAssignRepository $bedAssign */
        $bedAssign = App::make(BedAssignRepository::class);
        $bedAssign->store($bedAssignData);
//        } catch (Exception $e) {
//            throw new UnprocessableEntityHttpException($e->getMessage());
//        }

        return true;
    }

    /**
     * @param array $input
     * @param IpdPatientDepartment $ipdPatientDepartment
     * @return bool
     */
    public function updateIpdPatientDepartment($input, $ipdPatientDepartment)
    {
        try {
            $input['is_old_patient'] = isset($input['is_old_patient']) ? true : false;
            $bedId = $ipdPatientDepartment->bed_id;

            /** @var IpdPatientDepartment $ipdPatientDepartment */
            $ipdPatientDepartment = $this->update($input, $ipdPatientDepartment->id);

            $bedAssignData = [
                'bed_id'      => $input['bed_id'],
                'patient_id'  => $input['patient_id'],
                'case_id'     => $ipdPatientDepartment->patientCase ? $ipdPatientDepartment->patientCase->case_id : '',
                'assign_date' => $input['admission_date'],
                'status'      => true,
            ];

            /** @var BedAssign $bedAssign */
            $bedAssignUpdate = BedAssign::whereBedId($bedId)->first();

            /** @var BedAssignRepository $bedAssign */
            $bedAssign = App::make(BedAssignRepository::class);
            if (empty($bedAssignUpdate)) {
                $bedAssigns = BedAssign::create($bedAssignData);
                BedAssign::where('id',
                    $bedAssigns->id)->update(['ipd_patient_department_id' => $ipdPatientDepartment->id]);
            } else {
                $bedAssign->update($bedAssignData, $bedAssignUpdate);
            }
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return true;
    }

    /**
     * @param IpdPatientDepartment $ipdPatientDepartment
     * @throws Exception
     * @return bool
     *
     */
    public function deleteIpdPatientDepartment($ipdPatientDepartment)
    {
        $ipdPatientDepartment->bed->update(['is_available' => 1]);
        if ($ipdPatientDepartment->bedAssign) {
            BedAssign::where('id', $ipdPatientDepartment->bedAssign->id)->delete();
        }
        $ipdPatientDepartment->delete();

        return true;
    }

    /**
     * @return mixed
     */
    public function getSyncListForCreate()
    {
        $data['setting'] = Setting::pluck('value', 'key');

        return $data;
    }

    /**
     * @param array $input
     */
    public function createNotification($input)
    {
        try {
            $patient = Patient::with('patientUser')->where('id', $input['patient_id'])->first();
            addNotification([
                Notification::NOTIFICATION_TYPE['IPD Patient'],
                $patient->user_id,
                Notification::NOTIFICATION_FOR[Notification::PATIENT],
                $patient->patientUser->full_name.' your IPD record has been created.',
            ]);
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function getConsultantRegister($id)
    {
        $consultantRegister = IpdConsultantRegister::whereHas('doctor.doctorUser')->with('doctor.doctorUser')->where('ipd_patient_department_id', $id)->latest()->take(5)->get();

        return $consultantRegister;
    }

    public function getConsultantDoctor($id)
    {
        $consultantRegister = IpdConsultantRegister::whereHas('doctor.doctorUser')->with('doctor.doctorUser')->where('ipd_patient_department_id', $id)->groupBy('doctor_id')->latest()->take(5)->get();

        return $consultantRegister;
    }

    public function getIPDOperation($id)
    {
        $ipdOperation = IpdOperation::where('ipd_patient_department_id', $id)->latest()->take(5)->get();

        return $ipdOperation;
    }

    /**
     * @param int $id
     *
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getIPDTimeline(int $id): \Illuminate\Database\Eloquent\Collection|array
    {
        if (Auth::user()->hasRole('Admin')) {
            return IpdTimeline::where('ipd_patient_department_id', $id)->latest()->take(2)->get();
        }

        return IpdTimeline::where('ipd_patient_department_id', $id)->latest()->take(2)->visible()->get();
    }

    /**
     * @param int $id
     *
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getIPDPrescription(int $id): \Illuminate\Database\Eloquent\Collection|array
    {
        return IpdPrescription::with('patient')->where('ipd_patient_department_id', $id)->latest()->take(5)->get();
    }

    /**
     * @param int $id
     *
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getIPDCharges(int $id): \Illuminate\Database\Eloquent\Collection|array
    {
        return IpdCharge::with(['chargecategory', 'charge'])->where('ipd_patient_department_id', $id)->latest()->take(5)->get();
    }

    /**
     * @param int $id
     *
     *
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getIPDPayment(int $id): array|\Illuminate\Database\Eloquent\Collection|Collection
    {
        return IpdPayment::whereIpdPatientDepartmentId($id)->latest()->take(5)->get();
    }

    /**
     * @param int $id
     *
     *
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getIPDDiagnosis(int $id): array|\Illuminate\Database\Eloquent\Collection|Collection
    {
        return IpdDiagnosis::whereIpdPatientDepartmentId($id)->latest()->take(5)->get();
    }

    public function getOperationCategoryList()
    {
        return OperationCategory::get()->pluck('name', 'id')->toArray();
    }

    public function getDoseDurationList()
    {
        $result = Prescription::DOSE_DURATION;

        $doseDuration = [];
        foreach ($result as $key => $item) {
            $doseDuration[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $doseDuration;
    }

    public function getDoseIntervalList()
    {
        $result = Prescription::DOSE_INTERVAL;

        $doseInterval = [];
        foreach ($result as $key => $item) {
            $doseInterval[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $doseInterval;
    }

    public function getMealList()
    {
        $result = Prescription::MEAL_ARR;

        $meal = [];
        foreach ($result as $key => $item) {
            $meal[] = [
                'key' => $key,
                'value' => $item,
            ];
        }

        return $meal;
    }
}
