<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' =>  $this->id,
            'uuid' => $this->uuid,
            'property_id' => (int) $this->property_id,
            'signature_path_id' => (int) $this->signature_path_id,
            'type_damage_id' =>  (int)$this->type_damage_id,
            'user_id_ref_by' => $this->referredByUser->name . ' '. $this->referredByUser->last_name,
            'claim_internal_id' => $this->claim_internal_id,
            'claim_number' => $this->claim_number,
            'policy_number' => $this->policy_number,
            'date_of_loss' => $this->date_of_loss,
            'description_of_loss' => $this->description_of_loss,
            'claim_date' => $this->claim_date,
            'claim_status' => $this->claim_status,
            'damage_description' => $this->damage_description,
            'number_of_floors' =>$this->number_of_floors,
            'work_date' =>$this->work_date,
            'created_at' =>  $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Relaciones opcionales
            'user_ref_by' =>  $this->referredByUser->name . ' ' . $this->referredByUser->last_name,
            'property' => $this->property->property_address. ' ' .$this->property->property_state. ' ' .$this->property->property_city. ' ' .$this->property->property_postal_code. ' ' .$this->property->property_country,
            'customers' => $this->transformCustomers($this->property->customers),
            'signature_path' => asset($this->signature->signature_path),  
            'type_damage' => $this->typeDamage->type_damage_name,

            // Relación de asignaciones
            'insurance_company_assignment' => $this->insuranceCompanyAssignment ? $this->insuranceCompanyAssignment->insuranceCompany->insurance_company_name : null,
            'insurance_adjuster_assignment' => $this->insuranceAdjusterAssignment ? $this->insuranceAdjusterAssignment->insuranceAdjuster->name : null,
            'public_adjuster_assignment' => $this->publicAdjusterAssignment ? $this->publicAdjusterAssignment->publicAdjuster->name : null,
            'public_company_assignment' => $this->publicCompanyAssignment ? $this->publicCompanyAssignment->publicCompany->public_company_name : null,
            //'technical_assignments' => $this->technicalAssignments->map(function ($assignment) {
            //return new UserResource($assignment->technicalUser);
            //}),
          'technical_assignments' => $this->technicalAssignments->map(function ($assignment) {
                return [
                    'id' => (int) $assignment->id,
                    'technical_user_name' => $assignment->technicalUser 
                        ? $assignment->technicalUser->name . ' ' . $assignment->technicalUser->last_name
                        : null,
                    // Otros campos de TechnicalAssignment si es necesario
                ];
            }),
           
           
            'alliance_companies' => new AllianceCompanyResource($this->allianceCompanies), 
            'requested_services' => $this->serviceRequests->map(function ($serviceRequest) {
                return [
                    'id' => (int) $serviceRequest->id,
                    'uuid' => $serviceRequest->uuid,
                    'requested_service' => $serviceRequest->requested_service,
                    // Añade aquí cualquier otro campo de ServiceRequest que quieras incluir
                    'created_at' => $serviceRequest->pivot->created_at,
                    'updated_at' => $serviceRequest->pivot->updated_at,
                ];
            }),
            'affidavit' => $this->affidavit,
            // Agregar los acuerdos de reclamo
        'claim_agreements' => $this->claimAgreement->map(function ($agreement) {
            return [
                'id' => (int) $agreement->id,
                'uuid' => $agreement->uuid,
                'claim_id' => (int) $agreement->claim_id,
                'full_pdf_path' => asset($agreement->full_pdf_path),
                'agreement_type' => $agreement->agreement_type,  // Aquí puedes hacer una conversión si es necesario
                'generated_by' => $agreement->generatedBy ? $agreement->generatedBy->name . ' ' . $agreement->generatedBy->last_name : null,
            ];
        }),
        
        ];
        
    }
    private function transformCustomers($customers)
    {
        return $customers->map(function ($customer) {
            return [
                'id' => (int) $customer->id,
                'name' => $customer->name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'cell_phone' => $customer->cell_phone,
                'home_phone' => $customer->home_phone,
                'occupation' => $customer->occupation,
                // Otros campos de Customer si es necesario
            ];
        })->toArray();
    }
}
