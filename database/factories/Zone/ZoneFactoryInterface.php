<?php
  /**
  * Author: Adam Chin Wai Kin
  */
namespace Database\Factories\Zone;

use Illuminate\Http\Request;
use App\Models\Zone;

interface ZoneFactoryInterface
{
    public function create(Request $request): Zone;
}
