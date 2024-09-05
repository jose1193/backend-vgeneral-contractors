<?php

namespace App\Repositories;
use App\Models\FilesEsx;
use App\Interfaces\FileEsxRepositoryInterface;

class FileEsxRepository implements FileEsxRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
         return FilesEsx::withTrashed()->orderBy('id', 'DESC')->get();
       
    }

     public function getByUuid(string $uuid)
    {
        return FilesEsx::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return FilesEsx::create($data);
    }

    public function update(array $data, $uuid)
   {
    $file = $this->getByUuid($uuid);
    
    $file->update($data);

    return $file;
 }
    
    public function delete(string $uuid)
    {
        $file = FilesEsx::where('uuid', $uuid)->firstOrFail();
        $file->delete();
        return $file;
    }

   public function getFileEsxByUser($user)
   {
    if ($user->hasPermissionTo('Super Admin', 'api')) {
        // Si el usuario tiene el permiso de "Super Admin", obtiene todos los archivos
        return FilesEsx::orderBy('id', 'DESC')->get();
    } elseif ($user->hasPermissionTo('Public Adjuster', 'api')) {
        // Si el usuario tiene el permiso de "Public Adjuster", obtiene solo los archivos asignados a su ID
        return FilesEsx::whereHas('assignments', function ($query) use ($user) {
            $query->where('public_adjuster_id', $user->id);
        })->orderBy('id', 'DESC')->get();
    } else {
        // Si el usuario no tiene permisos especiales, puede obtener los archivos que ha subido
        return FilesEsx::where('uploaded_by', $user->id)
                       ->orderBy('id', 'DESC')
                       ->get();
    }
 }


    
}
