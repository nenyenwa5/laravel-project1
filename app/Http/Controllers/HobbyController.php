<?php

namespace App\Http\Controllers;

use App\Hobby;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Gate;

class HobbyController extends Controller
{


    public function __construct()
    {
        $this->middleware( 'auth')->except(['index', 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //After manually inserting hobbies to get them

       // $hobbies = Hobby::all();
       //$hobbies = Hobby::paginate(10);

       $hobbies = Hobby::orderBy('created_at', 'DESC')->paginate(10);

        
        return view( 'hobby.index')->with([
            'hobbies' =>$hobbies
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view( 'hobby.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'description' => 'required|min:5'
        ]);
        $hobby = new Hobby([
            'name' =>$request['name'],
            'description' =>$request['description'],
            'user_id' => auth()->id()
        ]);
        $hobby->save();
        /*
        return $this->index()->with(
            [
                'message_success' => "The hobby <b>" . $hobby->name . "</b> was created successfully."
            ]
        );
        */
        return redirect('/hobby/' . $hobby->id)->with(
            [
                'message_warning' => "Please assign some tags now."
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Hobby  $hobby
     * @return \Illuminate\Http\Response
     */
    public function show(Hobby $hobby)
    {
        $allTags = Tag::all();
        $usedTags = $hobby->tags;
        $availableTags = $allTags->diff($usedTags);
        
        return view( 'hobby.show')->with([
            'hobby' => $hobby,
            'availableTags' => $availableTags,
            'message_success' => Session::get('message_success'),
            'message_warning' => Session::get('message_warning')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Hobby  $hobby
     * @return \Illuminate\Http\Response
     */
    public function edit(Hobby $hobby)
    {
        abort_unless(Gate::allows('update', $hobby), 403);

        return view('hobby.edit')->with([
            'hobby' => $hobby
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Hobby  $hobby
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Hobby $hobby)
    {
        abort_unless(Gate::allows('update', $hobby), 403);

        $request->validate([
            'name' => 'required|min:3',
            'description' => 'required|min:5',
            'name' => 'mimes:jpeg,jpg,bmp,png'
        ]);

            if($request->image) {
                $image = Image::make($request->image);
                if ($image->width() > $image->height() ) {
                    dd('landscape');
                }
                else {
                    
                }
            }

        $hobby->update([
            'name' =>$request['name'],
            'description' =>$request['description'],
        ]);
        $hobby->save();
        return $this->index()->with(
            [
                'message_success' => "The hobby <b>" . $hobby->name . "</b> was updated successfully."
            ]
        ); 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Hobby  $hobby
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hobby $hobby)
    {
        abort_unless(Gate::allows('delete', $hobby), 403);

        $oldName = $hobby->name;
        $hobby->delete();

        return $this->index()->with(
            [
                'message_success' => "The hobby <b>" . $oldName . "</b> was deleted successfully."
            ]
        ); 
    }
}
