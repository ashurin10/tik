<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Services\MenuService;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index()
    {
        $data = $this->menuService->getMenusForIndex();
        return view('menus.index', $data);
    }

    public function store(StoreMenuRequest $request)
    {
        $this->menuService->createMenu($request->validated());

        return redirect()->route('menus.index')->with('status', 'Menu created successfully!');
    }

    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $this->menuService->updateMenu($menu, $request->validated());

        return redirect()->route('menus.index')->with('status', 'Menu updated successfully!');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('status', 'Menu deleted successfully!');
    }
}
