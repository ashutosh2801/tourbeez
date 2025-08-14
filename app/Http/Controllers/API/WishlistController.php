<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    protected $rules; 
    protected $messages; 
    
    public function index(Request $request)
    {
        $userId     = $request->user_id;
        $sessionId  = $request->session_id;

        $wishlists = [];
        if ( $userId && $sessionId ) {
            $wishlists = Wishlist::with('tour')
                        ->where('user_id', $userId)
                        ->orWhere('session_id', $sessionId)
                        ->get();
        } else if ( $sessionId ) {
            $wishlists = Wishlist::with('tour')
                        ->where('session_id', $sessionId)
                        ->get();
        }

        // Return the transformed data along with pagination info
        return response()->json([
            'status'    => true,
            'requested' => $request->all(),
            'data'      => $wishlists,
        ]);
    }

    public function store(Request $request)
    {
        $this->rules = [
            'tour_id' => 'required|exists:tours,id',
        ];

        $this->messages = [
            'tour_id.required' => 'Tour ID is required.',
        ];

        $validated = Validator::make($request->all(), $this->rules, $this->messages);
        if ($validated->fails()) {
            return response()->json(['status' => false, 'errors' => $validated->errors()], 422);
        } 

        $tourId = $request->tour_id;
        $userId = $request->user_id;
        $sessionId = $request->session_id;

        if ( $userId ) {
            $wishlist = Wishlist::firstOrCreate([
                'user_id' => $userId,
                'tour_id' => $tourId,
            ]);
            return response()->json(['message' => 'Added to wishlist', 'wishlist' => $wishlist]);
        } else {
            $wishlist = Wishlist::firstOrCreate([
                'tour_id' => $tourId,
                'session_id' => $sessionId,
            ]);
            return response()->json(['message' => 'Added to session wishlist', 'wishlist' => $wishlist]);
        }
    }

    public function wishlist_tours(Request $request)
    {
        $userId = $request->user_id;
        $sessionId = $request->session_id;

        $tours = Tour::whereHas('wishlists', function ($query) use ($userId, $sessionId) {
                    if($userId) {
                        $query->where('user_id', $userId);
                    }
                    if($sessionId) {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->paginate(12);
        
        //dd($tours->toSql());

        $items = [];
        foreach ($tours->items() as $d) {

            $galleries = [];
            if(count($d->galleries)>0) {
                foreach( $d->galleries as $g ) {
                    $image      = uploaded_asset($g->id);
                    $medium_url = str_replace($g->file_name, $g->medium_name, $image);
                    $thumb_url  = str_replace($g->file_name, $g->thumb_name, $image);
                    $galleries[] = [
                        'original_image' => $image,
                        'medium_image'   => $medium_url,
                        'thumb_image'    => $thumb_url,
                    ];
                }
            }
            else {
                $image      = uploaded_asset($d->main_image->id);
                $medium_url = str_replace($d->main_image->file_name, $d->main_image->medium_name, $image);
                $thumb_url  = str_replace($d->main_image->file_name, $d->main_image->thumb_name, $image);
                $galleries[] = [
                    'original_image' => $image,
                    'medium_image'   => $medium_url,
                    'thumb_image'    => $thumb_url,
                ];
            }

            $duration = $d->schedule?->estimated_duration_num . ' ' ?? '';
            $duration .= ucfirst($d->schedule?->estimated_duration_unit ?? '');

            $items[] = [
                'id'             => $d->id,
                'title'          => $d->title,
                'slug'           => $d->slug,
                'unique_code'    => $d->unique_code,
                'all_images'     => $galleries,
                'price'          => price_format($d->price),
                'original_price' => $d->price,
                'duration'       => strtolower($duration),
                'rating'         => randomFloat(4, 5),
                'comment'        => rand(50, 100),
            ];
        }

        // Return the transformed data along with pagination info
        return response()->json([
            'status'         => true,
            'requested'      => $request->all(),
            'data'           => $items,
            'current_page'   => $tours->currentPage(),
            'last_page'      => $tours->lastPage(),
            'per_page'       => $tours->perPage(),
            'total'          => $tours->total(),
            'next_page_url'  => $tours->nextPageUrl(),
            'prev_page_url'  => $tours->previousPageUrl(),
        ]);
    }

    public function destroy(Request $request, $tourId)
    {
        $userId = $request->user_id;
        $sessionId = $request->session_id;
        if ( $userId ) {
            Wishlist::where('user_id', $userId)
                ->where('tour_id', $tourId)
                ->delete();
            return response()->json(['status'=>true, 'message' => 'Removed from wishlist']);
        } else {
            Wishlist::where('session_id', $sessionId)
                ->where('tour_id', $tourId)
                ->delete();
            return response()->json(['status'=>true, 'message' => 'Removed from session wishlist']);
        }
    }
}