<?php

namespace App\Http\Resources;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Evento;
use App\Models\Menu;
use App\Models\Ministerio;
use App\Models\user;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
 

    public function toArray($request)
    {


        $eventos = json_decode($this->configUser->evento_asignado, true);
        $Eventos_id = Evento::whereIn("id", $eventos ?  $eventos : [])->get();

        $eventos_array = $Eventos_id->map(function ($item) {
          return [
            'id' => $item->id,
            'nombre' => $item->titulo,
          ];
        });

        $menus = json_decode($this->configUser->menu_ids, true);
        $menus_id = Menu::whereIn("id", $menus ?  $menus : [])->get();

        $menu = $menus_id->map(function ($item) {
          return [
            'id' => $item->id,
            'nombre' => $item->title,
          ];
        });

        $oficinas = json_decode($this->configUser->oficina_asignada, true);
        $oficinas_id = Ministerio::whereIn("id", $oficinas ?  $oficinas : [])->get();

        return [
          'id' => $this->id,
          'name' => $this->name,
          'email' => $this->email,
          'role_id' => $this->role_id,
          'ministerio_id' => $this->persona?->ministerio_id,
          'cedula' => $this->cedula,
          'menus_id'=> $menu,
          'isAdmin' => $this->isAdmin(),
          'eventos_id'=>$eventos_array,
          'oficinas_ids'=>$oficinas_id,
        ];
    }

}
