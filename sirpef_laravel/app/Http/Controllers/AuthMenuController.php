<?php

namespace App\Http\Controllers;

//use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Menu\RecursiveMenuRepository;

class AuthMenuController extends Controller
{
    public function __invoke()
    {
        if (!Auth::user())
            return response()->json(["message" => "Forbidden"], 403);          
    
        $user = Auth::user();
        
        $menuIds = json_decode($user->configUser?->menu_ids ?? '[]', true) ?: [];

        $menus = [];
        if (count($menuIds) > 0) {
            $menus = RecursiveMenuRepository::recursive($menuIds);
        }

        $isAdmin = $user->isAdmin();
        $hasPaso1 = in_array(35, $menuIds) || in_array('35', $menuIds);
        $hasPaso2 = in_array(39, $menuIds) || in_array('39', $menuIds);

        foreach ($menus as &$rootMenu) {
            if ($rootMenu->id == 33 || strtolower($rootMenu->title) === 'administracion') {
                $newChildren = [];
                $hasAddedForm = false;

                foreach ($rootMenu->children_menus as $child) {
                    $isFormStep = in_array($child->id, [35, 39]) || 
                                  in_array($child->path, ['CasesAdminForm', 'CasesAdminFormPaso1', 'CasesAdminFormPaso2']) ||
                                  stripos($child->title, 'paso 1') !== false ||
                                  stripos($child->title, 'paso 2') !== false;

                    if ($isFormStep) {
                        if ($hasAddedForm) {
                            continue; // Unificar: no duplicar el ítem Formulario
                        }

                        $child->title = 'Formulario';
                        if ($isAdmin || ($hasPaso1 && $hasPaso2)) {
                            $child->path = 'CasesAdminForm';
                        } elseif ($hasPaso1) {
                            $child->path = 'CasesAdminFormPaso1';
                        } elseif ($hasPaso2) {
                            $child->path = 'CasesAdminFormPaso2';
                        } else {
                            $child->path = 'CasesAdminForm';
                        }

                        $newChildren[] = $child;
                        $hasAddedForm = true;
                    } else {
                        $newChildren[] = $child;
                    }
                }
                $rootMenu->children_menus = $newChildren;
            }
        }

        return response()->json($menus);       
    }
}
