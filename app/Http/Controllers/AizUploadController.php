<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;


class AizUploadController extends Controller
{

    public function __construct()
    {
        //$this->middleware(['permission:show_uploaded_files'])->only('index');
    }

    public function index(Request $request)
    {
        $all_uploads = Upload::query();
        $search = null;
        $sort_by = null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%'.$request->search.'%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }

        $all_uploads = $all_uploads->paginate(60)->appends(request()->query());

        return view('admin.uploaded_files.index', compact('all_uploads', 'search', 'sort_by') );
    }

    public function create(){
        return view('admin.uploaded_files.create');
    }

    public function show_uploader(Request $request){
        return view('uploader.aiz-uploader');
    }

    public function upload(Request $request){
        $type = array(
            "jpg"=>"image",
            "jpeg"=>"image",
            "png"=>"image",
            "svg"=>"image",
            "webp"=>"image",
            "gif"=>"image",
            "mp4"=>"video",
            "mpg"=>"video",
            "mpeg"=>"video",
            "webm"=>"video",
            "ogg"=>"video",
            "avi"=>"video",
            "mov"=>"video",
            "flv"=>"video",
            "swf"=>"video",
            "mkv"=>"video",
            "wmv"=>"video",
            "wma"=>"audio",
            "aac"=>"audio",
            "wav"=>"audio",
            "mp3"=>"audio",
            "zip"=>"archive",
            "rar"=>"archive",
            "7z"=>"archive",
            "doc"=>"document",
            "txt"=>"document",
            "docx"=>"document",
            "pdf"=>"document",
            "csv"=>"document",
            "xml"=>"document",
            "ods"=>"document",
            "xlr"=>"document",
            "xls"=>"document",
            "xlsx"=>"document"
        );

        if (!$request->hasFile('aiz_file') || !$request->file('aiz_file')->isValid()) {
            return response()->json([
                'status' => false,
                'message' => 'No valid file uploaded.'
            ], 400);
        }

        if($request->hasFile('aiz_file')){
            $upload = new Upload;
            $extension = strtolower($request->file('aiz_file')->getClientOriginalExtension());

            if(isset($type[$extension])) {
                try {

                    $file = $request->file('aiz_file');
                    //$path = $file->store('uploads/all', 'local');
                    $size = $file->getSize();
                    $originalName = Str::slug( pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) );

                    $uniqueSlug = $originalName;
                    $counter = 1;
                    while (Upload::where('file_name', $uniqueSlug)->exists()) {
                        $uniqueSlug = $originalName . '-' . $counter;
                        $counter++;
                    }
                    $originalName = $uniqueSlug;

                    $upload->file_original_name = $originalName;

                    if($type[$extension] == 'image' ) {
                        try {

                            $image = Image::read($file);
                            $filename = $originalName . '.' . $extension;
                            $path = 'uploads/all/' . $filename;
                            $image->save(base_path('public/' . $path));

                            // // Save medium size
                            $mediumImage = (clone $image)->scale(height: 600);
                            $mediumFilename = $originalName . '-600x600.' . $extension;
                            $mediumPath = 'uploads/all/' . $mediumFilename;
                            $mediumImage->save(base_path('public/' . $mediumPath));

                            // // Save thumbnail
                            $thumbImage = (clone $image)->scale(height: 200);                 
                            $thumbFilename = $originalName . '-200x200.' . $extension;
                            $thumbPath = 'uploads/all/' . $thumbFilename;
                            $thumbImage->save(base_path('public/' . $thumbPath));

                            clearstatcache();
                            

                        } catch (\Exception $e) {
                            Log::error('Upload failed', ['error' => $e->getMessage()]);
                            return response()->json([
                                'status' => false,
                                'message' => $e->getMessage(),
                            ], 500);
                        }
                    }

                    try {
                        Log::info('s3 Upload triggered' . env('FILESYSTEM_DRIVER') );
                        if (env('FILESYSTEM_DRIVER') === 's3') {
                            Storage::disk('s3')->put($path, file_get_contents(base_path('public/' . $path)), 'public');
                            Storage::disk('s3')->put($mediumPath, file_get_contents(base_path('public/' . $mediumPath)), 'public');
                            Storage::disk('s3')->put($thumbPath, file_get_contents(base_path('public/' . $thumbPath)), 'public');
                            //Log::info("Upload to s3 successful: " . $path);
                            //$url = Storage::disk('s3')->url($path);
                            //Log::info("File URL: " . $url);
                            //unlink(base_path('public/' . $path));
                            //unlink(base_path('public/' . $mediumPath));
                            //unlink(base_path('public/' . $thumbPath));
                        }
                    } catch (\Exception $e) {
                        Log::error('s3 Upload error: ' . $e->getMessage());
                    }

                    $upload->extension      = $extension;
                    $upload->file_name      = $path;
                    $upload->medium_name    = $mediumPath ?? null;
                    $upload->thumb_name     = $thumbPath ?? null;
                    $upload->user_id        = Auth::user()->id;
                    $upload->type           = $type[$upload->extension];
                    $upload->file_size      = $size;
                    $upload->save();
                } catch (\Exception $e) {
                    Log::error('Upload failed', ['error' => $e->getMessage()]);
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ], 500);
                }
            }
            else {
                //Log::error('Upload failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'status' => false,
                    'message' => 'No valid file uploaded.'
                ], 400);
            }
        }
    }

    public function get_uploaded_files(Request $request)
    {
        $uploads = Upload::where('user_id', Auth::user()->id);
        if ($request->search != null) {
            $uploads->where('file_original_name', 'like', '%'.$request->search.'%');
        }
        if ($request->sort != null) {
            switch ($request->sort) {
                case 'newest':
                    $uploads->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $uploads->orderBy('created_at', 'asc');
                    break;
                case 'smallest':
                    $uploads->orderBy('file_size', 'asc');
                    break;
                case 'largest':
                    $uploads->orderBy('file_size', 'desc');
                    break;
                default:
                    $uploads->orderBy('created_at', 'desc');
                    break;
            }
        }
        return $uploads->paginate(60)->appends(request()->query());
    }

    public function destroy(Request $request,$id)
    {
        try{
            if(env('FILESYSTEM_DRIVER') === 's3'){
                Storage::disk('s3')->delete(Upload::where('id', $id)->first()->file_name);
            }
            else{
                unlink(public_path().'/'.Upload::where('id', $id)->first()->file_name);
            }
            Upload::destroy($id);
            Log::info("File deleted successfully " . $id);
            //flash(translate('File deleted successfully'))->success();
            return redirect()->back()->with('success','File deleted successfully');
        }
        catch(\Exception $e){
            //Upload::destroy($id);
            Log::info("Faild deleted: " . $e->getMessage());
            //flash(translate('File deleted successfully'))->success();
        }
        return redirect()->back()->with('error',"Faild deleted: " . $e->getMessage());;
    }

    public function get_preview_files(Request $request){
        $ids = explode(',', $request->ids);
        $files = Upload::whereIn('id', $ids)->get();
        return $files;
    }

    //Download project attachment
    public function attachment_download($id)
    {
        $project_attachment = Upload::find($id);
        try{
           $file_path = public_path($project_attachment->file_name);
            return Response::download($file_path);
        }catch(\Exception $e){
            Log::info("File does not exist! " . $e->getMessage());
            //flash(translate('File does not exist!'))->error();
            return back();
        }

    }
    
    //Download project attachment
    public function file_info(Request $request)
    {
        $file = Upload::findOrFail($request['id']);
        return view('admin.uploaded_files.info',compact('file'));
    }

    public function migrate_database(Request $request){
		//bugs
    }
    public function add_image_info(Request $request){

        $request->validate([
            'image_title' => 'required|max:255',
        ],
        [
            'image_title.required' => 'Please enter a image title',
        ]);
        
        // Update tour instance
        $id = $request->image_id;
        $upload  = Upload::findOrFail($id);           
        $upload->file_original_name     = $request->image_title;
        $upload->caption                = $request->caption;
        $upload->description            = $request->description;
        if ( $upload->save() ) {
            return redirect()->back()->withInput()->with('success','Image Info saved successfully.'); 
        }
    }

    public function storeYoutube(Request $request)
    {
        $request->validate([
            'youtube_url' => 'required|url'
        ]);

        $videoId = $this->getYoutubeVideoId($request->youtube_url);

        if (!$videoId) {
            return response()->json(['error' => 'Invalid YouTube URL'], 400);
        }

        // Thumbnail URLs
        $largeThumbnail = "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        $mediumThumbnail = "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
        // $smallThumbnail = "https://img.youtube.com/vi/{$videoId}/default.jpg";

        // Save to DB
        $upload = new Upload();
        $upload->file_original_name = $videoId; // Using video ID as original name
        $upload->file_name = $request->youtube_url; // Storing the URL
        $upload->medium_name = $largeThumbnail; // Large thumbnail
        $upload->thumb_name = $mediumThumbnail; // Medium thumbnail
        $upload->user_id = auth()->id();
        $upload->type = 'youtube';
        $upload->caption = $request->caption ?? null;
        $upload->description = $request->description ?? null;
        $upload->save();

        return response()->json([
            'message' => 'YouTube video saved successfully!',
            'data' => $upload
        ]);
    }

    private function getYoutubeVideoId($url)
    {
        preg_match('/(youtu\.be\/|v=)([A-Za-z0-9_-]{11})/', $url, $matches);
        return $matches[2] ?? null;
    }

}
