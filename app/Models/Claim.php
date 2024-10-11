<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Claim extends Model
{
    use HasFactory;
    use SoftDeletes;

    
    protected $fillable = [
        'uuid', 'property_id', 'signature_path_id', 'claim_internal_id',
        'policy_number', 'date_of_loss', 'user_id_ref_by', 'number_of_floors',
        'claim_date', 'type_damage_id', 'claim_status', 'damage_description','claim_number','work_date','scope_of_work',
        'customer_reviewed',
    ];

    

    public function property() {
        return $this->belongsTo(Property::class);
    }

    public function signature()
    {
        return $this->belongsTo(CompanySignature::class, 'signature_path_id');
    }

    public function referredByUser()
    {
        return $this->belongsTo(User::class, 'user_id_ref_by');
    }

    
    public function insuranceCompanyAssignment()
    {
        return $this->hasOne(InsuranceCompanyAssignment::class);
    }

    public function insuranceAdjusterAssignment()
    {
         return $this->hasOne(InsuranceAdjusterAssignment::class);
       
    }

    public function publicAdjusterAssignment()
    {
        return $this->hasOne(PublicAdjusterAssignment::class);
        
    }

    public function publicCompanyAssignment()
    {
        return $this->hasOne(PublicCompanyAssignment::class);  
    }


     public function technicalAssignments()
 {
    return $this->hasMany(TechnicalAssignment::class, 'claim_id');
 }


    public function typeDamage()
    {
        return $this->belongsTo(TypeDamage::class, 'type_damage_id');
    }

    
   public function claimAlliance()
    {
        return $this->hasOne(ClaimAlliance::class);
    }

    public function allianceCompany()
    {
        return $this->belongsToMany(AllianceCompany::class, 'claim_alliances')
                    ->withPivot('assignment_date')
                    ->withTimestamps();
    }
    
     public function customerSignatures()
    {
        return $this->hasMany(ClaimCustomerSignature::class);
    }

    public function scopeSheet()
    {
        return $this->hasMany(ScopeSheet::class);
    }

     public function serviceRequests()
    {
        return $this->belongsToMany(ServiceRequest::class, 'claim_services', 'claim_id', 'service_request_id');
    }

     public function claimAgreement()
    {
        return $this->hasMany(ClaimAgreementFull::class);
    }

    public function claimDocusign()
    {
        return $this->hasMany(DocusignClaim::class);
    }

    public function affidavit()
    {
        return $this->hasOne(AffidavitForm::class, 'claim_id');
    }

    public function claimStatu()
    {
        return $this->belongsTo(ClaimStatu::class, 'claim_status');
    }

    public function causesOfLoss()
    {
        return $this->belongsToMany(CauseOfLoss::class, 'claim_cause_of_loss', 'claim_id', 'cause_of_loss_id');
    }
    
}
