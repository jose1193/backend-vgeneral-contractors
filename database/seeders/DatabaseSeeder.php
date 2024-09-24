<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TypeDamage;
use App\Models\InsuranceCompany;
use App\Models\PublicCompany;
use App\Models\CategoryProduct;
use App\Models\AllianceCompany;
use App\Models\CompanySignature;
use App\Models\Zone;
use App\Models\ServiceRequest;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       // Define el guardia, 'api' en este caso
        $guardName = 'api';
        
       // Definición de Permisos
$permissions = [
    // Administración y Supervisión Global
    Permission::create(['name' => 'Super Admin', 'guard_name' => $guardName]), // $permissions[0]
    Permission::create(['name' => 'Administrators', 'guard_name' => $guardName]), // $permissions[1]
    
    // Gestión de Equipos y Departamentos
    Permission::create(['name' => 'Manager', 'guard_name' => $guardName]), // $permissions[2]
    Permission::create(['name' => 'Marketing Manager', 'guard_name' => $guardName]), // $permissions[3]
    Permission::create(['name' => 'Director Assistant', 'guard_name' => $guardName]), // $permissions[4]
    Permission::create(['name' => 'Technical Supervisor', 'guard_name' => $guardName]), // $permissions[5]

    // Empresas y Operadores Externos
    Permission::create(['name' => 'Representation Company', 'guard_name' => $guardName]), // $permissions[6]
    Permission::create(['name' => 'Public Company', 'guard_name' => $guardName]), // $permissions[7]
    Permission::create(['name' => 'External Operators', 'guard_name' => $guardName]), // $permissions[8]
    
    // Ajustadores y Servicios Especializados
    Permission::create(['name' => 'Public Adjuster', 'guard_name' => $guardName]), // $permissions[9]
    Permission::create(['name' => 'Insurance Adjuster', 'guard_name' => $guardName]), // $permissions[10]
    Permission::create(['name' => 'Technical Services', 'guard_name' => $guardName]), // $permissions[11]
    Permission::create(['name' => 'Marketing', 'guard_name' => $guardName]), // $permissions[12]
    
    // Operaciones y Soporte Interno
    Permission::create(['name' => 'Warehouse', 'guard_name' => $guardName]), // $permissions[13]
    Permission::create(['name' => 'Administrative', 'guard_name' => $guardName]), // $permissions[14]
    Permission::create(['name' => 'Collections', 'guard_name' => $guardName]), // $permissions[15]
    
    // Acceso a Informes y Prospectos
    Permission::create(['name' => 'Reportes', 'guard_name' => $guardName]), // $permissions[16]
    Permission::create(['name' => 'Lead', 'guard_name' => $guardName]), // $permissions[17]
    
    // Usuarios Generales
    Permission::create(['name' => 'Employees', 'guard_name' => $guardName]), // $permissions[18]
    Permission::create(['name' => 'Client', 'guard_name' => $guardName]), // $permissions[19]
    Permission::create(['name' => 'Contact', 'guard_name' => $guardName]), // $permissions[20]
    Permission::create(['name' => 'Spectator', 'guard_name' => $guardName]), // $permissions[21]
];





    // Creación y Asignación de Roles


// SUPER ADMIN USER
$superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => $guardName]);
$superAdminRole->syncPermissions($permissions);

$superAdminUser = User::factory()->create([
    'name' => 'Victor',
    'last_name' => 'Lara',
    'username' => 'vialca21',
    'email' => 'vgeneralcontractors30@gmail.com',
    'uuid' => Uuid::uuid4()->toString(), 
    'phone' => '00000',
    'password' => bcrypt('Gc98765=')
]);
$superAdminUser->assignRole($superAdminRole);
// END SUPER ADMIN USER

   

     // ADMIN USER
$adminRole = Role::create(['name' => 'Admin', 'guard_name' => $guardName]);
$adminRole->syncPermissions([
    $permissions[1],  // Administrators
    $permissions[2],  // Manager
    $permissions[3],  // Marketing Manager
    $permissions[4],  // Director Assistant
    $permissions[5],  // Technical Supervisor
    $permissions[6],  // Representation Company
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]); 

$adminUser = User::factory()->create([
    'name' => 'Admin',
    'username' => 'admin24',
    'email' => 'admin@company.com',
    'uuid' => Uuid::uuid4()->toString(),
    'phone' => '00000',
    'password' => bcrypt('Gc98765=')
]);
$adminUser->assignRole($adminRole);
// END ADMIN USER



   // MANAGER USER
$managerRole = Role::create(['name' => 'Manager', 'guard_name' => $guardName]);

// Asignar permisos al rol de Manager
$managerRole->syncPermissions([
    $permissions[2],  // Manager
    $permissions[3],  // Marketing Manager
    $permissions[4],  // Director Assistant
    $permissions[5],  // Technical Supervisor
    $permissions[6],  // Representation Company
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

// Creación del usuario con rol de Manager
$managerUser = User::factory()->create([
    'name' => 'Manager',
    'username' => 'manager24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'manager@company.com',
    'phone' => '00000',
    'password' => bcrypt('Gc98765=')
]);

// Asignar el rol de Manager al usuario
$managerUser->assignRole($managerRole);
// END MANAGER USER

// MARKETING MANAGER USER
$marketingManagerRole = Role::create(['name' => 'Marketing Manager', 'guard_name' => $guardName]);

$marketingManagerRole->syncPermissions([
    $permissions[3],  // Marketing Manager
    $permissions[4],  // Director Assistant
    $permissions[5],  // Technical Supervisor
    $permissions[6],  // Representation Company
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$marketingManagerUser = User::factory()->create([
    'name' => 'Marketing Manager',
    'username' => 'marketingmanager24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'marketingmanager@company.com',
    'phone' => '00001',
    'password' => bcrypt('Gc98765=')
]);

$marketingManagerUser->assignRole($marketingManagerRole);
// END MARKETING MANAGER USER
  

// DIRECTOR ASSISTANT USER
$directorAssistantRole = Role::create(['name' => 'Director Assistant', 'guard_name' => $guardName]);

$directorAssistantRole->syncPermissions([
    $permissions[4],  // Director Assistant
    $permissions[5],  // Technical Supervisor
    $permissions[6],  // Representation Company
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$directorAssistantUser = User::factory()->create([
    'name' => 'Director Assistant',
    'username' => 'directorassistant24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'directorassistant@company.com',
    'phone' => '00002',
    'password' => bcrypt('Gc98765=')
]);

$directorAssistantUser->assignRole($directorAssistantRole);
// END DIRECTOR ASSISTANT USER


// TECHNICAL SUPERVISOR USER
$technicalSupervisorRole = Role::create(['name' => 'Technical Supervisor', 'guard_name' => $guardName]);

$technicalSupervisorRole->syncPermissions([
    $permissions[5],  // Technical Supervisor
    $permissions[6],  // Representation Company
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$technicalSupervisorUser = User::factory()->create([
    'name' => 'Technical Supervisor',
    'username' => 'technicalsupervisor24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'technicalsupervisor@company.com',
    'phone' => '00003',
    'password' => bcrypt('Gc98765=')
]);

$technicalSupervisorUser->assignRole($technicalSupervisorRole);
// END TECHNICAL SUPERVISOR USER



// REPRESENTATION COMPANY USER
$representationCompanyRole = Role::create(['name' => 'Representation Company', 'guard_name' => $guardName]);

$representationCompanyRole->syncPermissions([
    $permissions[6],  // Representation Company
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$representationCompanyUser = User::factory()->create([
    'name' => 'Representation Company',
    'username' => 'repcompany24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'repcompany@company.com',
    'phone' => '00004',
    'password' => bcrypt('Gc98765=')
]);

$representationCompanyUser->assignRole($representationCompanyRole);
// END REPRESENTATION COMPANY USER


// PUBLIC COMPANY USER
$publicCompanyRole = Role::create(['name' => 'Public Company', 'guard_name' => $guardName]);

$publicCompanyRole->syncPermissions([
    $permissions[7],  // Public Company
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$publicCompanyUser = User::factory()->create([
    'name' => 'Public Company',
    'username' => 'publiccompany24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'publiccompany@company.com',
    'phone' => '00005',
    'password' => bcrypt('Gc98765=')
]);

$publicCompanyUser->assignRole($publicCompanyRole);
// END PUBLIC COMPANY USER


// EXTERNAL OPERATORS USER
$externalOperatorsRole = Role::create(['name' => 'External Operators', 'guard_name' => $guardName]);

$externalOperatorsRole->syncPermissions([
    $permissions[8],  // External Operators
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$externalOperatorsUser = User::factory()->create([
    'name' => 'External Operators',
    'username' => 'externalops24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'externalops@company.com',
    'phone' => '00006',
    'password' => bcrypt('Gc98765=')
]);

$externalOperatorsUser->assignRole($externalOperatorsRole);
// END EXTERNAL OPERATORS USER


// PUBLIC ADJUSTER USER
$publicAdjusterRole = Role::create(['name' => 'Public Adjuster', 'guard_name' => $guardName]);

$publicAdjusterRole->syncPermissions([
    $permissions[9], // Public Adjuster
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$publicAdjusterUser = User::factory()->create([
    'name' => 'Public Adjuster',
    'username' => 'publicadjuster24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'publicadjuster@company.com',
    'phone' => '00007',
    'password' => bcrypt('Gc98765=')
]);

$publicAdjusterUser->assignRole($publicAdjusterRole);
// END PUBLIC ADJUSTER USER


// INSURANCE ADJUSTER USER
$insuranceAdjusterRole = Role::create(['name' => 'Insurance Adjuster', 'guard_name' => $guardName]);

$insuranceAdjusterRole->syncPermissions([
    $permissions[10], // Insurance Adjuster
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$insuranceAdjusterUser = User::factory()->create([
    'name' => 'Insurance Adjuster',
    'username' => 'insuranceadjuster24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'insuranceadjuster@company.com',
    'phone' => '00008',
    'password' => bcrypt('Gc98765=')
]);

$insuranceAdjusterUser->assignRole($insuranceAdjusterRole);
// END INSURANCE ADJUSTER USER


// TECHNICAL SERVICES USER
$technicalServicesRole = Role::create(['name' => 'Technical Services', 'guard_name' => $guardName]);

$technicalServicesRole->syncPermissions([
    $permissions[11], // Technical Services
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$technicalServicesUser = User::factory()->create([
    'name' => 'Technical Services',
    'username' => 'techservices24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'techservices@company.com',
    'phone' => '00009',
    'password' => bcrypt('Gc98765=')
]);

$technicalServicesUser->assignRole($technicalServicesRole);
// END TECHNICAL SERVICES USER


// MARKETING USER
$marketingRole = Role::create(['name' => 'Marketing', 'guard_name' => $guardName]);

$marketingRole->syncPermissions([
    $permissions[12], // Marketing
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$marketingUser = User::factory()->create([
    'name' => 'Marketing',
    'username' => 'marketing24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'marketing@company.com',
    'phone' => '00010',
    'password' => bcrypt('Gc98765=')
]);

$marketingUser->assignRole($marketingRole);
// END MARKETING USER


// WAREHOUSE USER
$warehouseRole = Role::create(['name' => 'Warehouse', 'guard_name' => $guardName]);

$warehouseRole->syncPermissions([
    $permissions[13], // Warehouse
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$warehouseUser = User::factory()->create([
    'name' => 'Warehouse',
    'username' => 'warehouse24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'warehouse@company.com',
    'phone' => '00011',
    'password' => bcrypt('Gc98765=')
]);

$warehouseUser->assignRole($warehouseRole);
// END WAREHOUSE USER



// ADMINISTRATIVE USER
$administrativeRole = Role::create(['name' => 'Administrative', 'guard_name' => $guardName]);

$administrativeRole->syncPermissions([
    $permissions[14], // Administrative
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$administrativeUser = User::factory()->create([
    'name' => 'Administrative',
    'username' => 'administrative24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'administrative@company.com',
    'phone' => '00012',
    'password' => bcrypt('Gc98765=')
]);

$administrativeUser->assignRole($administrativeRole);
// END ADMINISTRATIVE USER


// COLLECTIONS USER
$collectionsRole = Role::create(['name' => 'Collections', 'guard_name' => $guardName]);

$collectionsRole->syncPermissions([
    $permissions[15], // Collections
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$collectionsUser = User::factory()->create([
    'name' => 'Collections',
    'username' => 'collections24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'collections@company.com',
    'phone' => '00013',
    'password' => bcrypt('Gc98765=')
]);

$collectionsUser->assignRole($collectionsRole);
// END COLLECTIONS USER

// REPORTES USER
$reportesRole = Role::create(['name' => 'Reportes', 'guard_name' => $guardName]);

$reportesRole->syncPermissions([
    $permissions[16], // Reportes
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$reportesUser = User::factory()->create([
    'name' => 'Reportes',
    'username' => 'reportes24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'reportes@company.com',
    'phone' => '00014',
    'password' => bcrypt('Gc98765=')
]);

$reportesUser->assignRole($reportesRole);
// END REPORTES USER

// LEAD USER
$leadRole = Role::create(['name' => 'Lead', 'guard_name' => $guardName]);

$leadRole->syncPermissions([
    $permissions[17], // Lead
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$leadUser = User::factory()->create([
    'name' => 'Lead',
    'username' => 'lead24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'lead@company.com',
    'phone' => '00015',
    'password' => bcrypt('Gc98765=')
]);

$leadUser->assignRole($leadRole);
// END LEAD USER


// EMPLOYEES USER
$employeesRole = Role::create(['name' => 'Employees', 'guard_name' => $guardName]);

$employeesRole->syncPermissions([
    $permissions[18], // Employees
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$employeesUser = User::factory()->create([
    'name' => 'Employees',
    'username' => 'employees24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'employees@company.com',
    'phone' => '00016',
    'password' => bcrypt('Gc98765=')
]);

$employeesUser->assignRole($employeesRole);
// END EMPLOYEES USER


// CLIENT USER
$clientRole = Role::create(['name' => 'Client', 'guard_name' => $guardName]);

$clientRole->syncPermissions([
    $permissions[19], // Client
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$clientUser = User::factory()->create([
    'name' => 'Client',
    'username' => 'client24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'client@company.com',
    'phone' => '00017',
    'password' => bcrypt('Gc98765=')
]);

$clientUser->assignRole($clientRole);
// END CLIENT USER


// CONTACT USER
$contactRole = Role::create(['name' => 'Contact', 'guard_name' => $guardName]);

$contactRole->syncPermissions([
    $permissions[20], // Contact
    $permissions[21], // Spectator
]);

$contactUser = User::factory()->create([
    'name' => 'Contact',
    'username' => 'contact24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'contact@company.com',
    'phone' => '00018',
    'password' => bcrypt('Gc98765=')
]);

$contactUser->assignRole($contactRole);
// END CONTACT USER


// SPECTATOR USER
$spectatorRole = Role::create(['name' => 'Spectator', 'guard_name' => $guardName]);

$spectatorRole->syncPermissions([
    $permissions[21], // Spectator
]);

$spectatorUser = User::factory()->create([
    'name' => 'Spectator',
    'username' => 'spectator24',
    'uuid' => Uuid::uuid4()->toString(),
    'email' => 'spectator@company.com',
    'phone' => '00019',
    'password' => bcrypt('Gc98765=')
]);

$spectatorUser->assignRole($spectatorRole);
// END SPECTATOR USER


        // User::factory(10)->create();

        //User::factory()->create([
            //'name' => 'Test User',
            //'email' => 'test@example.com',
        //]);
       
        
        // TYPE DAMAGES
        $typeDamages = [
            'Kitchen',
            'Bathroom',
            'AC',
            'Heater',
            'Mold',
            'Roof Leak',
            'Flood',
            'Broke Pipe',
            'Internal Pipe',
            'Water Heater',
            'Roof',
            'Overflow',
            'Windstorm',
            'Water Leak',
            'Unknown',
            'Fire Damage',
            'Wind Damage',
            'Hurricane',
            'Water Damage',
            'Slab Leak',
            'TARP',
            'Hail Storm',
            'Shrink Wrap Roof',
            'Invoice',
            'Retarp',
            'Mold Testing',
            'Post-Hurricane',
            'Mitigation',
            'Mold Testing Clearance',
            'Rebuild',
            'Mold Remediation',
            'Plumbing',
            'Post-Storm'
        ];

        foreach ($typeDamages as $damage) {
            TypeDamage::create([
                'uuid' => Uuid::uuid4()->toString(),
                'type_damage_name' => $damage,
                'description' => 'Descripción de ' . $damage,
                'severity' => 'low' // o 'low'/'high' dependiendo de tu lógica de negocio
            ]);
        }
          // END TYPE DAMAGES

        //INSURANCE COMPANY
        $insuranceCompanies = [
            [
                'insurance_company_name' => 'Clear Insurance',
                'address' => '',
                'phone' => '(000) 000-0000',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Pekin Insurance',
                'address' => '',
                'phone' => '(888) 735-4611',
                'email' => 'claims@pekininsurance.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Openly',
                'address' => '',
                'phone' => '(888) 808-4842',
                'email' => 'Claims@openly.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Plymouth Rock Assurance',
                'address' => '',
                'phone' => '(844) 242-3555',
                'email' => 'rockcare@plymouthrock.com',
                'website' => 'https://www.plymouthrock.com/',
            ],
            [
                'insurance_company_name' => 'American Family Insurance',
                'address' => '',
                'phone' => '(800) 692-6326',
                'email' => 'Claimdocuments@asics.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Kemper Insurance',
                'address' => '',
                'phone' => '(800) 353-6737',
                'email' => 'Mail.claims@kemper.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Narrangansett Bay Insurance Company',
                'address' => '',
                'phone' => '(800) 343-3375',
                'email' => 'CALLandASK@insurancecompany.com',
                'website' => 'www.nbic.com',
            ],
            [
                'insurance_company_name' => 'State Farm Lloyds',
                'address' => '',
                'phone' => '(800) 732-5246',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'State Farm Fire and Casualty Company',
                'address' => '',
                'phone' => '(845) 226-5005',
                'email' => 'statefarmfireclaims@statefarm.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'First Community Insurance Company',
                'address' => '',
                'phone' => '(866) 401-1106',
                'email' => 'consultar@eugenia',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'New London County Mutual Insurance Company',
                'address' => '',
                'phone' => '(800) 962-0800',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Roadrunner Indemnity Company',
                'address' => '',
                'phone' => '(866) 522-0361',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'AAA Texas',
                'address' => '',
                'phone' => '(180) 067-2524',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'MESA UNDERWRITERS SPECIALTY INSURANCE COMPANY',
                'address' => '',
                'phone' => '(866) 547-0868',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'American Mobile Insurance Exchange',
                'address' => '',
                'phone' => '(844) 631-7819',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Utica National Insurance Group',
                'address' => '',
                'phone' => '(800) 598-8422',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Merrimack Mutual Fire Insurance Company',
                'address' => '',
                'phone' => '(978) 475-3300',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'American Commerce Insurance Company',
                'address' => '',
                'phone' => '(877) 627-3731',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'National Summit Insurance Company',
                'address' => '',
                'phone' => '(800) 749-6419',
                'email' => '',
                'website' => '',
            ],
             [
                'insurance_company_name' => 'Bunker Hill Insurance Company',
                'address' => '',
                'phone' => '(888) 472-5246',
                'email' => '',
                'website' => 'bunkerhillins.com',
            ],
            [
                'insurance_company_name' => 'The Providence Mutual Fire Insurance Company',
                'address' => '',
                'phone' => '(877) 763-1800',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'UNITED STATES LIABILITY INSURANCE COMPANY',
                'address' => '',
                'phone' => '(888) 523-5545',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Rockford Mutual Insurance Company',
                'address' => '',
                'phone' => '(800) 747-7642',
                'email' => 'claims@rockfordmutual.com',
                'website' => 'https://www.rockfordmutual.com/insurance/home',
            ],
            [
                'insurance_company_name' => 'CHASE',
                'address' => '',
                'phone' => '(877) 530-8951',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Church Mutual Insurance Company',
                'address' => '',
                'phone' => '(800) 554-2642',
                'email' => 'claims@churchmutual.com',
                'website' => 'https://www.churchmutual.com/7/Contact-Us',
            ],
            [
                'insurance_company_name' => 'Conifer Insurance Company',
                'address' => '',
                'phone' => '(877) 263-6468',
                'email' => 'claims@coniferinsurance.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'NatGen Premier',
                'address' => '',
                'phone' => '(184) 428-7223',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Underwriters at lloyd´s of london',
                'address' => '',
                'phone' => '(034) 530-0000',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Next',
                'address' => '',
                'phone' => '(800) 252-3439',
                'email' => 'ConsumerProtection@tdi.texas.gov',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'The Hanover Insurance Group',
                'address' => '',
                'phone' => '(800) 628-0250',
                'email' => 'firstreport@hanover.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Ussa Casualty Insurance Company',
                'address' => '',
                'phone' => '(210) 531-8722',
                'email' => '',
                'website' => 'https://www.usaa.com/?wa_ref=pub_global_home',
            ],
            [
                'insurance_company_name' => 'Kingstone Insurance',
                'address' => '',
                'phone' => '(800) 364-7045',
                'email' => 'claimreports@kingstoneic.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Texas Farm Bureau Insurance',
                'address' => '',
                'phone' => '(800) 224-7936',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'American Mercury Lloyds Insurance',
                'address' => '',
                'phone' => '(888) 637-2176',
                'email' => 'wrhome@mercuryinsurance.com',
                'website' => 'www.mercuryinsurance.com',
            ],
            [
                'insurance_company_name' => 'Standard Guaranty Insurance Company',
                'address' => '',
                'phone' => '(800) 652-1262',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'slide insurance company',
                'address' => '',
                'phone' => '(800) 748-2030',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Spinnaker Insurance Company',
                'address' => '',
                'phone' => '(888) 221-7742',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'VYRD Insurance Company',
                'address' => '',
                'phone' => '(844) 217-6993',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Andover',
                'address' => '',
                'phone' => '(203) 744-2800',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Branch Insurance exchange',
                'address' => '',
                'phone' => '(833) 427-2624',
                'email' => '',
                'website' => 'https://www.ourbranch.com/',
            ],
            [
                'insurance_company_name' => 'American Integrity',
                'address' => '',
                'phone' => '(866) 968-8390',
                'email' => 'claimsmail@aiiflorida.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'TypTap Insurance Company',
                'address' => '',
                'phone' => '(844) 289-7968',
                'email' => 'claims@typtap.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'National Summit',
                'address' => '',
                'phone' => '(800) 749-6419',
                'email' => 'claims@natlloyds.com',
                'website' => 'https://www.nationallloydsinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Bass Underwriters',
                'address' => '',
                'phone' => '(954) 316-3198',
                'email' => 'Claims@bassuw.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Sage Sure',
                'address' => '',
                'phone' => '(888) 316-0540',
                'email' => 'claimshelp@sagesure.com',
                'website' => 'https://www.sagesure.com/',
            ],
            [
                'insurance_company_name' => 'Weston Specialty Insurance',
                'address' => '',
                'phone' => '(000) 000-0000',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Homeowners of America insurance Company',
                'address' => '',
                'phone' => '(866) 407-9896',
                'email' => 'claims@hoaic.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Foremost Lloyds of Texas',
                'address' => '',
                'phone' => '(616) 942-3000',
                'email' => 'myclaim@foremost.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Southern Vanguard Insurance Company',
                'address' => '',
                'phone' => '(888) 432-9393',
                'email' => 'rhpclaims@rhpga.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Allied Trust',
                'address' => '',
                'phone' => '(844) 200-2842',
                'email' => 'alliedclaims@transcynd.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Preatorian Insurance Company',
                'address' => '',
                'phone' => '(866) 318-2016',
                'email' => 'claimmail@us.qbe.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Zurich American Insurance Company',
                'address' => '',
                'phone' => '(877) 777-6440',
                'email' => 'sstone@acmclaims.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Colonial Lloyds',
                'address' => '',
                'phone' => '(866) 522-0361',
                'email' => '',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Progressive Homesite',
                'address' => '',
                'phone' => '(800) 466-3748',
                'email' => 'claimdocuments@afics.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'SafeCo Insurance',
                'address' => '',
                'phone' => '(800) 332-3226',
                'email' => 'imaging@libertymutual.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Wellington Insurance Group',
                'address' => '',
                'phone' => '(800) 880-0474',
                'email' => 'claims@wellingtoninsgroup.com',
                'website' => 'http://www.wellingtoninsgroup.com/',
            ],
            [
                'insurance_company_name' => 'Berkshire Hathaway Guard',
                'address' => '',
                'phone' => '(800) 673-2465',
                'email' => 'claims@guard.com',
                'website' => '',
            ],
            [
                'insurance_company_name' => 'Aventus Insurance Company',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(972) 494-1591',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'MissionSelect Property Insurance Solutions',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(800) 233-2160',
                'email' => 'JSmith@missionselect.com',
                'website' => 'www.missionselect.com',
            ],
            [
                'insurance_company_name' => 'Encompass Insurance',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(800) 588-7400',
                'email' => 'claims@ngic.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Grojean Insurance',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(000) 000-0000',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Integon National Insurance Company',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(000) 000-0000',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'US Insurance',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(000) 000-0000',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Country Financial',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(866) 268-6879',
                'email' => 'claimsdocs@countryfinancial.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'US Standard Insurance Company (Grange Insurance)',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(800) 686-0025',
                'email' => null, // Agrega el correo si está disponible
                'website' => 'www.usstandardco.com',
            ],
            [
                'insurance_company_name' => 'AmGuard Insurance Company',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(570) 825-9900',
                'email' => 'guardclaimsteam@guard.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'UTICA FIRST INSURANCE COMPANY',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(000) 000-0000',
                'email' => 'claims@uticafirst.com',
                'website' => 'www.uticafirst.com',
            ],
            [
                'insurance_company_name' => 'Engle Martin & Associates',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(888) 239-7872',
                'email' => 'claims@emcas.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Texas FAIR Plan Association',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(800) 979-6440',
                'email' => 'tfphclaims@twia.org',
                'website' => 'http://www.texasfairplan.org/claimscenter',
            ],
            [
                'insurance_company_name' => 'National Fire & Marine Ins Co',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(402) 916-3000',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Germania Insurance',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(800) 392-2202',
                'email' => 'gclaims@germaniainsurance.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Homeowners of America',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(866) 407-9896',
                'email' => 'claims@hoaic.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Alicia Mutual Insurance Company',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(000) 000-0000',
                'email' => 'claims@amica.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'GEICO Insurance Agency',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(866) 621-4823',
                'email' => 'claimdocuments@afics.com',
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Cj Johnson Insurance Llc',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(407) 204-2600',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Alfa General Insurance Corporation',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(800) 964-2532',
                'email' => null, // Agrega el correo si está disponible
                'website' => 'https://www.alfainsurance.com',
            ],
            [
                'insurance_company_name' => 'USAA Casualty Insurance Company',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(000) 000-0000',
                'email' => null, // Agrega el correo si está disponible
                'website' => null, // Agrega el sitio web si está disponible
            ],
            [
                'insurance_company_name' => 'Hippo Insurance Services',
                'address' => null, // Agrega la dirección si está disponible
                'phone' => '(855) 999-9746',
                'email' => null, // Agrega el correo si está disponible
                'website' => 'www.hippo.com',
            ],
            [
                'insurance_company_name' => 'Amica Mutual Insurance Company',
                'address' => null,
                'phone' => '(800) 242-6422',
                'email' => null,
                'website' => null,
            ],
            [
                'insurance_company_name' => 'American Modern',
                'address' => null,
                'phone' => '(000) 000-0000',
                'email' => null,
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Main Street America Protection Insurance Company',
                'address' => null,
                'phone' => '(877) 425-2467',
                'email' => 'claimdocuments@afics.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'American Family Connect Property and Casualty Insurance Company (CONNECT)',
                'address' => null,
                'phone' => '(800) 872-5246',
                'email' => 'claimdocuments@afics.com',
                'website' => 'https://www.connectbyamfam.com',
            ],
            [
                'insurance_company_name' => 'Praetorian Insurance Company',
                'address' => null,
                'phone' => '(844) 723-2524',
                'email' => null,
                'website' => 'https://www.qbe.com/us',
            ],
            [
                'insurance_company_name' => 'National Summit Insurance Company',
                'address' => null,
                'phone' => '(800) 749-6419',
                'email' => 'claimsclerks@catalyticclaimsservices.com',
                'website' => 'www.nationalsummit.com',
            ],
            [
                'insurance_company_name' => 'State Auto Insurance Company',
                'address' => null,
                'phone' => '(833) 724-3577',
                'email' => 'claims@stateauto.com',
                'website' => 'www.stateauto.com',
            ],
            [
                'insurance_company_name' => 'Columbia Lloyds Insurance Company',
                'address' => null,
                'phone' => '(800) 275-6768',
                'email' => 'arefuge@mdowinsurance.com',
                'website' => 'https://www.columbialloyds.com/',
            ],
            [
                'insurance_company_name' => 'Transverse Specialty',
                'address' => null,
                'phone' => '(800) 447-6465',
                'email' => 'claims@wellingtoninsgroup.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Standard Casualty Company',
                'address' => null,
                'phone' => '(000) 000-0000',
                'email' => null,
                'website' => 'https://www.stdins.com/',
            ],
            [
                'insurance_company_name' => 'ASI',
                'address' => null,
                'phone' => '(000) 000-0000',
                'email' => 'claims@asicorp.org',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'RSUI Group Inc',
                'address' => null,
                'phone' => '(000) 000-0000',
                'email' => 'mail@mail.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Century Surety Company',
                'address' => null,
                'phone' => '(000) 000-0000',
                'email' => null,
                'website' => null,
            ],
            [
                'insurance_company_name' => 'American Risk Insurance Company',
                'address' => null,
                'phone' => '(713) 559-0700',
                'email' => 'claims@americanriskins.com',
                'website' => 'https://www.americanriskins.com/',
            ],
            [
                'insurance_company_name' => 'Kin Interinsurance Network',
                'address' => null,
                'phone' => '(866) 204-2219',
                'email' => 'claims@kin.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Zurich American Insurance',
                'address' => null,
                'phone' => '(000) 000-0000',
                'email' => 'notiene@zurichna.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'VAULT',
                'address' => null,
                'phone' => '(844) 388-5677',
                'email' => 'claimsfnol@vault.insurance',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'All American Insurance Consultants, Inc.',
                'address' => null,
                'phone' => '(754) 263-2160',
                'email' => 'david@allamericaninsure.com',
                'website' => 'https://allamerican-insurance.com/contact-us/',
            ],
            [
                'insurance_company_name' => 'Palomar Specialty',
                'address' => null,
                'phone' => '(619) 567-5290',
                'email' => 'claims@wellingtoninsgroup.com',
                'website' => 'wellingtoninsgroup.com',
            ],
            [
                'insurance_company_name' => 'Weston Insurance',
                'address' => null,
                'phone' => '(877) 505-3040',
                'email' => 'mailroom@narisk.com',
                'website' => 'https://weston-ins.com/',
            ],
            [
                'insurance_company_name' => 'American Platinum Property and Casualty',
                'address' => null,
                'phone' => '(800) 470-0599',
                'email' => 'noesta@noreply.com',
                'website' => 'https://americanplatinumpcic.com/claims',
            ],
            [
                'insurance_company_name' => 'Palomar Insurance Company',
                'address' => null,
                'phone' => '(334) 270-0105',
                'email' => 'claims@palomarins.com',
                'website' => 'https://www.palomarins.com/',
            ],
            [
                'insurance_company_name' => 'All Risks Insurance',
                'address' => null,
                'phone' => '(800) 366-7475',
                'email' => 'claims@allrisks.com',
                'website' => 'https://www.allrisks.com/',
            ],
            [
                'insurance_company_name' => 'White Pine Insurance Company',
                'address' => null,
                'phone' => '(800) 459-1690',
                'email' => 'claims@coniferinsurance.com',
                'website' => 'http://www.whitepineins.com/',
            ],
            [
                'insurance_company_name' => 'American Bankers Insurance Company of Florida',
                'address' => null,
                'phone' => null,
                'email' => 'myclaiminfo@assurant.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Scottsdale Insurance Company',
                'address' => null,
                'phone' => '(800) 423-7675',
                'email' => 'clmsrpts@nationwide.com',
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Great Lakes Insurance',
                'address' => null,
                'phone' => '(954) 341-8331',
                'email' => 'Claims@firestoneagency.com',
                'website' => 'https://www.greatlakesins.com/',
            ],
            [
                'insurance_company_name' => 'Axis Surplus Insurance Company',
                'address' => null,
                'phone' => '(866) 259-5435',
                'email' => 'ussnol@axiscapital.com',
                'website' => 'https://www.axisins.com/',
            ],
            [
                'insurance_company_name' => 'Farmers Insurance',
                'address' => null,
                'phone' => '(800) 435-7764',
                'email' => 'myclaim@farmersinsurance.com',
                'website' => 'https://www.farmers.com/',
            ],
             [
                'insurance_company_name' => 'Seacoast Brokers',
                'address' => null,
                'phone' => '(404) 751-4400',
                'email' => 'claims@seacoastbrokers.com',
                'website' => 'https://www.seacoastbrokers.com/report-a-claim/',
            ],
            [
                'insurance_company_name' => 'Swyfft Insurance',
                'address' => null,
                'phone' => '(877) 799-3389',
                'email' => 'claims@swyfft.zendesk.com',
                'website' => 'swyfft.com/claims',
            ],
            [
                'insurance_company_name' => 'Service Insurance Company',
                'address' => null,
                'phone' => '(866) 969-3899',
                'email' => 'eclaims@iatinsurance.com',
                'website' => 'https://www.iatinsurancegroup.com/claims/report-a-claim-property',
            ],
            [
                'insurance_company_name' => 'Southwest Business Corporation (SWBC)',
                'address' => null,
                'phone' => '(800) 527-0066',
                'email' => 'mtgclm01@swbc.com',
                'website' => 'https://www.swbc.com/',
            ],
            [
                'insurance_company_name' => 'Security First Insurance Company',
                'address' => null,
                'phone' => '(877) 581-4862',
                'email' => 'catclaims@securityfirstflorida.com',
                'website' => 'www.ROOFEMAIL.COM',
            ],
            [
                'insurance_company_name' => 'The Hearth Insurance Group',
                'address' => null,
                'phone' => '(305) 265-3101',
                'email' => 'claims@thehearth.com',
                'website' => 'www.thehearth.com',
            ],
            [
                'insurance_company_name' => 'Southern Oak',
                'address' => null,
                'phone' => '(877) 900-3971',
                'email' => 'southernoakmail@southernoakins.com',
                'website' => 'https://www.southernoak.com/',
            ],
            [
                'insurance_company_name' => 'Colony Insurance Company',
                'address' => null,
                'phone' => '(804) 560-2000',
                'email' => 'CommercialNewClaims@argogroupus.com',
                'website' => 'www.argolimited.com',
            ],
            [
                'insurance_company_name' => 'Florida Farm Bureau',
                'address' => null,
                'phone' => '(866) 275-7322',
                'email' => 'andrew.hinkle@ffbic.com',
                'website' => 'https://floridafarmbureau.com/',
            ],
            [
                'insurance_company_name' => 'Blackboard Insurance',
                'address' => null,
                'phone' => '(877) 347-8475',
                'email' => 'claims@blackboardinsurance.com',
                'website' => 'https://www.blackboardinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Proctor Insurance',
                'address' => null,
                'phone' => '(407) 647-4060',
                'email' => 'claimsadmin@pfic.com',
                'website' => 'https://www.proctorinsurance.com/',
            ],
            [
                'insurance_company_name' => 'US Coastal Property & Casualty',
                'address' => null,
                'phone' => '(866) 482-5246',
                'email' => 'claims@harborclaims.com',
                'website' => 'https://www.uscoastalpc.com/',
            ],
            [
                'insurance_company_name' => 'Assurant Insurance Company',
                'address' => null,
                'phone' => '(800) 852-2244',
                'email' => 'myclaiminfo@assurant.com',
                'website' => 'https://www.assurant.com/',
            ],
            [
                'insurance_company_name' => 'Stillwater Property and Casualty Insurance Company',
                'address' => null,
                'phone' => '(800) 220-1351',
                'email' => 'claims@stillwater.com',
                'website' => 'https://stillwaterinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Covington Insurance Agency, LLC',
                'address' => null,
                'phone' => '4042312366',
                'email' => 'reportclaims@rsui.com',
                'website' => 'http://rsui.com/',
            ],
            [
                'insurance_company_name' => 'National Specialty Insurance Company',
                'address' => null,
                'phone' => '(817) 265-2000',
                'email' => 'webclaims@statenational.com',
                'website' => 'https://insurance.mo.gov/CompanyAgentSearch/CompanySearch/compSearchDetails.php?id=122387&n=NATIONAL+SPECIALTY+INSURANCE+COMPANY',
            ],
            [
                'insurance_company_name' => 'Velocity Risk Underwriters',
                'address' => null,
                'phone' => '(844) 878-2567',
                'email' => 'claimdocuments@velocityrisk.com',
                'website' => 'http://velocityrisk.com',
            ],
            [
                'insurance_company_name' => 'USAA General Indemnity Company',
                'address' => null,
                'phone' => '(800) 531-8722',
                'email' => null,
                'website' => 'www.fema.gov/es/wyo-insurance-company/usaa-general-indemnity-company',
            ],
            [
                'insurance_company_name' => 'Sikes Insurance',
                'address' => null,
                'phone' => '(407) 282-5145',
                'email' => 'office@sikesinsurance.com',
                'website' => 'http://sikesinsuranceagency.com/',
            ],
            [
                'insurance_company_name' => 'Bass Underwriters, INC',
                'address' => null,
                'phone' => '(954) 473-4488',
                'email' => 'claims@bassuw.com',
                'website' => 'https://www.bassuw.com/',
            ],
            [
                'insurance_company_name' => 'Home First Agency',
                'address' => null,
                'phone' => '(800) 804-9389',
                'email' => 'hfaclaims@homefirstagengy.com',
                'website' => 'https://homefirstagency.com/home',
            ],
            [
                'insurance_company_name' => 'West Insurance Company',
                'address' => null,
                'phone' => '(252) 224-9381',
                'email' => 'info@westinsurance.com',
                'website' => 'http://www.westinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Fed Nat Adjusting',
                'address' => null,
                'phone' => '(800) 293-2532',
                'email' => 'claimdocs@fednat.com',
                'website' => 'http://www.fednat.com',
            ],
            [
                'insurance_company_name' => 'Capacity Insurance Company',
                'address' => null,
                'phone' => '(866) 351-3062',
                'email' => 'claims@capacityinsurance.com',
                'website' => 'http://www.macneillgroup.com/report-a-claim/',
            ],
            [
                'insurance_company_name' => 'Travelers Insurance Company',
                'address' => null,
                'phone' => '(800) 252-4633',
                'email' => 'boilinsp@travelers.com',
                'website' => 'https://www.travelers.com/',
            ],
            [
                'insurance_company_name' => 'Frontline Insurance',
                'address' => null,
                'phone' => '(877) 744-5224',
                'email' => null,
                'website' => 'https://www.frontlineinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Omega Insurance Company',
                'address' => null,
                'phone' => '(800) 509-1592',
                'email' => 'claims@thig.com',
                'website' => 'https://www.thig.com/claims',
            ],
            [
                'insurance_company_name' => 'North Carolina Insurance Underwriting Association',
                'address' => null,
                'phone' => '(919) 821-1299',
                'email' => 'claims.admin@ncjua.com',
                'website' => 'https://www.ncjua-nciua.org/',
            ],
            [
                'insurance_company_name' => 'Allstate Insurance Company',
                'address' => null,
                'phone' => '(877) 810-2920',
                'email' => 'claims@claims.allstate.com',
                'website' => 'https://www.allstate.com/',
            ],
            [
                'insurance_company_name' => 'Transcontinental Ins Group',
                'address' => null,
                'phone' => '(305) 671-3500',
                'email' => 'noreply@tigflorid.com',
                'website' => 'http://tigflorid.com/',
            ],
            [
                'insurance_company_name' => 'Progressive Home Insurance Company',
                'address' => null,
                'phone' => '(866) 274-5677',
                'email' => 'claims@asicorp.org',
                'website' => 'https://www.progressive.com/',
            ],
            [
                'insurance_company_name' => 'American States Insurance Company',
                'address' => null,
                'phone' => '(800) 332-3226',
                'email' => 'noreply@safeco.com',
                'website' => 'http://safeco.com',
            ],
            [
                'insurance_company_name' => 'Centauri Specialty Insurance Company',
                'address' => null,
                'phone' => '(866) 318-4113',
                'email' => 'customerservice@centauri-ins.com',
                'website' => 'https://www.centauriinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Amica Mutual Insurance Company',
                'address' => null,
                'phone' => '8002426422',
                'email' => 'mjones@amica.com',
                'website' => 'https://www.amica.com/',
            ],
            [
                'insurance_company_name' => 'The Hartford',
                'address' => null,
                'phone' => '(800) 243-5860',
                'email' => 'ccoclaims@thehartford.com',
                'website' => 'www.thehartford.com',
            ],
            [
                'insurance_company_name' => '1st Capital Insurance',
                'address' => null,
                'phone' => '(813) 739-8705',
                'email' => null,
                'website' => 'http://www.1stcapitalinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Safe Harbor Insurance Company',
                'address' => null,
                'phone' => '(866) 896-7233',
                'email' => 'claims@harborclaims.com',
                'website' => 'http://www.safeharborflorida.com/',
            ],
            [
                'insurance_company_name' => 'ARK Insurance Solution',
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
            ],
            [
                'insurance_company_name' => 'Prohenza Financial Benefits Insurance',
                'address' => null,
                'phone' => null,
                'email' => null,
                'website' => null,
            ],
            [
                'insurance_company_name' => 'United Services Automobile Association (USAA)',
                'address' => null,
                'phone' => '(210) 531-8722',
                'email' => null,
                'website' => 'www.usaa.com',
            ],
            [
                'insurance_company_name' => 'Universal Insurance Company of North America',
                'address' => null,
                'phone' => '(888) 846-7647',
                'email' => 'claims@uihna.com',
                'website' => 'www.fema.gov/...insurance-company/universal-insurance-.',
            ],
            [
                'insurance_company_name' => 'Tower Hill Insurance Group',
                'address' => null,
                'phone' => '(352) 332-8800',
                'email' => 'claims@thig.com',
                'website' => 'www.thig.com',
            ],
            [
                'insurance_company_name' => 'TAPCO',
                'address' => null,
                'phone' => '(800) 334-5579',
                'email' => 'claims@gotapco.com',
                'website' => 'www.gotapco.com',
            ],
            [
                'insurance_company_name' => 'Standard Premium Finance',
                'address' => null,
                'phone' => '(305) 232-2752',
                'email' => 'sebasstr@gmail.com',
                'website' => 'www.standardpremium.com',
            ],
            [
                'insurance_company_name' => 'State Farm Florida Insurance Company',
                'address' => null,
                'phone' => '(800) 732-5246',
                'email' => 'statefarmfireclaims@statefarm.com',
                'website' => 'www.statefarm.com',
            ],
            [
                'insurance_company_name' => 'St Johns Insurance Company',
                'address' => null,
                'phone' => '(877) 748-2059',
                'email' => 'dispatch@seibels.com',
                'website' => 'www.stjohnsinsurance.com',
            ],
            [
                'insurance_company_name' => 'Southern Fidelity Insurance Company',
                'address' => null,
                'phone' => '(866) 722-4995',
                'email' => 'claims@southernfidelityins.com',
                'website' => 'www.southernfidelityins.com',
            ],
            [
                'insurance_company_name' => 'Security National Ins',
                'address' => null,
                'phone' => '(877) 581-4862',
                'email' => 'claims@securitynational.com',
                'website' => 'www.securitynationallife.com',
            ],
            [
                'insurance_company_name' => 'Security First Insurance Company',
                'address' => null,
                'phone' => '(877) 581-4862',
                'email' => 'claims@securityfirstflorida.com',
                'website' => 'www.securityfirstflorida.com',
            ],
            [
                'insurance_company_name' => 'Sawgrass Mutual Insurance Company',
                'address' => null,
                'phone' => '(877) 853-4430',
                'email' => 'claims@sawgrassmutual.com',
                'website' => 'www.sawgrassmutual.com',
            ],
            [
                'insurance_company_name' => 'Safe Point Insurance Company',
                'address' => null,
                'phone' => '(855) 252-4615',
                'email' => 'claims@safepointins.com',
                'website' => 'www.safepointins.com',
            ],
            [
                'insurance_company_name' => 'Prepared Insurance Company',
                'address' => null,
                'phone' => '(877) 511-7737',
                'email' => null,
                'website' => 'www.preparedins.com',
            ],
            [
                'insurance_company_name' => 'Peoples Trust Insurance',
                'address' => null,
                'phone' => '(877) 333-1230',
                'email' => 'claimsadmin@peoplestrustinsurance.com',
                'website' => 'www.peoplestrustinsurance.com',
            ],
            [
                'insurance_company_name' => 'Olympus Insurance',
                'address' => null,
                'phone' => '(866) 281-2242',
                'email' => 'olympusclaims@oigfl.com',
                'website' => 'www.olympusinsurance.com',
            ],
            [
                'insurance_company_name' => 'Nationwide Ins Co. of Florida',
                'address' => null,
                'phone' => '(800) 421-3535',
                'email' => 'clmsrpts@nationwide.com',
                'website' => 'www.nationwide.com',
            ],
            [
                'insurance_company_name' => 'New Hampshire Insurance Company',
                'address' => null,
                'phone' => '(877) 399-6442',
                'email' => 'lexorgfnol@aig.com',
                'website' => 'www.newhampshireinsurancecompany.com',
            ],
            [
                'insurance_company_name' => 'Monarch National',
                'address' => null,
                'phone' => '(800) 293-2532',
                'email' => 'claimdocs@fednat.com',
                'website' => 'www.jpperry.com/insurance-company/monarch-national',
            ],
            [
                'insurance_company_name' => 'Modern USA Insurance Company',
                'address' => null,
                'phone' => '(866) 270-8430',
                'email' => 'musaclaims@westpointuw.com',
                'website' => 'www.floridainsurancecenter.com/insurance.../modern-usa/',
            ],
            [
                'insurance_company_name' => 'Met Life',
                'address' => null,
                'phone' => '(800) 854-6011',
                'email' => 'importrequest@metlife.com',
                'website' => 'www.metlife.com',
            ],
            [
                'insurance_company_name' => 'Lloyds of London',
                'address' => null,
                'phone' => '(800) 293-2532',
                'email' => null,
                'website' => 'www.lloyds.com',
            ],
            [
                'insurance_company_name' => 'Lexington Company',
                'address' => '',
                'phone' => '(800) 931-9546',
                'email' => 'lexingtonins@aig.com',
                'website' => 'www.lexingtoncompany.com',
            ],
            [
                'insurance_company_name' => 'National General Insurance Company',
                'address' => '',
                'phone' => '(800) 325-1088',
                'email' => '',
                'website' => 'www.nationalgeneral.com',
            ],
            [
                'insurance_company_name' => 'Integon National Insurance',
                'address' => '',
                'phone' => '(800) 323-7466',
                'email' => 'claims@ngic.com',
                'website' => 'www.massauto.twrgrp.com',
            ],
            [
                'insurance_company_name' => 'Homeowners Choice',
                'address' => '',
                'phone' => '(813) 405-3600',
                'email' => 'claims@hcpci.com',
                'website' => 'www.hcpci.com',
            ],
            [
                'insurance_company_name' => 'Heritage Property and Casualty Insurance',
                'address' => '',
                'phone' => '(727) 362-7200',
                'email' => 'claims@heritagepci.com',
                'website' => 'www.heritagepci.com',
            ],
            [
                'insurance_company_name' => 'Hazard Insurance Agency, Inc',
                'address' => '',
                'phone' => '(877) 829-0222',
                'email' => 'ritap@hazardins.com',
                'website' => 'www.hazardins.com',
            ],
            [
                'insurance_company_name' => 'Gulfstream Property and Casualty Insurance Company',
                'address' => '',
                'phone' => '(866) 485-3004',
                'email' => 'dispatch@seibels.com',
                'website' => 'www.gspcic.com',
            ],
            [
                'insurance_company_name' => 'Guideone Mutual Insurance',
                'address' => '',
                'phone' => '(877) 448-4331',
                'email' => 'CLU@guideone.com',
                'website' => 'www.guideone.com',
            ],
            [
                'insurance_company_name' => 'Great American Insurance Co',
                'address' => '',
                'phone' => '(877) 429-3826',
                'email' => 'fidclaims@gaig.com',
                'website' => 'www.greatamericancrop.com',
            ],
            [
                'insurance_company_name' => 'Geovera Specialty Insurance',
                'address' => '',
                'phone' => '(800) 232-3347',
                'email' => 'hurricaneclaims@geoveraadvantage.com',
                'website' => 'www.geoveraspecialty.com',
            ],
            [
                'insurance_company_name' => 'Foresmost Insurance Group',
                'address' => '',
                'phone' => '(800) 527-3907',
                'email' => 'myclaim@foremost.com',
                'website' => 'www.foremost.com',
            ],
            [
                'insurance_company_name' => 'Florida Specialty Insurance Company',
                'address' => '',
                'phone' => '(866) 554-5896',
                'email' => 'figaclaimsworkflow@agfgroup.org',
                'website' => 'www.floridaspecialty.com',
            ],
            [
                'insurance_company_name' => 'Florida Peninsula Insurance Company',
                'address' => '',
                'phone' => '(877) 229-2244',
                'email' => 'csclaims@floridapeninsula.com',
                'website' => 'www.floridapeninsula.com/',
            ],
            [
                'insurance_company_name' => 'Florida Insurance Specialists',
                'address' => '',
                'phone' => '(866) 681-4668',
                'email' => 'allen.cotton@elements-ins.com',
                'website' => 'www.thefis.com',
            ],
            [
                'insurance_company_name' => 'Florida Family Insurance',
                'address' => '',
                'phone' => '(888) 850-4663',
                'email' => 'claims@floridafamily.com',
                'website' => 'www.floridafamily.com/',
            ],
            [
                'insurance_company_name' => 'Federated National Insurance Company',
                'address' => '',
                'phone' => '(800) 293-2532',
                'email' => 'claims@fednat.com',
                'website' => 'www.hillcrestinsurance.com/insurance-company/fed-nat',
            ],
            [
                'insurance_company_name' => 'Elements Property',
                'address' => '',
                'phone' => '(866) 709-8749',
                'email' => 'customerservice@epicipx.com',
                'website' => 'www.epicipx.com/Main/',
            ],
            [
                'insurance_company_name' => 'Edison Insurance Company',
                'address' => '',
                'phone' => '(888) 683-7971',
                'email' => 'csclaims@edisoninsurance.com',
                'website' => 'www.edisoninsurance.com/',
            ],
            [
                'insurance_company_name' => 'Cypress Property and Casualty',
                'address' => '',
                'phone' => '(877) 560-5224',
                'email' => 'claimsinfo@cypressig.com',
                'website' => 'www.cypressig.com',
            ],
            [
                'insurance_company_name' => 'Citizens',
                'address' => '',
                'phone' => '(866) 411-2742',
                'email' => 'claims.communications@citizensfla.com',
                'website' => 'www.citizensfla.com',
            ],
            [
                'insurance_company_name' => 'Castle Key Indemnity Company',
                'address' => '',
                'phone' => '(888) 866-7069',
                'email' => 'claims@claims.allstate.com',
                'website' => 'www.allstate.com',
            ],
            [
                'insurance_company_name' => 'Capitol Preferred',
                'address' => '',
                'phone' => '(888) 388-2742',
                'email' => 'iareports@capitol-preferred.com',
                'website' => 'www.capitol-preferred.com/',
            ],
            [
                'insurance_company_name' => 'Bankers Insurance',
                'address' => '',
                'phone' => '(727) 823-4000',
                'email' => 'csc@bankersinsurance.com',
                'website' => 'www.bankersinsurance.com',
            ],
            [
                'insurance_company_name' => 'Avatar Property & Casualty Ins',
                'address' => '',
                'phone' => '(813) 514-0333',
                'email' => '',
                'website' => 'www.avatarins.com/',
            ],
            [
                'insurance_company_name' => 'Auto Club Insurance',
                'address' => '',
                'phone' => '(800) 289-1325',
                'email' => 'claims@autoclubfl.com',
                'website' => 'www.autoclubfl.com/',
            ],
            [
                'insurance_company_name' => 'Anchor Property & Casualty Insurance Company',
                'address' => '',
                'phone' => '(941) 365-5588',
                'email' => 'flclaims@relyonanchor.com',
                'website' => 'https://www.relyonanchor.com',
            ],
            [
                'insurance_company_name' => 'American Traditions Insurance',
                'address' => '',
                'phone' => '(866) 270-8430',
                'email' => 'aticclaims@westpointuw.com',
                'website' => 'www.jergermga.com',
            ],
            [
                'insurance_company_name' => 'American Security Insurance Company',
                'address' => '',
                'phone' => '(800) 326-2845',
                'email' => 'myclaiminfo@assurant.com',
                'website' => 'www.americansecurityinsurance.net/about/',
            ],
            [
                'insurance_company_name' => 'Acord Insurance Group',
                'address' => '',
                'phone' => '(800) 776-4737',
                'email' => 'sdillings@guideone.com',
                'website' => 'www.acord.org',
            ],
            [
                'insurance_company_name' => 'American Integrity Insurance Company of Florida',
                'address' => '',
                'phone' => '(866) 277-9871',
                'email' => 'claimsmail@aiiflorida.com',
                'website' => 'www.aiicfl.com',
            ],
            [
                'insurance_company_name' => 'American Strategic Insurance',
                'address' => '',
                'phone' => '(866) 274-8765',
                'email' => 'claims@asicorp.org',
                'website' => 'www.americanstrategic.com',
            ],
            [
                'insurance_company_name' => 'Ark Royal Insurance',
                'address' => '',
                'phone' => '(727) 456-1673',
                'email' => 'claims@asicorp.org',
                'website' => 'https://www.asicorp.org/',
            ],
            [
                'insurance_company_name' => 'Universal Property & Casualty Insurance Company',
                'address' => '',
                'phone' => '(954) 958-1200',
                'email' => '',
                'website' => 'https://www.universalproperty.com/',
            ],
            [
                'insurance_company_name' => 'UPC / United Property & Casualty',
                'address' => '',
                'phone' => '(800) 988-1450',
                'email' => 'claims@upcinsurance.com',
                'website' => 'https://www.upcinsurance.com/',
            ],
            [
                'insurance_company_name' => 'Liberty Mutual Fire Insurance',
                'address' => '',
                'phone' => '(855) 212-3227',
                'email' => 'ccu@libertymutual.com',
                'website' => 'https://www.libertymutual.com/',
            ],

            // 2 PARTE
            [
        'insurance_company_name' => 'ACCC Insurance Company',
        'address' => '3635 N. 1st Ave, Suite 102, Tucson, AZ 85719',
        'phone' => '(520) 888-1234',
        'email' => 'info@acccinsurance.com',
        'website' => 'https://acccinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Access Insurance Company',
        'address' => '1200 Abernathy Road NE, Suite 1700, Atlanta, GA 30328',
        'phone' => '(678) 369-3700',
        'email' => 'contact@accessinsurance.com',
        'website' => 'https://www.accessinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Alicot Insurance Company',
        'address' => '2144 E. 5th Street, Suite 101, Austin, TX 78702',
        'phone' => '(512) 472-1234',
        'email' => 'info@alicotinsurance.com',
        'website' => 'https://www.alicotinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Amcare Health Plans of Texas, Inc',
        'address' => '5001 S. Congress Avenue, Suite 200, Austin, TX 78745',
        'phone' => '(512) 280-1234',
        'email' => 'contact@amcarehealth.com',
        'website' => 'https://www.amcarehealth.com/'
        ],
        [
        'insurance_company_name' => 'Amcare Management, Inc.',
        'address' => '5400 W. Plano Parkway, Suite 400, Plano, TX 75093',
        'phone' => '(972) 555-5678',
        'email' => 'support@amcaremanagement.com',
        'website' => 'https://www.amcaremanagement.com/'
        ],
        [
        'insurance_company_name' => 'American Founders Financial Corporation',
        'address' => '1301 W. 15th Street, Suite 100, Plano, TX 75075',
        'phone' => '(214) 555-6789',
        'email' => 'info@americanfounders.com',
        'website' => 'https://www.americanfounders.com/'
        ],
        [
        'insurance_company_name' => 'Austin Indemnity Lloyds Insurance Company',
        'address' => '1000 W. 15th Street, Suite 300, Austin, TX 78703',
        'phone' => '(512) 555-4321',
        'email' => 'contact@austinindemnity.com',
        'website' => 'https://www.austinindemnity.com/'
        ],
        [
        'insurance_company_name' => 'Austin Indemnity Management Company, LLC',
        'address' => '1011 W. 15th Street, Suite 400, Austin, TX 78701',
        'phone' => '(512) 555-5678',
        'email' => 'info@austinindemnitymanagement.com',
        'website' => 'https://www.austinindemnitymanagement.com/'
        ],
        [
        'insurance_company_name' => 'Bright Healthcare Insurance Company of Texas',
        'address' => '2600 N. Lamar Boulevard, Suite 200, Austin, TX 78705',
        'phone' => '(512) 555-8765',
        'email' => 'contact@brighthealthcare.com',
        'website' => 'https://www.brighthealthcare.com/'
        ],
        [
        'insurance_company_name' => 'Capson Physicians Insurance Company',
        'address' => '1075 E. 25th Street, Suite 300, Austin, TX 78705',
        'phone' => '(512) 555-6789',
        'email' => 'info@capsonphysicians.com',
        'website' => 'https://www.capsonphysicians.com/'
        ],
        [
        'insurance_company_name' => 'Conifer Insurance',
        'address' => '4000 N. Central Expressway, Suite 700, Dallas, TX 75204',
        'phone' => '(214) 555-1234',
        'email' => 'contact@coniferinsurance.com',
        'website' => 'https://www.coniferinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Family Life Insurance Company of America',
        'address' => '7000 W. 12th Street, Suite 100, Little Rock, AR 72204',
        'phone' => '(501) 555-6789',
        'email' => 'info@familylifeinsurance.com',
        'website' => 'https://www.familylifeinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Friday Health Insurance Company Inc.',
        'address' => '555 E. 16th Avenue, Suite 200, Denver, CO 80203',
        'phone' => '(303) 555-7890',
        'email' => 'contact@fridayhealth.com',
        'website' => 'https://www.fridayhealth.com/'
        ],
        [
        'insurance_company_name' => 'Good Samaritan Life Insurance Company',
        'address' => '123 Good Samaritan Way, Suite 100, Nashville, TN 37203',
        'phone' => '(615) 555-4321',
        'email' => 'info@goodsamaritanlife.com',
        'website' => 'https://www.goodsamaritanlife.com/'
        ],
        [
        'insurance_company_name' => 'Gramercy Insurance Company',
        'address' => '2000 Gramercy Drive, Suite 300, New York, NY 10010',
        'phone' => '(212) 555-6789',
        'email' => 'contact@gramercyinsurance.com',
        'website' => 'https://www.gramercyinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Grand Court Order of Calanthe',
        'address' => '900 Grand Court Avenue, Suite 200, Chicago, IL 60614',
        'phone' => '(312) 555-9876',
        'email' => 'info@grandcourtcalanthe.com',
        'website' => 'https://www.grandcourtcalanthe.com/'
        ],
        [
        'insurance_company_name' => 'Highlands Insurance Company',
        'address' => '5000 Highlands Drive, Suite 400, Houston, TX 77002',
        'phone' => '(713) 555-4321',
        'email' => 'contact@highlandsinsurance.com',
        'website' => 'https://www.highlandsinsurance.com/'
        ],
        [
        'insurance_company_name' => 'Houston General Insurance Exchange',
        'address' => '8000 Main Street, Suite 200, Houston, TX 77002',
        'phone' => '(713) 555-6789',
        'email' => 'info@houstongeneral.com',
        'website' => 'https://www.houstongeneral.com/'
        ],
        [
        'insurance_company_name' => 'Lincoln Memorial Life Ins. Company',
        'address' => '1201 Lincoln Way, Suite 100, Lincoln, NE 68502',
        'phone' => '(402) 555-1234',
        'email' => 'info@lincolnmemorial.com',
        'website' => 'https://www.lincolnmemorial.com/'
        ],
        [
        'insurance_company_name' => 'Lone Star Life Insurance Company',
        'address' => '600 Lone Star Parkway, Suite 300, Dallas, TX 75201',
        'phone' => '(214) 555-7890',
        'email' => 'contact@lonestarlife.com',
        'website' => 'https://www.lonestarlife.com/'
        ],
        [
        'insurance_company_name' => 'Memorial Service Life Ins. Company',
        'address' => '700 Memorial Drive, Suite 200, San Antonio, TX 78209',
        'phone' => '(210) 555-1234',
        'email' => 'info@memorialservice.com',
        'website' => 'https://www.memorialservice.com/'
        ],
        [
        'insurance_company_name' => 'Millennium Closing Services',
        'address' => '1500 Millennium Tower, Suite 500, New York, NY 10005',
        'phone' => '(212) 555-5678',
        'email' => 'contact@millenniumclosing.com',
        'website' => 'https://www.millenniumclosing.com/'
        ],
        [
        'insurance_company_name' => 'National Prearranged Services, Inc.',
        'address' => '900 National Avenue, Suite 300, Charlotte, NC 28202',
        'phone' => '(704) 555-6789',
        'email' => 'info@nationalprearranged.com',
        'website' => 'https://www.nationalprearranged.com/'
        ],
        [
        'insurance_company_name' => 'San Antonio Indemnity Company',
        'address' => '1100 San Antonio Road, Suite 200, San Antonio, TX 78215',
        'phone' => '(210) 555-6789',
        'email' => 'contact@sanantonioindemnity.com',
        'website' => 'https://www.sanantonioindemnity.com/'
        ],
        [
        'insurance_company_name' => 'Santa Fe Auto Insurance Company',
        'address' => '2500 Santa Fe Avenue, Suite 100, Santa Fe, NM 87501',
        'phone' => '(505) 555-1234',
        'email' => 'info@santafeauto.com',
        'website' => 'https://www.santafeauto.com/'
        ],
        [
        'insurance_company_name' => 'Select Ins. Services, Inc., And',
        'address' => '3000 Select Way, Suite 200, Los Angeles, CA 90001',
        'phone' => '(323) 555-6789',
        'email' => 'contact@selectinsservices.com',
        'website' => 'https://www.selectinsservices.com/'
        ],
        [
        'insurance_company_name' => 'Shelby Casualty Ins. Company',
        'address' => '4000 Shelby Avenue, Suite 100, Memphis, TN 38103',
        'phone' => '(901) 555-1234',
        'email' => 'info@shelbycasualty.com',
        'website' => 'https://www.shelbycasualty.com/'
        ],
        [
        'insurance_company_name' => 'Statefarm',
        'address' => '1 State Farm Plaza, Bloomington, IL 61710',
        'phone' => '(309) 766-2311',
        'email' => 'contact@statefarm.com',
        'website' => 'https://www.statefarm.com/'
        ],
        [
        'insurance_company_name' => 'Texas Fair Plan',
        'address' => '1400 Texas Street, Suite 200, Austin, TX 78701',
        'phone' => '(512) 555-1234',
        'email' => 'info@texasfairplan.com',
        'website' => 'https://www.texasfairplan.com/'
        ],
        [
        'insurance_company_name' => 'Texas Select Lloyds Ins. Company',
        'address' => '3000 Lloyds Lane, Suite 400, Houston, TX 77002',
        'phone' => '(713) 555-6789',
        'email' => 'contact@texasselectlloyds.com',
        'website' => 'https://www.texasselectlloyds.com/'
        ],
        [
        'insurance_company_name' => 'Texas Windstorm',
        'address' => '5000 Windstorm Drive, Suite 300, Galveston, TX 77550',
        'phone' => '(409) 555-1234',
        'email' => 'info@texaswindstorm.com',
        'website' => 'https://www.texaswindstorm.com/'
        ],
        [
        'insurance_company_name' => 'The Shelby Ins. Company',
        'address' => '800 Shelby Parkway, Suite 200, Atlanta, GA 30328',
        'phone' => '(678) 555-5678',
        'email' => 'contact@theshelby.com',
        'website' => 'https://www.theshelby.com/'
        ],
        [
        'insurance_company_name' => 'Universal HMO of Texas, Inc.',
        'address' => '2000 Universal Drive, Suite 100, Dallas, TX 75201',
        'phone' => '(214) 555-6789',
        'email' => 'info@universalhmo.com',
        'website' => 'https://www.universalhmo.com/'
        ],
        [
        'insurance_company_name' => 'Universal Insurance Exchange',
        'address' => '3000 Universal Plaza, Suite 200, Houston, TX 77002',
        'phone' => '(713) 555-1234',
        'email' => 'contact@universalinsuranceexchange.com',
        'website' => 'https://www.universalinsuranceexchange.com/'
        ],
            [
        'insurance_company_name' => 'Universal Paratransit Insurance Services, Corp',
        'address' => '4000 Paratransit Lane, Suite 300, San Diego, CA 92101',
        'phone' => '(619) 555-6789',
        'email' => 'info@universalparatransit.com',
        'website' => 'https://www.universalparatransit.com/'
        ],
        [
        'insurance_company_name' => 'Vesta Fire Insurance Corp.',
        'address' => '5000 Vesta Avenue, Suite 400, Atlanta, GA 30318',
        'phone' => '(404) 555-1234',
        'email' => 'contact@vestafire.com',
        'website' => 'https://www.vestafire.com/'
        ],
        [
        'insurance_company_name' => 'Vesta Insurance Corporation',
        'address' => '6000 Vesta Drive, Suite 500, Miami, FL 33101',
        'phone' => '(305) 555-6789',
        'email' => 'info@vestainsurance.com',
        'website' => 'https://www.vestainsurance.com/'
        ],
        [
        'insurance_company_name' => 'Windhaven National Insurance Company',
        'address' => '7000 Windhaven Road, Suite 600, Fort Worth, TX 76102',
        'phone' => '(817) 555-1234',
        'email' => 'contact@windhaven.com',
        'website' => 'https://www.windhaven.com/'
        ],
        [
                'insurance_company_name' => 'Progressive Insurance',
                'address' => '6300 Wilson Mills Road, Mayfield Village, Ohio 4414',
                'phone' => '866 407 4844',
                'email' => '',
                'website' => 'https://www.progressive.com/',
            ],
            [
                'insurance_company_name' => 'Other Not Mentioned Force Placed Policies',
                'address' => '',
                'phone' => '',
                'email' => '',
                'website' => '',
            ],
        ];

        foreach ($insuranceCompanies as $company) {
            Insurancecompany::create([
                'uuid' => Uuid::uuid4()->toString(),
                'insurance_company_name' => $company['insurance_company_name'],
                'address' => $company['address'],
                'phone' => $company['phone'],
                'email' => $company['email'],
                'website' => $company['website'],
            ]);
        }
          // EN INSURANCE COMPANY

         // PUBLIC COMPANY
 $publicCompanies = [
            [
                'public_company_name' => 'Integrity Claims',
                'unit' => null,
                'address' => null,
                'phone' => '800 213 8069',
                'email' => null,
                'website' => 'https://integrityclaimsgroup.com',
            ],
            
            
        ];

        foreach ($publicCompanies as $company) {
            PublicCompany::create([
                'uuid' => Uuid::uuid4()->toString(),
                'public_company_name' => $company['public_company_name'],
                'unit' => $company['unit'],
                'address' => $company['address'],
                'phone' => $company['phone'],
                'email' => $company['email'],
                'website' => $company['website'],
            ]);
        }
        
    //END PUBLIC COMPANY

     // CATEGORY PRODUCT
$categoriesproducts = [
            'Material Removal',
            'Consumible',
            'Content Movement',
            'Administrative',
            'PPE',
            'Equipment',
            'Products',
            'Services'
        ];

        foreach ($categoriesproducts as $category) {
            CategoryProduct::create([
                'uuid' => Uuid::uuid4()->toString(),
                'category_product_name' => $category,
            ]);
        }
   // END CATEGORY PRODUCT
    $this->call(ProductSeeder::class);


   //ALIANCE COMPANY
     // Sample AllianceCompany data
        $allianceCompanies = [
            [
                'alliance_company_name' => 'Claim Pay',
                'phone' => '877 443 3007',
                'email' => 'info@claimpay.net',
                'address' => '111 E 17th St #13327 SMB#60762 Austin, TX 78701',
                'website' => 'https://claimpay.net',
                'user_id' => 1, // Assign a random user
            ],

             [
                'alliance_company_name' => 'Servxpress Restoration, LLC ',
                'phone' => '832 392 1147',
                'email' => 'claims@servxpressrestorations.com',
                'address' => '178 N Fry suite 260 Houston, TX 77084',
                'website' => 'https://servxpressrestorations.com/restoration/',
                'user_id' => 1, // Assign a random user
            ],
           
        ];

        foreach ($allianceCompanies as $companyData) {
        $companyData['uuid'] = Uuid::uuid4()->toString();
             AllianceCompany::create($companyData);
        }
        // END ALIANCE COMPANY

        //SIGNATURE COMPANY
     
        $companySignature = [
            [
                'company_name' => 'V General Contractors',
                'signature_path' => '/signatures/acme_signature.png',
                'phone' => '346 615-5393',
                'email' => 'info@vgeneralcontractors.com',
                'address' => '1302 Waugh Dr # 810 Houston TX 77019',
                'website' => 'https://vgeneralcontractors.com',
                'user_id' => 1, 
            ]
           
        ];

        foreach ($companySignature as $companyData) {
        $companyData['uuid'] = Uuid::uuid4()->toString();
             CompanySignature::create($companyData);
        }
        // END COMPANY SIGNATURE


        //ZONES
        $zones = [
            ['zone_name' => 'Bathroom'],
            ['zone_name' => 'Kitchen'],
            ['zone_name' => 'Bedroom'],
            ['zone_name' => 'Living Room'],
            ['zone_name' => 'Dining Room'],
            ['zone_name' => 'Basement'],
            ['zone_name' => 'Attic'],
            ['zone_name' => 'Garage'],
            ['zone_name' => 'Laundry Room'],
            ['zone_name' => 'Hallway'],
            ['zone_name' => 'Closet'],
            ['zone_name' => 'Office'],
            ['zone_name' => 'Family Room'],
            ['zone_name' => 'Utility Room'],
            ['zone_name' => 'Foyer/Entryway'],
            ['zone_name' => 'Staircase'],
            ['zone_name' => 'Crawl Space'],
            ['zone_name' => 'Porch'],
            ['zone_name' => 'Patio'],
            ['zone_name' => 'Deck'],
            ['zone_name' => 'Sunroom'],
            ['zone_name' => 'Study'],
            ['zone_name' => 'Guest Room'],
            ['zone_name' => 'Home Theater'],
            ['zone_name' => 'Wine Cellar'],
            ['zone_name' => 'Gym'],
            ['zone_name' => 'Workshop'],
            ['zone_name' => 'Storage Room'],
            ['zone_name' => 'Roof'],
            ['zone_name' => 'Exterior Walls'],
        ];

        foreach ($zones as $zone) {
            $zone['uuid'] = Uuid::uuid4()->toString();
            Zone::create($zone);
        }
        // END ZONE


         //SERVICE REQUEST
     
        $services = [
            'Mitigation',
            'TARP',
            'RETARP',
            'REPAIR'
        ];

        // Recorrer y crear registros en la tabla service_requests
        foreach ($services as $service) {
            ServiceRequest::create([
                'uuid' => Uuid::uuid4()->toString(),
                'requested_service' => $service
            ]);
        }
        // END SERVICE REQUEST
    }
  

 
    
}
