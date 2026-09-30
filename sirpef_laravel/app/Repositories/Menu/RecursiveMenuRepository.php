<?php
namespace App\Repositories\Menu;

class RecursiveMenuRepository
{
   /**
   * Return an array of recursive.
   *
   * @return Array
   */    
    static public function recursive(Array $menuIds = []): Array
    {
        if (empty($menuIds)) {
            $query = \App\Models\Menu::whereNull("menu_id");
            $menu = $query->with("childrenMenus")->get();
            return json_decode($menu) ?? [];
        }

        // Expand parent menu IDs so parent categories are always loaded
        $expandedMenuIds = $menuIds;
        $parentIds = \App\Models\Menu::whereIn('id', $menuIds)->pluck('menu_id')->filter()->unique()->toArray();
        while (!empty($parentIds)) {
            $expandedMenuIds = array_unique(array_merge($expandedMenuIds, $parentIds));
            $parentIds = \App\Models\Menu::whereIn('id', $parentIds)->pluck('menu_id')->filter()->unique()->toArray();
        }

        $query = \App\Models\Menu::whereNull("menu_id")->whereIn("id", $expandedMenuIds);

        $menu = $query->with(
            "childrenMenus",
            fn ($q) => $q->whereIn("id", $menuIds)
        )->get();

        return json_decode($menu) ?? [];
    } 
 
}
