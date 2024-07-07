<?php

namespace Database\Seeders;

use App\Models\User;
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
    Permission::create(['name' => 'Super Master', 'guard_name' => $guardName]), // $permissions[0]
    Permission::create(['name' => 'Super Admin', 'guard_name' => $guardName]), // $permissions[1]
    Permission::create(['name' => 'Administrators', 'guard_name' => $guardName]), // $permissions[2]
    
    // Gestión de Equipos y Departamentos
    Permission::create(['name' => 'Manager', 'guard_name' => $guardName]), // $permissions[3]
    Permission::create(['name' => 'Marketing Manager', 'guard_name' => $guardName]), // $permissions[4]
    Permission::create(['name' => 'Director Assistant', 'guard_name' => $guardName]), // $permissions[5]
    Permission::create(['name' => 'Technical Supervisor', 'guard_name' => $guardName]), // $permissions[6]

    // Empresas y Operadores Externos
    Permission::create(['name' => 'Representation Company', 'guard_name' => $guardName]), // $permissions[7]
    Permission::create(['name' => 'Public Company', 'guard_name' => $guardName]), // $permissions[8]
    Permission::create(['name' => 'External Operators', 'guard_name' => $guardName]), // $permissions[9]
    
    // Ajustadores y Servicios Especializados
    Permission::create(['name' => 'Public Adjuster', 'guard_name' => $guardName]), // $permissions[10]
    Permission::create(['name' => 'Insurance Adjuster', 'guard_name' => $guardName]), // $permissions[11]
    Permission::create(['name' => 'Technical Services', 'guard_name' => $guardName]), // $permissions[12]
    Permission::create(['name' => 'Marketing', 'guard_name' => $guardName]), // $permissions[13]
    
    // Operaciones y Soporte Interno
    Permission::create(['name' => 'Warehouse', 'guard_name' => $guardName]), // $permissions[14]
    Permission::create(['name' => 'Administrative', 'guard_name' => $guardName]), // $permissions[15]
    Permission::create(['name' => 'Collections', 'guard_name' => $guardName]), // $permissions[16]
    
    // Acceso a Informes y Prospectos
    Permission::create(['name' => 'Reportes', 'guard_name' => $guardName]), // $permissions[17]
    Permission::create(['name' => 'Lead', 'guard_name' => $guardName]), // $permissions[18]
    
    // Usuarios Generales
    Permission::create(['name' => 'Employees', 'guard_name' => $guardName]), // $permissions[19]
    Permission::create(['name' => 'Client', 'guard_name' => $guardName]), // $permissions[20]
    Permission::create(['name' => 'Contact', 'guard_name' => $guardName]), // $permissions[21]
    Permission::create(['name' => 'Spectator', 'guard_name' => $guardName]), // $permissions[22]
];





    // Creación y Asignación de Roles

     // SUPER MASTER USER
$superMasterRole = Role::create(['name' => 'Super Master', 'guard_name' => $guardName]);
$superMasterRole->syncPermissions($permissions);

$superMasterUser = User::factory()->create([
    'name' => 'Super Master',
    'username' => 'supermaster24',
    'email' => 'supermaster@company.com',
    'uuid' => Uuid::uuid4()->toString(), 
    'phone' => '00001',
    'password' => bcrypt('Gc98765=')
]);
$superMasterUser->assignRole($superMasterRole);
// END SUPER MASTER USER

// SUPER ADMIN USER
$superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => $guardName]);
$superAdminRole->syncPermissions([
    $permissions[1],  // Super Admin
    $permissions[2],  // Administrators
    $permissions[3],  // Manager
    $permissions[4],  // Marketing Manager
    $permissions[5],  // Director Assistant
    $permissions[6],  // Technical Supervisor
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
]); 

$superAdminUser = User::factory()->create([
    'name' => 'Super Admin',
    'username' => 'superadmin24',
    'email' => 'superadmin@company.com',
    'uuid' => Uuid::uuid4()->toString(), 
    'phone' => '00000',
    'password' => bcrypt('Gc98765=')
]);
$superAdminUser->assignRole($superAdminRole);
// END SUPER ADMIN USER

   

     // ADMIN USER
$adminRole = Role::create(['name' => 'Admin', 'guard_name' => $guardName]);
$adminRole->syncPermissions([
    $permissions[2],  // Administrators
    $permissions[3],  // Manager
    $permissions[4],  // Marketing Manager
    $permissions[5],  // Director Assistant
    $permissions[6],  // Technical Supervisor
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[3],  // Manager
    $permissions[4],  // Marketing Manager
    $permissions[5],  // Director Assistant
    $permissions[6],  // Technical Supervisor
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[4],  // Marketing Manager
    $permissions[5],  // Director Assistant
    $permissions[6],  // Technical Supervisor
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[5],  // Director Assistant
    $permissions[6],  // Technical Supervisor
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[6],  // Technical Supervisor
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[7],  // Representation Company
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[8],  // Public Company
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[9],  // External Operators
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[10], // Public Adjuster
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[11], // Insurance Adjuster
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[12], // Technical Services
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[13], // Marketing
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[14], // Warehouse
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[15], // Administrative
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[16], // Collections
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[17], // Reportes
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[18], // Lead
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[19], // Employees
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[20], // Client
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[21], // Contact
    $permissions[22], // Spectator
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
    $permissions[22], // Spectator
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
    

       

    }
}
