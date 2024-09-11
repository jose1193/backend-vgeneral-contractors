<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use HasRoles;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'username',
        'register_date',
        'uuid',
        'email',
        'password',
        'phone',
        'address',    // Add the new fields here
        'zip_code',
        'city',
        'country',
        'gender',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function providers()
    {
        return $this->hasMany(Provider::class,'user_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class,'user_id');
    }

    public function insuranceAdjusterUser()
    {
        return $this->hasMany(InsuranceAdjusterAssignment::class,'insurance_adjuster_id');
    }

    public function publicAdjusterUser()
    {
        return $this->hasMany(PublicAdjusterAssignment::class,'public_adjuster_id');
    }

    public function allianceCompany()
    {
        return $this->hasMany(AllianceCompany::class,'user_id');
    }


    public function claim()
    {
        return $this->hasMany(Claim::class,'user_id_ref_by');
    }

     public function technicalAssignments()
    {
        return $this->hasMany(TechnicalAssignment::class, 'technical_user_id');
    }


    public function uploadedFiles()
    {
        return $this->hasMany(FilesEsx::class, 'uploaded_by');
    }


     public function assignedFiles()
    {
        return $this->hasManyThrough(File::class, FileAssignmentEsx::class, 'public_adjuster_id', 'id', 'id', 'file_id');
    }

    public function claimAgreementPreviews()
    {
        return $this->hasMany(ClaimAgreementPreview::class, 'generated_by');
    }

    public function documentTemplate()
    {
        return $this->hasMany(DocumentTemplate::class, 'uploaded_by');
    }
    
    public function documentTemplateAlliance()
    {
        return $this->hasMany(DocumentTemplateAlliance::class, 'uploaded_by');
    }

    public function claimAgreementFulls()
    {
        return $this->hasMany(ClaimAgreementFull::class, 'generated_by');
    }

    public function scopeSheet()
    {
        return $this->hasMany(ScopeSheet::class, 'generated_by');
    }

    public function scopeSheetExportFull()
    {
        return $this->hasMany(ScopeSheetExport::class, 'generated_by');
    }

    public function customerSignature()
    {
        return $this->hasMany(CustomerSignature::class,'user_id_ref_by');
    }

    public function sellerSignature()
    {
        return $this->hasMany(SalespersonSignature::class,'user_id_ref_by');
    }

    public function docusign()
    {
        return $this->hasMany(DocusignClaim::class, 'generated_by');
    }

    public function connectedDocusign()
    {
        return $this->hasMany(DocusignToken::class, 'connected_by');
    }

}
