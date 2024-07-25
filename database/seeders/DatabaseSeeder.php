<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TypeDamage;
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
    }
}
