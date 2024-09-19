<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\PermissionController;
//use App\Http\Controllers\Api\ErrorController;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ProfilePhotoController;

use App\Http\Controllers\CheckUsernameController;
use App\Http\Controllers\CheckEmailController;
use App\Http\Controllers\BiometricAuthController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\CreateUserController;
use App\Http\Controllers\PasswordResetUserController;
use App\Http\Controllers\TypeDamageController;
use App\Http\Controllers\InsuranceCompanyController;
use App\Http\Controllers\PublicCompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InsuranceAdjusterController;
use App\Http\Controllers\PublicAdjusterController;
use App\Http\Controllers\CompanySignatureController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\FileEsxController;
use App\Http\Controllers\ClaimCustomerSignatureController;
use App\Http\Controllers\ClaimAgreementPreviewController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\DocumentTemplateAllianceController;
use App\Http\Controllers\ClaimAgreementFullController;
use App\Http\Controllers\CustomerSignatureController;
use App\Http\Controllers\ScopeSheetController;
use App\Http\Controllers\ScopeSheetZoneController;
use App\Http\Controllers\ScopeSheetPresentationController;
use App\Http\Controllers\ScopeSheetZonePhotoController;
use App\Http\Controllers\ScopeSheetExportController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\SalespersonSignatureController;
use App\Http\Controllers\AllianceCompanyController;

use App\Http\Controllers\DocuSignController;



//Route::get('/user', function (Request $request) {
    //return $request->user();
//})->middleware('auth:sanctum');

Route::post('login', [AuthController::class, 'login']);

Route::post('/register', [CreateUserController::class, 'store']);

Route::get('/username-available/{username}', [CheckUsernameController::class, 'checkUsernameAvailability']);

Route::get('/email-available/{email}', [CheckEmailController::class, 'checkEmailAvailability']);

// Route related to User Social Login
Route::post('/social-login', [SocialLoginController::class, 'handleProviderCallback']);


Route::controller(PasswordResetUserController::class)->group(function () {
    Route::post('/forgot-password', 'store'); 
    Route::post('/enter-pin', 'verifyResetPassword');
    Route::post('/reset-password', 'updatePassword');  
   
});


//Route::controller(RegisterController::class)->group(function(){
    //Route::post('register', 'register');
    //Route::post('login', 'login');
//});



Route::middleware(['auth:sanctum','handle.notfound'])->group(function() {
    //Route::get('/user', function (Request $request) {
        //return $request->user();
    //});

    // Rutas protegidas por autenticación y verificación
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    //Route::get('/users', [AuthController::class, 'getUsers']);
    Route::post('update-password', [AuthController::class, 'updatePassword']);
    Route::get('/username-check/{username}', [AuthController::class, 'checkUsernameAvailability']);
    Route::get('/email-check/{email}', [AuthController::class, 'checkEmailAvailability']);

    //Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('update-profile', [CreateUserController::class, 'update']);
    Route::post('update-profile-photo', [ProfilePhotoController::class, 'update']);
    

    // Rutas relacionadas con roles
    Route::get('roles-list', [RoleController::class, 'index']); // Obtener una lista de roles
    Route::post('roles', [RoleController::class, 'store']); // Crear un nuevo rol
    Route::get('roles/{id}', [RoleController::class, 'show']); // Mostrar un rol específico
    Route::put('roles-update/{id}', [RoleController::class, 'update']); // Actualizar un rol existente
    Route::delete('roles-delete/{id}', [RoleController::class, 'destroy']); // Eliminar un rol existente
    Route::get('roles-permissions', [RoleController::class, 'create']); // Mostrar listado de permisos
    Route::get('roles/{id}/edit', [RoleController::class, 'edit']); // Mostrar listado de roles y permisos del usuario a editar

    
    // Routes related to Users
    Route::prefix('users')->group(function () {
    Route::get('/', [UsersController::class, 'index']);    
    Route::post('/store', [UsersController::class, 'store']); 
    Route::get('/{uuid}', [UsersController::class, 'show']); 
    Route::put('/update/{uuid}', [UsersController::class, 'update']); 
    Route::post('/update-images/{uuid}/', [UsersController::class, 'updateImage']); 
    Route::delete('delete/{uuid}', [UsersController::class, 'destroy']);
    Route::put('restore/{uuid}', [UsersController::class, 'restore']);  
    Route::get('/users-roles/list/{role}', [UsersController::class, 'getUsersRoles']);
    }); 
    


    // Rutas relacionadas con permisos
    Route::get('permissions-list', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);
    Route::get('permissions/{id}', [PermissionController::class, 'show']);
    Route::put('permissions-update/{id}', [PermissionController::class, 'update']);
    Route::delete('permissions-delete/{id}', [PermissionController::class, 'destroy']);
    Route::get('permissions/create', [PermissionController::class, 'create']);
    Route::get('permissions/{id}/edit', [PermissionController::class, 'edit']);

    
   
    // Routes related to Biometric Login
    Route::post('/biometric-login', [BiometricAuthController::class, 'store']);

    // Routes related to Type Damages
    Route::prefix('type-damage')->group(function () {
    Route::get('/', [TypeDamageController::class, 'index']);    
    Route::post('/store', [TypeDamageController::class, 'store']); 
    Route::get('/{uuid}', [TypeDamageController::class, 'show']); 
    Route::patch('/update/{uuid}', [TypeDamageController::class, 'update']); 
    Route::delete('delete/{uuid}', [TypeDamageController::class, 'destroy']);

    
    }); 

      // Routes related to Alliance Company
    Route::prefix('alliance-company')->group(function () {
    Route::get('/', [AllianceCompanyController::class, 'index']);    
    Route::post('/store', [AllianceCompanyController::class, 'store']); 
    Route::get('/{uuid}', [AllianceCompanyController::class, 'show']); 
    Route::put('/update/{uuid}', [AllianceCompanyController::class, 'update']); 
    Route::delete('delete/{uuid}', [AllianceCompanyController::class, 'destroy']);
    
    }); 

    // Routes related to Insurance Company
    Route::prefix('insurance-company')->group(function () {
    Route::get('/', [InsuranceCompanyController::class, 'index']);    
    Route::post('/store', [InsuranceCompanyController::class, 'store']); 
    Route::get('/{uuid}', [InsuranceCompanyController::class, 'show']); 
    Route::put('/update/{uuid}', [InsuranceCompanyController::class, 'update']); 
    Route::delete('delete/{uuid}', [InsuranceCompanyController::class, 'destroy']);
    
    }); 


    // Routes related to Public Company
    Route::prefix('public-company')->group(function () {
    Route::get('/', [PublicCompanyController::class, 'index']);    
    Route::post('/store', [PublicCompanyController::class, 'store']); 
    Route::get('/{uuid}', [PublicCompanyController::class, 'show']); 
    Route::put('/update/{uuid}', [PublicCompanyController::class, 'update']); 
    Route::delete('delete/{uuid}', [PublicCompanyController::class, 'destroy']);
    
    }); 

     // Routes related to Customer
    Route::prefix('customer')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);    
    Route::post('/store', [CustomerController::class, 'store']); 
    Route::get('/{uuid}', [CustomerController::class, 'show']); 
    Route::put('/update/{uuid}', [CustomerController::class, 'update']); 
    Route::delete('/delete/{uuid}', [CustomerController::class, 'destroy']);
    Route::put('/restore/{uuid}', [CustomerController::class, 'restore']); 
    });

    // Routes related to CustomerProperty
    Route::prefix('properties')->group(function () {
    Route::get('/', [PropertyController::class, 'index']);    
    Route::post('/store', [PropertyController::class, 'store']); 
    Route::get('/{uuid}', [PropertyController::class, 'show']); 
    Route::put('/update/{uuid}', [PropertyController::class, 'update']); 
    Route::delete('/delete/{uuid}', [PropertyController::class, 'destroy']);
    });


    // Routes related to Product Category
    Route::prefix('product-category')->group(function () {
    Route::get('/', [CategoryProductController::class, 'index']);    
    Route::post('/store', [CategoryProductController::class, 'store']); 
    Route::get('/{uuid}', [CategoryProductController::class, 'show']); 
    Route::put('/update/{uuid}', [CategoryProductController::class, 'update']); 
    Route::delete('/delete/{uuid}', [CategoryProductController::class, 'destroy']);

    });

     // Routes related to Product
    Route::prefix('product')->group(function () {
    Route::get('/', [ProductController::class, 'index']);    
    Route::post('/store', [ProductController::class, 'store']); 
    Route::get('/{uuid}', [ProductController::class, 'show']); 
    Route::put('/update/{uuid}', [ProductController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ProductController::class, 'destroy']);
    Route::put('/restore/{uuid}', [ProductController::class, 'restore']); 
    });

     // Routes related to Insurance Adjuster
    Route::prefix('insurance-adjuster')->group(function () {
    Route::get('/', [InsuranceAdjusterController::class, 'index']);    
    Route::post('/store', [InsuranceAdjusterController::class, 'store']); 
    Route::get('/{uuid}', [InsuranceAdjusterController::class, 'show']); 
    Route::put('/update/{uuid}', [InsuranceAdjusterController::class, 'update']); 
    Route::delete('/delete/{uuid}', [InsuranceAdjusterController::class, 'destroy']);
   
    });

     // Routes related to Public Adjuster
    Route::prefix('public-adjuster')->group(function () {
    Route::get('/', [PublicAdjusterController::class, 'index']);    
    Route::post('/store', [PublicAdjusterController::class, 'store']); 
    Route::get('/{uuid}', [PublicAdjusterController::class, 'show']); 
    Route::put('/update/{uuid}', [PublicAdjusterController::class, 'update']); 
    Route::delete('/delete/{uuid}', [PublicAdjusterController::class, 'destroy']);
   
    });

     // Routes related to Company Signature
    Route::prefix('company-signature')->group(function () {
    Route::get('/', [CompanySignatureController::class, 'index']);    
    Route::post('/store', [CompanySignatureController::class, 'store']); 
    Route::get('/{uuid}', [CompanySignatureController::class, 'show']); 
    Route::put('/update/{uuid}', [CompanySignatureController::class, 'update']); 
    Route::delete('/delete/{uuid}', [CompanySignatureController::class, 'destroy']);
   
    });

     // Routes related to Zone
    Route::prefix('zone')->group(function () {
    Route::get('/', [ZoneController::class, 'index']);    
    Route::post('/store', [ZoneController::class, 'store']); 
    Route::get('/{uuid}', [ZoneController::class, 'show']); 
    Route::put('/update/{uuid}', [ZoneController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ZoneController::class, 'destroy']);
    Route::put('/restore/{uuid}', [ZoneController::class, 'restore']); 
    });

    // Routes related to Claim
    Route::prefix('claim')->group(function () {
    Route::get('/', [ClaimController::class, 'index']);    
    Route::post('/store', [ClaimController::class, 'store']); 
    Route::get('/{uuid}', [ClaimController::class, 'show']); 
    Route::put('/update/{uuid}', [ClaimController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ClaimController::class, 'destroy']);
    Route::put('restore/{uuid}', [ClaimController::class, 'restore']);  
    });

    // Routes related to Customer Signature
    Route::prefix('customer-signature2')->group(function () {
    Route::get('/', [ClaimCustomerSignatureController::class, 'index']);    
    Route::post('/store', [ClaimCustomerSignatureController::class, 'store']); 
    Route::get('/{uuid}', [ClaimCustomerSignatureController::class, 'show']); 
    Route::put('/update/{uuid}', [ClaimCustomerSignatureController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ClaimCustomerSignatureController::class, 'destroy']);
    
    });

     // Routes related to Customer Signature
    Route::prefix('customer-signature')->group(function () {
    Route::get('/', [CustomerSignatureController::class, 'index']);    
    Route::post('/store', [CustomerSignatureController::class, 'store']); 
    Route::get('/{uuid}', [CustomerSignatureController::class, 'show']); 
    Route::put('/update/{uuid}', [CustomerSignatureController::class, 'update']); 
    Route::delete('/delete/{uuid}', [CustomerSignatureController::class, 'destroy']);
    
    });


    // Routes related to Claim Agreement Preview
    //Route::prefix('claim-agreement')->group(function () {
    //Route::get('/', [ClaimAgreementPreviewController::class, 'index']);    
    //Route::post('/store', [ClaimAgreementPreviewController::class, 'store']); 
    //Route::get('/{uuid}', [ClaimAgreementPreviewController::class, 'show']); 
    //Route::put('/update/{uuid}', [ClaimAgreementPreviewController::class, 'update']); 
    //Route::delete('/delete/{uuid}', [ClaimAgreementPreviewController::class, 'destroy']);
    
    //});

    // Routes related to Claim Agreement Preview
    Route::prefix('claim-agreement')->group(function () {
    Route::get('/', [ClaimAgreementFullController::class, 'index']);    
    Route::post('/store', [ClaimAgreementFullController::class, 'store']); 
    Route::get('/{uuid}', [ClaimAgreementFullController::class, 'show']); 
    Route::put('/update/{uuid}', [ClaimAgreementFullController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ClaimAgreementFullController::class, 'destroy']);
    
    });

    // Routes related to File Esx
    Route::prefix('file-esx')->group(function () {
    Route::get('/', [FileEsxController::class, 'index']);    
    Route::post('/store', [FileEsxController::class, 'store']); 
    Route::get('/{uuid}', [FileEsxController::class, 'show']); 
    Route::post('/update/{uuid}', [FileEsxController::class, 'update']); 
    Route::delete('/delete/{uuid}', [FileEsxController::class, 'destroy']);
    
    });

    // Routes related to Document Templates
    Route::prefix('document-template')->group(function () {
    Route::get('/', [DocumentTemplateController::class, 'index']);    
    Route::post('/store', [DocumentTemplateController::class, 'store']); 
    Route::get('/{uuid}', [DocumentTemplateController::class, 'show']); 
    Route::put('/update/{uuid}', [DocumentTemplateController::class, 'update']); 
    Route::delete('/delete/{uuid}', [DocumentTemplateController::class, 'destroy']);
    
    });

    // Routes related to Document Templates
    Route::prefix('document-template-alliance')->group(function () {
    Route::get('/', [DocumentTemplateAllianceController::class, 'index']);    
    Route::post('/store', [DocumentTemplateAllianceController::class, 'store']); 
    Route::get('/{uuid}', [DocumentTemplateAllianceController::class, 'show']); 
    Route::put('/update/{uuid}', [DocumentTemplateAllianceController::class, 'update']); 
    Route::delete('/delete/{uuid}', [DocumentTemplateAllianceController::class, 'destroy']);
    
    });

     // Routes related to Scope Sheet
    Route::prefix('scope-sheet')->group(function () {
    Route::get('/', [ScopeSheetController::class, 'index']);    
    Route::post('/store', [ScopeSheetController::class, 'store']); 
    Route::get('/{uuid}', [ScopeSheetController::class, 'show']); 
    Route::put('/update/{uuid}', [ScopeSheetController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ScopeSheetController::class, 'destroy']);
    
    });

    // Routes related to Scope Sheet Zone
    Route::prefix('scope-sheet-zone')->group(function () {
    Route::get('/', [ScopeSheetZoneController::class, 'index']);    
    Route::post('/store', [ScopeSheetZoneController::class, 'store']); 
    Route::get('/{uuid}', [ScopeSheetZoneController::class, 'show']); 
    Route::put('/update/{uuid}', [ScopeSheetZoneController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ScopeSheetZoneController::class, 'destroy']);
    
    });

    // Routes related to Scope Sheet Zone Photo
    Route::prefix('scope-sheet-zone-photo')->group(function () {
    Route::get('/', [ScopeSheetZonePhotoController::class, 'index']);    
    Route::post('/store', [ScopeSheetZonePhotoController::class, 'store']); 
    Route::get('/{uuid}', [ScopeSheetZonePhotoController::class, 'show']); 
    Route::post('/update/{uuid}', [ScopeSheetZonePhotoController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ScopeSheetZonePhotoController::class, 'destroy']);
    Route::put('/reorder-images', [ScopeSheetZonePhotoController::class, 'reorderImages']);
    
    });

    // Routes related to Scope Sheet Zone Photo
    Route::prefix('scope-sheet-presentation')->group(function () {
    Route::get('/', [ScopeSheetPresentationController::class, 'index']);    
    Route::post('/store', [ScopeSheetPresentationController::class, 'store']); 
    Route::get('/{uuid}', [ScopeSheetPresentationController::class, 'show']); 
    Route::post('/update/{uuid}', [ScopeSheetPresentationController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ScopeSheetPresentationController::class, 'destroy']);
    Route::put('/reorder-images', [ScopeSheetPresentationController::class, 'reorderImages']);
    
    });

     // Routes related to Scope Sheet Export
    Route::prefix('scope-sheet-export')->group(function () {
    Route::get('/', [ScopeSheetExportController::class, 'index']);    
    Route::post('/store', [ScopeSheetExportController::class, 'store']); 
    Route::get('/{uuid}', [ScopeSheetExportController::class, 'show']); 
    Route::post('/update/{uuid}', [ScopeSheetExportController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ScopeSheetExportController::class, 'destroy']);
   
    
    });


    // Routes related to Service Request
    Route::prefix('service-request')->group(function () {
    Route::get('/', [ServiceRequestController::class, 'index']);    
    Route::post('/store', [ServiceRequestController::class, 'store']); 
    Route::get('/{uuid}', [ServiceRequestController::class, 'show']); 
    Route::put('/update/{uuid}', [ServiceRequestController::class, 'update']); 
    Route::delete('/delete/{uuid}', [ServiceRequestController::class, 'destroy']);
   
    
    });


     // Routes related to Sales person Signature 
    Route::prefix('sales-person-signature')->group(function () {
    Route::get('/', [SalespersonSignatureController::class, 'index']);    
    Route::post('/store', [SalespersonSignatureController::class, 'store']); 
    Route::get('/{uuid}', [SalespersonSignatureController::class, 'show']); 
    Route::put('/update/{uuid}', [SalespersonSignatureController::class, 'update']); 
    Route::delete('/delete/{uuid}', [SalespersonSignatureController::class, 'destroy']);

    });
 // Routes related to Sales person Signature 
    Route::prefix('docusign')->group(function () {
    Route::get('/', [DocuSignController::class, 'index']);    
    //Route::post('/store', [DocuSignController::class, 'store']); 
    //Route::get('/{uuid}', [DocuSignController::class, 'show']); 
    //Route::put('/update/{uuid}', [DocuSignController::class, 'update']); 
    Route::delete('/delete/{uuid}', [DocuSignController::class, 'destroy']);
    Route::post('/connect', [DocuSignController::class, 'connectDocusign']);
    Route::post('/callback', [DocuSignController::class, 'callbackDocusign']); 
    Route::post('/sign', [DocuSignController::class, 'validateDocument']); 
    Route::post('/check-document', [DocuSignController::class, 'checkDocumentStatus']);
    Route::post('/refresh-token', [DocuSignController::class, 'refreshToken']);
    Route::get('/all-documents', [DocuSignController::class, 'allDocuments']); 

    });


});
    

//Route::get('/docusign/connect', [DocusignController::class, 'connectDocusign']);
//Route::post('/docusign/callback', [DocusignController::class, 'callback']);
//Route::post('/docusign/sign', [DocusignController::class, 'validateDocument']);
//Route::post('/docusign/check-document', [DocusignController::class, 'checkDocumentStatus']);