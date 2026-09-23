<?php

namespace App\Http\Controllers\Ketua;

use App\Http\Controllers\Controller;

abstract class KetuaController extends Controller
{
    // Otorisasi lewat middleware role + Gate di FormRequest (manage-master / manage-holidays).
}
