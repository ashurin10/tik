<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuAccess;

class MenuService
{
    public function getMenusForIndex()
    {
        $menus = Menu::whereNull('parent_id')->with('children.roles', 'roles')->orderBy('order')->get();
        $allMenus = Menu::orderBy('name')->get();
        
        return compact('menus', 'allMenus');
    }

    public function createMenu(array $data)
    {
        $menu = Menu::create([
            'name' => $data['name'],
            'url' => $data['url'] ?? null,
            'icon' => $data['icon'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'order' => Menu::max('order') + 1,
        ]);

        $this->syncRoles($menu->id, $data['roles'] ?? []);

        return $menu;
    }

    public function updateMenu(Menu $menu, array $data)
    {
        $menu->update([
            'name' => $data['name'],
            'url' => $data['url'] ?? null,
            'icon' => $data['icon'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        $this->syncRoles($menu->id, $data['roles'] ?? []);

        return $menu;
    }

    protected function syncRoles(int $menuId, array $roles)
    {
        MenuAccess::where('menu_id', $menuId)->delete();
        foreach ($roles as $role) {
            MenuAccess::create([
                'menu_id' => $menuId,
                'role' => $role,
            ]);
        }
    }
}
