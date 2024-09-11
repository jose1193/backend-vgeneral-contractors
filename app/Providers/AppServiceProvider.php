<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\UsersRepository;
use App\Repositories\InsuranceCompanyRepository;
use App\Repositories\PublicCompanyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\PropertyRepository;
use App\Repositories\CategoryProductRepository;
use App\Repositories\ProductRepository;
use App\Repositories\InsuranceAdjusterRepository;
use App\Repositories\PublicAdjusterRepository;
use App\Repositories\CompanySignatureRepository;
use App\Repositories\AllianceCompanyRepository;
use App\Repositories\ZoneRepository;
use App\Repositories\TypeDamageRepository;
use App\Repositories\ClaimRepository;
use App\Repositories\FileEsxRepository;
use App\Repositories\ClaimCustomerSignatureRepository;
use App\Repositories\ClaimAgreementPreviewRepository;
use App\Repositories\DocumentTemplateRepository;
use App\Repositories\DocumentTemplateAllianceRepository;
use App\Repositories\ClaimAgreementFullRepository;
use App\Repositories\CustomerSignatureRepository;
use App\Repositories\ScopeSheetRepository;
use App\Repositories\ScopeSheetZoneRepository;
use App\Repositories\ScopeSheetPresentationRepository;
use App\Repositories\ScopeSheetZonePhotoRepository;
use App\Repositories\ScopeSheetExportRepository;
use App\Repositories\ServiceRequestRepository;
use App\Repositories\SalespersonSignatureRepository;
use App\Repositories\DocuSignRepository;


use App\Interfaces\TypeDamageRepositoryInterface;
use App\Interfaces\UsersRepositoryInterface;
use App\Interfaces\InsuranceCompanyRepositoryInterface;
use App\Interfaces\PublicCompanyRepositoryInterface;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\Interfaces\CategoryProductRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\InsuranceAdjusterRepositoryInterface;
use App\Interfaces\PublicAdjusterRepositoryInterface;
use App\Interfaces\CompanySignatureRepositoryInterface;
use App\Interfaces\AllianceCompanyRepositoryInterface;
use App\Interfaces\ZoneRepositoryInterface;
use App\Interfaces\ClaimRepositoryInterface;
use App\Interfaces\FileEsxRepositoryInterface;
use App\Interfaces\ClaimCustomerSignatureRepositoryInterface;
use App\Interfaces\ClaimAgreementPreviewRepositoryInterface;
use App\Interfaces\DocumentTemplateRepositoryInterface;
use App\Interfaces\DocumentTemplateAllianceRepositoryInterface;
use App\Interfaces\ClaimAgreementFullRepositoryInterface;
use App\Interfaces\CustomerSignatureRepositoryInterface;
use App\Interfaces\ScopeSheetRepositoryInterface;
use App\Interfaces\ScopeSheetZoneRepositoryInterface;
use App\Interfaces\ScopeSheetPresentationRepositoryInterface;
use App\Interfaces\ScopeSheetZonePhotoRepositoryInterface;
use App\Interfaces\ScopeSheetExportRepositoryInterface;
use App\Interfaces\ServiceRequestRepositoryInterface;
use App\Interfaces\SalespersonSignatureRepositoryInterface;
use App\Interfaces\DocuSignRepositoryInterface;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    
    public function register(): void
    {
        $this->app->bind(UsersRepositoryInterface::class, UsersRepository::class);
        $this->app->bind(TypeDamageRepositoryInterface::class,TypeDamageRepository::class);
        $this->app->bind(InsuranceCompanyRepositoryInterface::class,InsuranceCompanyRepository::class);
        $this->app->bind(PublicCompanyRepositoryInterface::class,PublicCompanyRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class,CustomerRepository::class);
        $this->app->bind(PropertyRepositoryInterface::class,PropertyRepository::class);
        $this->app->bind(CategoryProductRepositoryInterface::class,CategoryProductRepository::class);
        $this->app->bind(ProductRepositoryInterface::class,ProductRepository::class);
        $this->app->bind(InsuranceAdjusterRepositoryInterface::class,InsuranceAdjusterRepository::class);
        $this->app->bind(PublicAdjusterRepositoryInterface::class,PublicAdjusterRepository::class);
        $this->app->bind(CompanySignatureRepositoryInterface::class,CompanySignatureRepository::class);
        $this->app->bind(AllianceCompanyRepositoryInterface::class,AllianceCompanyRepository::class);    
        $this->app->bind(ZoneRepositoryInterface::class,ZoneRepository::class);   
        $this->app->bind(ClaimRepositoryInterface::class,ClaimRepository::class);    
        $this->app->bind(FileEsxRepositoryInterface::class,FileEsxRepository::class);
        $this->app->bind(ClaimCustomerSignatureRepositoryInterface::class,ClaimCustomerSignatureRepository::class);
        $this->app->bind(ClaimAgreementPreviewRepositoryInterface::class,ClaimAgreementPreviewRepository::class);
        $this->app->bind(DocumentTemplateRepositoryInterface::class,DocumentTemplateRepository::class);
        $this->app->bind(DocumentTemplateAllianceRepositoryInterface::class,DocumentTemplateAllianceRepository::class);
        $this->app->bind(ClaimAgreementFullRepositoryInterface::class,ClaimAgreementFullRepository::class);
        $this->app->bind(CustomerSignatureRepositoryInterface::class,CustomerSignatureRepository::class);
        $this->app->bind(ScopeSheetRepositoryInterface::class,ScopeSheetRepository::class);
        $this->app->bind(ScopeSheetZoneRepositoryInterface::class,ScopeSheetZoneRepository::class);
        $this->app->bind(ScopeSheetPresentationRepositoryInterface::class,ScopeSheetPresentationRepository::class);
        $this->app->bind(ScopeSheetZonePhotoRepositoryInterface::class,ScopeSheetZonePhotoRepository::class);
        $this->app->bind(ScopeSheetExportRepositoryInterface::class,ScopeSheetExportRepository::class);
        $this->app->bind(ServiceRequestRepositoryInterface::class,ServiceRequestRepository::class);
        $this->app->bind(SalespersonSignatureRepositoryInterface::class, SalespersonSignatureRepository::class);
        $this->app->bind(DocuSignRepositoryInterface::class, DocuSignRepository::class);
    
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
