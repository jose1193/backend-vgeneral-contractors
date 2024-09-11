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
            'signature_path_id' => $this->signature_path_id,
            'type_damage_id' => $this->type_damage_id,
            'user_id_ref_by' => $this->referredByUser->name,
            'claim_internal_id' => $this->claim_internal_id,
            'policy_number' => $this->policy_number,
            'date_of_loss' => $this->date_of_loss,
            'claim_date' => $this->claim_date,
            'claim_status' => $this->claim_status,
            'damage_description' => $this->damage_description,
            'number_of_floors' =>$this->number_of_floors,
            'work_date' =>$this->work_date,
            'created_at' =>  $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),

            // Relaciones opcionales
            'user_ref_by' =>  $this->referredByUser->name . ' ' . $this->referredByUser->last_name,
            'property' => new PropertyResource($this->property), 
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
                    'id' => $serviceRequest->id,
                    'uuid' => $serviceRequest->uuid,
                    'requested_service' => $serviceRequest->requested_service,
                    // Añade aquí cualquier otro campo de ServiceRequest que quieras incluir
                    'created_at' => $serviceRequest->pivot->created_at,
                    'updated_at' => $serviceRequest->pivot->updated_at,
                ];
            }),

            // Agregar los acuerdos de reclamo
        'claim_agreements' => $this->claimAgreement->map(function ($agreement) {
            return [
                'uuid' => $agreement->uuid,
                'claim_id' => (int) $agreement->claim_id,
                'full_pdf_path' => asset($agreement->full_pdf_path),
                'agreement_type' => $agreement->agreement_type,  // Aquí puedes hacer una conversión si es necesario
                'generated_by' => $agreement->generatedBy ? $agreement->generatedBy->name . ' ' . $agreement->generatedBy->last_name : null,
            ];
        }),
        
        ];
    }
}
