<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Tour;
use Illuminate\Support\Facades\Cache;

class ExportController extends Controller
{
    /**
     * Export a listing of the tours.
     */
    public function index(Request $request)
    {
        $tours = Tour::select(['id', 'title', 'slug', 'unique_code', 'price'])
            ->with([
                'mainImage:id,file_name,medium_name,thumb_name',
            ])
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('sort_order', 'ASC')
            ->get();

        // CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tours.csv"',
        ];

        $callback = function () use ($tours) {
            $file = fopen('php://output', 'w');

            // Column headers
            fputcsv($file, ['product sku (required)', 'product name (required)', 'product url', 'product image url', 'price', 'currency']);

            // Iterate through data
            foreach ($tours as $tour) {
                $imageFile = optional($tour->mainImage)->file_name;
                $imageUrl = $imageFile ? 'https://tourbeez.s3.amazonaws.com/' . $imageFile : '';

                fputcsv($file, [
                    $tour->unique_code,
                    $tour->title,
                    'https://tourbeez.com/tour/'.$tour->slug,
                    $imageUrl,
                    $tour->price,
                    'CAD'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }



}
