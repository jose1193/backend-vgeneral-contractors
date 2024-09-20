<?php // app/Services/ClaimService.php

namespace App\Services;

use App\Interfaces\ClaimRepositoryInterface;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Database\QueryException;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PublicAdjusterAssignmentNotification;
use App\Mail\TechnicalUserAssignmentNotification;
use App\Jobs\SendPublicAdjusterAssignmentNotification;
use App\Jobs\SendTechnicalUserAssignmentNotification;

use App\Helpers\ImageHelper;

class ClaimService
{
    protected $claimRepositoryInterface;
    protected $baseController;
    protected $cacheKey;
    protected $cacheTime = 720;

    public function __construct(
        ClaimRepositoryInterface $claimRepositoryInterface,
        BaseController $baseController
    ) {
        $this->claimRepositoryInterface = $claimRepositoryInterface;
        $this->baseController = $baseController;
        
    }

    public function all()
    {
        try {
            $this->cacheKey = 'claims_total_list_' . auth()->id();

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->claimRepositoryInterface->getClaimsByUser(auth()->user());
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null; 
            }

            return $data;
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching claims: ' . $e->getMessage(), ['exception' => $e]);
            throw $e; 
        } catch (Exception $e) {
            Log::error('Error occurred while fetching claims: ' . $e->getMessage(), ['exception' => $e]);
            throw $e; 
        }
    }


    public function storeData(array $details, array $technicalIds, array $serviceRequestIds)
    {
        DB::beginTransaction();
    
        try {
        // Generar un UUID para el nuevo claim
        $details['uuid'] = Uuid::uuid4()->toString();
        // Asignar el user_id_ref_by al ID proporcionado o al del usuario autenticado si no se envía
        $details['user_id_ref_by'] = $details['user_id_ref_by'] ?? Auth::id();
        $details['claim_date'] = now();
        $details['claim_status'] = "In Progress";
        $year = date('Y');
        
        // Obtener el último claim del año actual
        $latestClaim = DB::table('claims')
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        // Incrementar el número secuencial
        $sequenceNumber = $latestClaim ? (int)substr($latestClaim->claim_internal_id, -4) + 1 : 1;
        $sequenceFormatted = str_pad($sequenceNumber, 4, '0', STR_PAD_LEFT); // Formato 0001, 0002, etc.
        
        // Construir el ID interno del claim en el formato "VG-CLAIM-2024-0001"
        $details['claim_internal_id'] = "VG-CLAIM-{$year}-{$sequenceFormatted}";
        
        // Aquí llamamos al repositorio de CompanySignature para obtener el signature_path_id
        $signaturePathId = $this->claimRepositoryInterface->getSignaturePathId(); // Asegúrate de tener este método implementado en el repositorio
        
        if (!$signaturePathId) {
            throw new Exception('Signature Path ID not found');
        }
        
        // Asignar el signature_path_id en los detalles del claim
        $details['signature_path_id'] = $signaturePathId;
        
        // Crear el claim en la base de datos
        $claim = $this->claimRepositoryInterface->store($details);
        
        if (!$claim) {
            throw new Exception('Claim not created');
        }
        
        // Lógica para guardar AffidavitForm si los datos están presentes
        if (isset($details['affidavit']) && is_array($details['affidavit'])) {
            // Generar un UUID para el AffidavitForm
            $affidavitData = $details['affidavit'];
            $affidavitData['uuid'] = Uuid::uuid4()->toString();

            // Guardar el AffidavitForm asociado al claim
            $this->claimRepositoryInterface->storeAffidavitForm($affidavitData, $claim->id);
        }
        // Manejar las asignaciones (Alliances, Technicians, etc.)
            //$this->handleAssignments($claim, array_merge($details, [
                //'alliance_company_id' => $alliancesIds
            //]));
            
         // Incluir los technicalIds en el array $details antes de pasarlo
        $details['technical_user_id'] = $technicalIds;

        // Llamar a handleAssignments con los detalles modificados
        $this->handleAssignments($claim, $details);


        // Asociar los service requests con el claim
        $claim->serviceRequests()->attach($serviceRequestIds, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actualizar la caché de datos
        $this->updateDataCache();
        
        DB::commit();
        
        return $claim;
        } catch (Exception $ex) {
        DB::rollBack();
        Log::error('Error occurred while storing claim: ' . $ex->getMessage(), [
            'exception' => $ex,
            'service_request_ids' => $serviceRequestIds,
        ]);
        throw new Exception('Error occurred while storing claim: ' . $ex->getMessage());
        }
    }




   public function updateData(array $updateDetails, string $uuid, array $technicalIds, array $serviceRequestIds)
    {
    DB::beginTransaction();

    try {
        // Obtener el claim existente por UUID
        $existingClaim = $this->claimRepositoryInterface->getByUuid($uuid);

        $updateDetails['user_id_ref_by'] = $updateDetails['user_id_ref_by'] ?? $existingClaim->user_id_ref_by;
        // Actualizar el claim en la base de datos
        $updatedClaim = $this->claimRepositoryInterface->update($updateDetails, $uuid);

        // Actualizar los datos del affidavit si están presentes
        if (isset($updateDetails['affidavit'])) {
            $this->claimRepositoryInterface->updateAffidavitForm($updateDetails['affidavit'], $existingClaim->id);
        }

            // Manejar las asignaciones
            //$this->handleAssignments($updatedClaim, array_merge($updateDetails, [
            //'alliance_company_id' => $alliancesIds
            //]));
            // Incluir los technicalIds en el array $details antes de pasarlo
            $updateDetails['technical_user_id'] = $technicalIds;

            // Llamar a handleAssignments con los detalles modificados
            $this->handleAssignments($updatedClaim, $updateDetails);

           
      
         $existingClaim->serviceRequests()->sync($serviceRequestIds, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

        // Actualizar la caché de datos
        $this->updateDataCache();
        DB::commit();

        return $updatedClaim;
    } catch (Exception $ex) {
        DB::rollBack();
        Log::error('Error occurred while updating claim: ' . $ex->getMessage(), ['exception' => $ex]);
        throw new Exception('Error occurred while updating claim: ' . $ex->getMessage());
    }
    }



    public function showData(string $uuid)
    {
        try {
            $cacheKey = 'claim_' . $uuid;

            $data = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->claimRepositoryInterface->getByUuid($uuid);
            });

            return $data;
        } catch (Exception $ex) {
            Log::error('Error occurred while retrieving claim: ' . $ex->getMessage(), ['exception' => $ex]);
            throw new Exception('Error occurred while retrieving claim: ' . $ex->getMessage());
        }
    }

    public function deleteData(string $uuid)
    {
        DB::beginTransaction();

        try {
            //$existingClaim = $this->claimRepositoryInterface->getByUuid($uuid);

            //if (!$existingClaim || $existingClaim->user_id !== Auth::id()) {
                //throw new Exception('No permission to delete this claim or claim not found.');
            //}

            $data = $this->claimRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('claim_' . $uuid);
            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('Error occurred while deleting claim: ' . $ex->getMessage(), ['exception' => $ex]);
            throw new Exception('Error occurred while deleting claim: ' . $ex->getMessage());
        }
    }

    private function handleAssignments($claim, array $details)
{
    $this->handleInsuranceCompanyAssignment($claim, $details);
    $this->handleInsuranceAdjusterAssignment($claim, $details);
    $this->handlePublicAdjusterAssignment($claim, $details);
    $this->handlePublicCompanyAssignment($claim, $details);
   
    if (isset($details['technical_user_id'])) {
        $this->handleTechnicalUserAssignment($claim, $details);
    }
    
    if (isset($details['alliance_company_id'])) {
        $this->handleAllianceCompanyAssignment($claim, $details);
    }
    
}

private function handleInsuranceCompanyAssignment($claim, array $details)
{
    if (isset($details['insurance_company_id'])) {
        $claim->insuranceCompanyAssignment()->updateOrCreate(
            ['claim_id' => $claim->id],
            ['insurance_company_id' => $details['insurance_company_id'], 'assignment_date' => now()]
        );
    }
}

private function handleInsuranceAdjusterAssignment($claim, array $details)
{
    if (isset($details['insurance_adjuster_id'])) {
        $claim->insuranceAdjusterAssignment()->updateOrCreate(
            ['claim_id' => $claim->id],
            ['insurance_adjuster_id' => $details['insurance_adjuster_id'], 'assignment_date' => now()]
        );
    }
}

private function handlePublicAdjusterAssignment($claim, array $details)
{
    if (isset($details['public_adjuster_id'])) {
        $publicAdjusterAssignment = $claim->publicAdjusterAssignment()->updateOrCreate(
            ['claim_id' => $claim->id],
            ['public_adjuster_id' => $details['public_adjuster_id'], 'assignment_date' => now()]
        );

        $publicAdjuster = User::findOrFail($details['public_adjuster_id']);
        
       // Enviar correo
    Mail::to($publicAdjuster->email)->send(new PublicAdjusterAssignmentNotification($publicAdjuster, $claim));
    
     // Dispatch welcome email
     //SendMailPublicAdjusterAssignmentNotification::dispatch($publicAdjuster, $claim);

    }
}

private function handlePublicCompanyAssignment($claim, array $details)
{
    if (isset($details['public_company_id'])) {
        $claim->publicCompanyAssignment()->updateOrCreate(
            ['claim_id' => $claim->id],
            ['public_company_id' => $details['public_company_id'], 'assignment_date' => now()]
        );
    }
}

    private function handleTechnicalUserAssignment($claim, array $details)
    {
        // Verifica si se envía un array de técnicos
        if (isset($details['technical_user_id']) && is_array($details['technical_user_id'])) {
        $technicalUserIds = $details['technical_user_id'];

        // Si el array está vacío, eliminamos todas las asignaciones
        if (empty($technicalUserIds)) {
            $claim->technicalAssignments()->delete(); // Elimina todas las asignaciones
        } else {
            // Si hay IDs, actualiza o crea las asignaciones
            foreach ($technicalUserIds as $technicalUserId) {
                $technicalAssignment = $claim->technicalAssignments()->updateOrCreate(
                    ['technical_user_id' => $technicalUserId],
                    [
                        'assignment_status' => $details['assignment_status'] ?? 'Pending',
                        'assignment_date' => now(),
                        'work_date' => $details['work_date'] ?? null,
                    ]
                );

                $technicalUser = User::findOrFail($technicalUserId);

                // Enviar correo
                Mail::to($technicalUser->email)->send(new TechnicalUserAssignmentNotification($technicalUser, $claim));
            // Dispatch welcome email
            //SendMailTechnicalUserAssignmentNotification::dispatch($technicalUser, $claim);
            }
            }
        }
    }




    //private function handleAllianceCompanyAssignment($claim, array $details)
    //{
        //$allianceCompanyIds = $details['alliance_company_id'] ?? [];

        // Eliminar asignaciones anteriores
        //$claim->allianceCompanies()->detach();

        // Crear nuevas asignaciones
        //foreach ($allianceCompanyIds as $companyId) {
        //$claim->allianceCompanies()->attach($companyId, ['assignment_date' => now()]);
        //}
    //}

    private function handleAllianceCompanyAssignment($claim, array $details)
    {
        // Asegurarse de que alliance_company_id sea un arreglo
        $allianceCompanyIds = is_array($details['alliance_company_id']) ? $details['alliance_company_id'] : [$details['alliance_company_id']];

        // Eliminar asignaciones anteriores
        $claim->allianceCompanies()->detach();

        // Crear nuevas asignaciones
        foreach ($allianceCompanyIds as $companyId) {
            $claim->allianceCompanies()->attach($companyId, ['assignment_date' => now()]);
        }
    }




    private function updateDataCache()
    {
        $this->cacheKey = 'claims_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->claimRepositoryInterface->index();
            });
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }

    public function restoreData(string $uuid)
    {
        try {
        $data = $this->claimRepositoryInterface->restore($uuid);
        
         // Invalidar el caché del usuario
        $this->baseController->invalidateCache('claim_' . $uuid);

           // Actualizar la caché de la lista de usuarios
            $this->updateDataCache();
        return $data;

        } catch (\Exception $ex) {
        throw new \Exception('Error occurred while retrieving claim: ' . $ex->getMessage());
        }
    }
}
