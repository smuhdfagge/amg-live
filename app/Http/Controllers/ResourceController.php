<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(Request $request): View
    {
        $type = array_key_exists($request->query('type'), Resource::TYPES) ? $request->query('type') : null;

        return view('resources.index', [
            'type' => $type,
            'resources' => Resource::published()
                ->when($type, fn ($q) => $q->where('type', $type))
                ->paginate(12)
                ->withQueryString(),
            'counts' => Resource::published()->reorder()->selectRaw('type, count(*) as total')
                ->groupBy('type')->pluck('total', 'type'),
        ]);
    }
}
